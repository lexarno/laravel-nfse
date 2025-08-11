<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\XmlSigner;
use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath = $certPath;
    $this->certPassword = $certPassword;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;

    $xml = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <Cnpj>{$cnpj}</Cnpj>
    <InscricaoMunicipal>{$inscricaoMunicipal}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    $dom->loadXML($xml);

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_situacao_lote'),
      'ConsultarSituacaoLoteRps',
      $this->gerarCabecalhoAbrasf(),
      $xml
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
