<?php

namespace Lexarno\NFSe\Provedores\Issnet;

use Lexarno\NFSe\Contracts\ConsultarSituacaoLoteRpsInterface;
use Lexarno\NFSe\Support\Xml\XmlBuilder;
use Lexarno\NFSe\Support\Xml\XmlSigner;
use Lexarno\NFSe\Support\Soap\SoapRequest;

class ConsultarSituacaoLoteRps implements ConsultarSituacaoLoteRpsInterface
{
  protected string $url;

  public function __construct(string $url)
  {
    $this->url = $url;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo, string $certPath, string $certPassword): string
  {
    $xml = XmlBuilder::create('ConsultarSituacaoLoteRpsEnvio', 'http://www.abrasf.org.br/nfse.xsd')
      ->withElement('Prestador', function ($node) use ($cnpj, $inscricaoMunicipal) {
        $node->addChild('Cnpj', $cnpj);
        $node->addChild('InscricaoMunicipal', $inscricaoMunicipal);
      })
      ->withElement('Protocolo', $protocolo)
      ->toXml();

    $signedXml = XmlSigner::sign(
      $xml,
      $certPath,
      $certPassword,
      'ConsultarSituacaoLoteRpsEnvio',
      'http://www.abrasf.org.br/nfse.xsd'
    );

    return SoapRequest::send(
      $this->url,
      'ConsultarSituacaoLoteRps',
      $signedXml,
      $certPath,
      $certPassword
    );
  }
}
