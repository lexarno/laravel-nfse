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
    $cnpj = preg_replace('/\D+/', '', $cnpj);
    $im   = trim((string) $inscricaoMunicipal);
    $imXml = $im !== '' ? "<InscricaoMunicipal>{$im}</InscricaoMunicipal>" : '';

    $dados = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    {$imXml}
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_lote'),
      'ConsultarLoteRps',                 // << operação SOAP correta
      $this->gerarCabecalhoAbrasf(),
      $dados                              // << payload Abrasf correto
    );
  }

  protected function gerarCabecalhoAbrasf(): string
  {
    return <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04">
  <versaoDados>2.04</versaoDados>
</cabecalho>
XML;
  }
}
