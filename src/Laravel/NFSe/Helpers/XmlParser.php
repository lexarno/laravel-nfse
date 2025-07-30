<?php

namespace Laravel\NFSe\Parsers;

class XmlParser
{
  protected \SimpleXMLElement $xml;

  public function __construct(string $xmlContent)
  {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlContent);

    if (!$xml) {
      throw new \Exception("Erro ao carregar XML");
    }

    // Desce até o EnviarLoteRpsResposta se for um SOAP envelope
    $body = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body ?? null;
    if ($body) {
      $resposta = $body->children('http://nfse.abrasf.org.br')->RecepcionarLoteRpsResponse ?? null;
      if ($resposta && $resposta->EnviarLoteRpsResposta) {
        $this->xml = $resposta->EnviarLoteRpsResposta;
        return;
      }
    }

    // Se não for SOAP, usa diretamente
    $this->xml = $xml;
  }

  public function get(string $key): ?string
  {
    return isset($this->xml->$key) ? (string) $this->xml->$key : null;
  }
}
