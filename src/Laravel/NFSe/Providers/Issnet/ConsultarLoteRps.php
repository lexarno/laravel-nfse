<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\XmlSigner;
use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath = $certPath;
    $this->certPassword = $certPassword;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $numeroLote): string
  {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;

    $xml = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <Cnpj>{$cnpj}</Cnpj>
    <InscricaoMunicipal>{$inscricaoMunicipal}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$numeroLote}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    $dom->loadXML($xml);

    $xmlAssinado = XmlSigner::sign(
      $dom,
      'ConsultarLoteRpsEnvio',
      null,
      $this->certPath,
      $this->certPassword
    );

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_lote_rps'),
      'ConsultarLoteRps',
      $this->gerarCabecalhoAbrasf(),
      $xmlAssinado
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
