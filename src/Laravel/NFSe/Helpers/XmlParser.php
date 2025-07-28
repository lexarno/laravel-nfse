<?php

namespace Laravel\NFSe\Helpers;

class XmlParser
{
  protected \SimpleXMLElement $xml;

  public function __construct(string $xml)
  {
    $this->xml = simplexml_load_string($xml);
    if (!$this->xml) {
      throw new \InvalidArgumentException('XML inválido.');
    }
  }

  public static function load(string $xml): \SimpleXMLElement
  {
    return simplexml_load_string($xml);
  }

  public function get(string $path, ?string $default = null): ?string
  {
    $nodes = explode('.', $path);
    $current = $this->xml;

    foreach ($nodes as $node) {
      if (isset($current->{$node})) {
        $current = $current->{$node};
      } else {
        return $default;
      }
    }

    return (string) $current;
  }

  public function raw(): \SimpleXMLElement
  {
    return $this->xml;
  }
}
