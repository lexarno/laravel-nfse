<?php

namespace Lexarno\NFSe\Provedores\Issnet;

use Lexarno\NFSe\Contracts\ConsultarLoteRpsInterface;
use Lexarno\NFSe\Support\Xml\XmlBuilder;
use Lexarno\NFSe\Support\Xml\XmlSigner;
use Lexarno\NFSe\Support\Soap\SoapRequest;

class ConsultarLoteRps implements ConsultarLoteRpsInterface
{
  protected string $url;

  public function __construct(string $url)
  {
    $this->url = $url;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $numeroLote, string $certPath, string $certPassword): string
  {
    $xml = XmlBuilder::create('ConsultarLoteRpsEnvio', 'http://www.abrasf.org.br/nfse.xsd')
      ->withElement('Prestador', function ($node) use ($cnpj, $inscricaoMunicipal) {
        $node->addChild('Cnpj', $cnpj);
        $node->addChild('InscricaoMunicipal', $inscricaoMunicipal);
      })
      ->addChild('Protocolo', $numeroLote);

    $signedXml = XmlSigner::sign(
      $xml->asXML(),
      $certPath,
      $certPassword,
      'ConsultarLoteRpsEnvio',
      'http://www.abrasf.org.br/nfse.xsd'
    );

    return SoapRequest::send(
      $this->url,
      'ConsultarLoteRps',
      $signedXml,
      $certPath,
      $certPassword
    );
  }
}
