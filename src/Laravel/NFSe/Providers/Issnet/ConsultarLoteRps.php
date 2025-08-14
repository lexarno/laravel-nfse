<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  /**
   * Consulta o conteúdo do lote (Após Situacao=3).
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $endpoint = (string) config('nfse.issnet.endpoints.consultar_lote');

    // 1) Descobre base/operação pelo WSDL do endpoint (.asmx)
    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['Lote', 'Consultar', 'Rps', 'RPS'] // prioriza métodos de "lote"
    );

    // 2) Monta o payload ABRASF SEM declaração XML
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

    // 3) Se o WSDL apontou p/ ABRASF ⇒ usar envelope "bare"
    if (stripos($base, 'nfse.abrasf.org.br') !== false) {
      $versao = (string) config('nfse.issnet.versao_dados', '2.04');

      $cabecalho = sprintf(
        '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
        $versao,
        $versao
      );

      // soapAction EXATO do WSDL (sem barra no namespace)
      $soapAction = rtrim($base, '/') . '/' . $op; // ex.: http://nfse.abrasf.org.br/ConsultarLoteRps

      return SoapRequestHelper::enviar(
        $endpoint,
        $op,            // "ConsultarLoteRps"
        $cabecalho,
        $dadosAbrasf,
        ['style' => 'bare', 'soap_action' => $soapAction]
      );
    }

    // 4) Caso o WSDL aponte para outro namespace ⇒ usa wrapper ASMX <xml><![CDATA[...]]>
    $ns = rtrim($base, '/'); // xmlns EXATO sem barra no final
    return SoapRequestHelper::enviarIssnet11ComBase(
      $endpoint,
      $ns,  // ex.: http://www.issnetonline.com.br/webservice/nfd  (sem / no final)
      $op,  // operação do WSDL
      $dadosAbrasf,
      true  // SOAPAction com aspas
    );
  }
}
