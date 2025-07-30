<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

use Laravel\NFSe\Helpers\XmlSigner;

trait WithSignedXml
{
  protected function assinarRps(string|\DOMDocument $xml): string
  {
    // Se for um DOMDocument, converte para string
    if ($xml instanceof \DOMDocument) {
      $xml = $xml->saveXML();
    }

    // Remove declaração XML
    $xml = trim(preg_replace('/<\?xml[^>]+\?>/', '', $xml));

    return XmlSigner::sign(
      $xml,
      'Rps',
      'Id',
      $this->getCertPath(),
      $this->getCertPassword()
    );
  }

  protected function assinarLote(string|\DOMDocument $xml): string
  {
    // Se for um DOMDocument, converte para string
    if ($xml instanceof \DOMDocument) {
      $xml = $xml->saveXML();
    }

    // Remove declaração XML
    $xml = trim(preg_replace('/<\?xml[^>]+\?>/', '', $xml));

    return XmlSigner::sign(
      $xml,
      'LoteRps',
      'Id',
      $this->getCertPath(),
      $this->getCertPassword()
    );
  }
}
