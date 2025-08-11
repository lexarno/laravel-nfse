<?php

namespace App\Helpers;

use DOMDocument;

class XmlParser
{
  protected DOMDocument $dom;

  public function __construct(string $xml)
  {
    $normalized = $this->normalizeXml($xml);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    // Evita warnings e mantém CDATA como texto
    $dom->loadXML($normalized, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA);

    $this->dom = $dom;
  }

  /**
   * Obtém o valor da primeira ocorrência de uma tag.
   */
  public function get(string $tag): ?string
  {
    $nodes = $this->dom->getElementsByTagName($tag);
    return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : null;
  }

  /**
   * Remove declaração XML, declarações xmlns e prefixos (ex: s:, ns2:, soap:)
   */
  protected function normalizeXml(string $xml): string
  {
    // Remove BOM/espacos laterais
    $xml = trim($xml);

    // Remove declaração XML
    $xml = preg_replace('/<\?xml[^>]*\?>/i', '', $xml);

    // Remove TODAS as declarações de namespace (xmlns, xmlns:algo)
    $xml = preg_replace('/\s+xmlns(:\w+)?="[^"]*"/i', '', $xml);

    // Remove prefixos de namespace nas tags, ex: <s:Envelope> -> <Envelope>
    // Funciona tanto para abertura quanto fechamento
    $xml = preg_replace('/(<\/?)(\w+):/i', '$1', $xml);

    // Opcional: compacta múltiplos espaços
    $xml = preg_replace('/>\s+</', '><', $xml);

    return $xml;
  }
}
