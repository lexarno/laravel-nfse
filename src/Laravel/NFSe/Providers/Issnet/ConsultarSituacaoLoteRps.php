<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $endpoint = (string) config('nfse.issnet.endpoints.consultar_situacao');

    // Sanitização
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    if ($cnpj === '' || $im === '') {
      throw new \RuntimeException('CNPJ e IM são obrigatórios para ConsultarSituacaoLoteRps.');
    }

    // Payload ABRASF (SEM declaração XML)
    $dadosAbrasf = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    if (stripos($endpoint, '.asmx') !== false) {
      // 1) Descobre a base + operação exatas pelo WSDL
      [$base, $op] = \Laravel\NFSe\Helpers\SoapRequestHelper::descobrirAsmxOperacao($endpoint, ['Situacao', 'Lote', 'Rps', 'RPS']);

      // 2) Envia em SOAP 1.1 com o que o WSDL publicou
      return \Laravel\NFSe\Helpers\SoapRequestHelper::enviarIssnet11ComBase(
        $endpoint,
        $base,
        $op,
        $dadosAbrasf,
        true // SOAPAction com aspas
      );
    }

    // Caso contrário (SVC), usa Abrasf "bare"
    $versao = (string) config('nfse.issnet.versao_dados', '2.04');
    $cabecalho = sprintf(
      '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
      $versao,
      $versao
    );

    return SoapRequestHelper::enviar(
      $endpoint,
      'ConsultarSituacaoLoteRps',
      $cabecalho,
      $dadosAbrasf,
      ['style' => 'bare']
    );
  }
}
