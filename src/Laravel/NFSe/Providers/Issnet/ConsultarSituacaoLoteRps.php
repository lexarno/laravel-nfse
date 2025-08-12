<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $endpoint = config('nfse.issnet.endpoints.consultar_situacao');

    $cnpj = preg_replace('/\D+/', '', $cnpj);
    $im   = preg_replace('/\D+/', '', $inscricaoMunicipal);
    if ($cnpj === '' || $im === '') {
      throw new \RuntimeException('CNPJ/IM obrigatórios.');
    }

    // Payload Abrasf SEM a declaração XML
    $dados = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    // Se for ASMX, use o formato ISSNet (action própria + <xml>...</xml>)
    if (stripos($endpoint, '.asmx') !== false) {
      return SoapRequestHelper::enviarIssnet(
        $endpoint,
        'ConsultarSituacaoLoteRPS', // << RPS MAIÚSCULAS para ASMX
        $dados
      );
    }

    // Caso contrário, WCF/SVC com envelope Abrasf (style bare)
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
      $dados,
      ['style' => 'bare']
    );
  }
}
