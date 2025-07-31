<?php

namespace Laravel\NFSe\Helpers;

class XmlParser
{
  protected \SimpleXMLElement $xml;

  public function __construct(string $xml)
  {
    // Remove namespace temporariamente para facilitar a leitura dos nós
    $xml = preg_replace('/xmlns(:\w+)?="[^"]+"/', '', $xml);
    $this->xml = simplexml_load_string($xml);
  }

  public function get(string $tag): ?string
  {
    // Busca profunda por todos os elementos com o nome da tag
    $nodes = $this->xml->xpath("//*[local-name() = '{$tag}']");
    if ($nodes && count($nodes) > 0) {
      return (string) $nodes[0];
    }

    return null;
  }

  public function getRaw(): \SimpleXMLElement
  {
    return $this->xml;
  }
}
