<?php

namespace Laravel\NFSe\Helpers;

use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class XmlParser
{
  protected SimpleXMLElement $xml;

  public function __construct(string $xmlContent)
  {
    $xmlContent = trim($xmlContent);

    // Remove qualquer coisa antes da primeira tag
    $xmlContent = preg_replace('/^[^\<]+/', '', $xmlContent);

    // Corrige possível encoding
    $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'auto');

    // Tenta carregar como SimpleXML
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);

    if (!$xml) {
      Log::error('[NFSE] Falha ao carregar XML. Erros:', ['erros' => libxml_get_errors()]);
      Log::error('[NFSE] XML inválido recebido:', ['raw' => $xmlContent]);
      throw new \Exception('Erro ao carregar XML.');
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

    foreach ($body->children($ns[''] ?? null) as $response) {
      foreach ($response->children() as $node) {
        if (isset($node->$tagName)) {
          return (string) $node->$tagName;
        }

        // fallback: percorre todos os filhos
        foreach ($node->children() as $child) {
          if ($child->getName() === $tagName) {
            return (string) $child;
          }
        }
      }
    }

    return null;
  }

  /**
   * Retorna todas as tags de resposta como array associativo.
   */
  public function all(): array
  {
    $result = [];

    $ns = $this->xml->getNamespaces(true);
    $body = $this->xml->children($ns['s'] ?? null)->Body ?? null;

    if (!$body) {
      return [];
    }

    foreach ($body->children($ns[''] ?? null) as $response) {
      foreach ($response->children() as $group) {
        foreach ($group->children() as $child) {
          $result[$child->getName()] = (string) $child;
        }
      }
    }

    return $result;
  }
}
