<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  /**
   * Consulta o conteúdo do lote (quando Situacao = 3).
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $endpoint = (string) config('nfse.issnet.endpoints.consultar_lote');

    // 1) Descobre base/operation publicadas no WSDL do .asmx
    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['Lote', 'Consultar', 'Rps', 'RPS'] // prioriza métodos do lote
    );

    // 2) Monta o DADOS ABRASF (sem declaração XML)
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim($protocolo);

    $dadosAbrasf = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    // 3) Se o WSDL apontou para ABRASF, usamos o envelope "bare"
    if (stripos($base, 'nfse.abrasf.org.br') !== false) {
      $versao = (string) config('nfse.issnet.versao_dados', '2.04');

      $cabecalho = sprintf(
        '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
        $versao,
        $versao
      );

      // SOAPAction exato do WSDL (sem barra sobrando no namespace)
      $soapAction = rtrim($base, '/') . '/' . $op; // ex.: http://nfse.abrasf.org.br/ConsultarLoteRps

      return SoapRequestHelper::enviar(
        $endpoint,
        $op,            // "ConsultarLoteRps"
        $cabecalho,
        $dadosAbrasf,
        ['style' => 'bare', 'soap_action' => $soapAction]
      );
    }

    // 4) Se o WSDL publicar outro namespace (não-ABRASF), usa wrapper ASMX <xml><![CDATA[...]]>
    $ns = rtrim($base, '/'); // xmlns EXATO, sem barra no final
    return SoapRequestHelper::enviarIssnet11ComBase(
      $endpoint,
      $ns,  // p/ xmlns="{$ns}"
      $op,  // operação do WSDL
      $dadosAbrasf,
      true  // SOAPAction com aspas
    );
  }
}
