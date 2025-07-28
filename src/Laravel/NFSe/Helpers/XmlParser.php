<?php

namespace Laravel\NFSe\Helpers;

class XmlParser
{
  public static function load(string $xml): \SimpleXMLElement
  {
    libxml_use_internal_errors(true);
    $parsed = simplexml_load_string($xml);
    if ($parsed === false) {
      throw new \RuntimeException('Erro ao interpretar XML: ' . implode(', ', array_map(fn($e) => $e->message, libxml_get_errors())));
    }

    return $parsed;
  }
}
