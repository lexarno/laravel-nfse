<?php

namespace Laravel\NFSe\Helpers;

use SimpleXMLElement;
//use Illuminate\Support\Facades\Log;

class XmlParser
{
  protected SimpleXMLElement $xml;

  public function __construct(string $xmlContent)
  {
    // Limpa espaços em branco ou caracteres indesejados antes da primeira tag
    $xmlContent = trim($xmlContent);
    $xmlContent = preg_replace('/^[^\<]+/', '', $xmlContent); // remove caracteres antes da primeira tag

    // Corrige possíveis problemas de encoding
    $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'auto');

    // Carrega o XML
    $xml = @simplexml_load_string($xmlContent);

    if (!$xml) {
      //Log::error('[NFSE] Falha ao carregar XML. Dump:', ['raw' => $xmlContent]);
      throw new \Exception('Erro ao carregar XML de resposta.');
    }

    $this->xml = $xml;
  }

  public function get(string $tagName): ?string
  {
    $ns = $this->xml->getNamespaces(true);
    $body = $this->xml->children($ns['s'] ?? null)->Body ?? null;

    if (!$body) {
      return null;
    }

    // Tenta pegar o conteúdo da tag dentro de qualquer nível do SOAP
    foreach ($body->children($ns[''] ?? null) as $response) {
      foreach ($response->children() as $element) {
        if (isset($element->$tagName)) {
          return (string) $element->$tagName;
        }
      }
    }

    return null;
  }
}
