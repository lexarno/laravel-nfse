<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

use DOMDocument;
use Laravel\NFSe\Helpers\XmlSigner;

trait WithSignedXml
{
  protected function assinarRps(string $xml): string
  {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;
    $dom->loadXML($xml);

    $signedXml = XmlSigner::sign(
      $dom,
      'LoteRps',
      'Id',
      $this->getCertPath(),
      $this->getCertPassword()
    );

    return $signedXml;
  }
}
