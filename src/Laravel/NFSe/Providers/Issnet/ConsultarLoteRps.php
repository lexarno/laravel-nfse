<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath     = $certPath;
    $this->certPassword = $certPassword;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    // 1) Sanitização
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);

    if ($cnpj === '' || $im === '') {
      throw new \RuntimeException('Prestador sem CNPJ ou Inscrição Municipal (IM). Ambas são exigidas para ConsultarLoteRps.');
    }

    // 2) Cabeçalho “seco” + versão configurável (fallback 2.04)
    $versao = (string) config('nfse.issnet.versao_dados', '2.04');
    $cabecalho = sprintf(
      '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
      $versao,
      $versao
    );

    // 3) Payload SEM a declaração XML
    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    return \Laravel\NFSe\Helpers\SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_lote'),
      'ConsultarLoteRps',
      $cabecalho,
      $dados,
      ['style' => 'bare'] // este endpoint quer o wrapper bare
    );
  }
}
