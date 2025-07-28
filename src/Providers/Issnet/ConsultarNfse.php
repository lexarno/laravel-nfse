<?php

namespace Lexarno\NFSe\Provedores\Issnet;

use Lexarno\NFSe\Contracts\ConsultarNfseInterface;
use Lexarno\NFSe\Support\Xml\XmlBuilder;
use Lexarno\NFSe\Support\Xml\XmlSigner;
use Lexarno\NFSe\Support\Soap\SoapRequest;

class ConsultarNfse implements ConsultarNfseInterface
{
  protected string $url;

  public function __construct(string $url)
  {
    $this->url = $url;
  }

  public function consultar(array $params, string $certPath, string $certPassword): string
  {
    $xml = XmlBuilder::create('ConsultarNfseEnvio', 'http://www.abrasf.org.br/nfse.xsd')
      ->withElement('Prestador', function ($node) use ($params) {
        $node->addChild('Cnpj', $params['cnpj']);
        $node->addChild('InscricaoMunicipal', $params['inscricao_municipal']);
      });

    if (!empty($params['cpf_cnpj_tomador'])) {
      $xml->addChild('Tomador')
        ->addChild('CpfCnpj')
        ->addChild(strlen($params['cpf_cnpj_tomador']) === 11 ? 'Cpf' : 'Cnpj', $params['cpf_cnpj_tomador']);
    }

    if (!empty($params['numero_nfse'])) {
      $xml->addChild('NumeroNfse', $params['numero_nfse']);
    }

    $signedXml = XmlSigner::sign(
      $xml->asXML(),
      $certPath,
      $certPassword,
      'ConsultarNfseEnvio',
      'http://www.abrasf.org.br/nfse.xsd'
    );

    return SoapRequest::send(
      $this->url,
      'ConsultarNfse',
      $signedXml,
      $certPath,
      $certPassword
    );
  }
}
