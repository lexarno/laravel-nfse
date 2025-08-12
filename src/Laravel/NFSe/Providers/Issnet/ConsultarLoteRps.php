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
    $imXml = $im !== '' ? "<InscricaoMunicipal>{$im}</InscricaoMunicipal>" : '';

    // 2) Cabeçalho SEM quebras e no padrão 2.04
    $cabecalho = '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04"><versaoDados>2.04</versaoDados></cabecalho>';

    // 3) Payload SEM a declaração XML
    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    {$imXml}
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    // Estilo 'bare' (wrapper <ns:ConsultarLoteRps> com nfseCabecMsg/nfseDadosMsg)
    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_lote'),
      'ConsultarLoteRps',
      $cabecalho,
      $dados,
      ['style' => 'bare'] // <- importante neste endpoint
    );
  }
}
