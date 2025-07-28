<?php

namespace Lexarno\NFSe\Provedores\Issnet;

use Lexarno\NFSe\Contracts\CancelarNfseInterface;
use Lexarno\NFSe\Support\Xml\XmlBuilder;
use Lexarno\NFSe\Support\Xml\XmlSigner;
use Lexarno\NFSe\Support\Soap\SoapRequest;

class CancelarNfse implements CancelarNfseInterface
{
  protected string $url;

  public function __construct(string $url)
  {
    $this->url = $url;
  }

  public function cancelar(array $dados, string $certPath, string $certPassword): string
  {
    $xml = XmlBuilder::create('CancelarNfseEnvio', 'http://www.abrasf.org.br/nfse.xsd')
      ->withElement('Pedido', function ($node) use ($dados) {
        $node->withElement('InfPedidoCancelamento', function ($n) use ($dados) {
          $n->withElement('IdentificacaoNfse', function ($id) use ($dados) {
            $id->addChild('Numero', $dados['numero']);
            $id->withElement('CpfCnpj', function ($doc) use ($dados) {
              $doc->addChild(strlen($dados['cnpj']) === 14 ? 'Cnpj' : 'Cpf', $dados['cnpj']);
            });
            $id->addChild('InscricaoMunicipal', $dados['inscricao_municipal']);
            $id->addChild('CodigoMunicipio', $dados['codigo_municipio']);
          });
          $n->addChild('CodigoCancelamento', $dados['codigo_cancelamento'] ?? '1');
        });
      });

    $signedXml = XmlSigner::sign(
      $xml->asXML(),
      $certPath,
      $certPassword,
      'CancelarNfseEnvio',
      'http://www.abrasf.org.br/nfse.xsd'
    );

    return SoapRequest::send(
      $this->url,
      'CancelarNfse',
      $signedXml,
      $certPath,
      $certPassword
    );
  }
}
