<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    if ($cnpj === '' || $im === '') {
      throw new \RuntimeException('CNPJ/IM do prestador obrigatórios para ConsultarSituacaoLoteRps.');
    }

    // Cabeçalho “seco”
    $versao = (string) config('nfse.issnet.versao_dados', '2.04');
    $cabecalho = sprintf(
      '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
      $versao,
      $versao
    );

    // Payload SEM declaração XML
    $dados = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_situacao'),
      'ConsultarSituacaoLoteRps',
      $cabecalho,
      $dados,
      ['style' => 'bare'] // este endpoint quer bare
    );
  }
}
