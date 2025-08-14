<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  /**
   * Consulta a situação do lote (1=processando, 2=erro, 3=processado).
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $endpoint = (string) config('nfse.issnet.endpoints.consultar_situacao');

    // 1) Descobre base/operação pelo WSDL do endpoint (.asmx)
    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['Situacao', 'Lote', 'Rps', 'RPS']
    );

    // 2) Payload ABRASF SEM declaração XML
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim($protocolo);

    $dadosAbrasf = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    // 3) Se o WSDL apontou p/ ABRASF ⇒ usar envelope "bare"
    if (stripos($base, 'nfse.abrasf.org.br') !== false) {
      $versao = (string) config('nfse.issnet.versao_dados', '2.04');

      $cabecalho = sprintf(
        '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
        $versao,
        $versao
      );

      $soapAction = rtrim($base, '/') . '/' . $op; // ex.: http://nfse.abrasf.org.br/ConsultarSituacaoLoteRps

      return SoapRequestHelper::enviar(
        $endpoint,
        $op,            // "ConsultarSituacaoLoteRps"
        $cabecalho,
        $dadosAbrasf,
        ['style' => 'bare', 'soap_action' => $soapAction]
      );
    }

    // 4) Caso o WSDL aponte para outro namespace ⇒ usa wrapper ASMX <xml><![CDATA[...]]>
    $ns = rtrim($base, '/'); // xmlns EXATO sem barra final
    return SoapRequestHelper::enviarIssnet11ComBase(
      $endpoint,
      $ns,
      $op,
      $dadosAbrasf,
      true
    );
  }
}
