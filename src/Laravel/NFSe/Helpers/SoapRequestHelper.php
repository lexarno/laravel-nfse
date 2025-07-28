<?php

namespace Laravel\NFSe\Helpers;

use Exception;

class SoapRequestHelper
{
  /**
   * Envia a requisição SOAP com cURL.
   *
   * @param string $url
   * @param string $xmlEnvelope
   * @param string $certFile Caminho completo para o certificado .pem
   * @param string|null $keyFile Caminho para chave privada, se separada
   * @return string
   * @throws Exception
   */
  public static function send(string $url, string $xmlEnvelope, string $certFile, ?string $keyFile = null): string
  {
    $ch = curl_init($url);

    if ($ch === false) {
      throw new Exception('Não foi possível inicializar o cURL');
    }

    $headers = [
      "Content-Type: text/xml; charset=utf-8",
      "Content-Length: " . strlen($xmlEnvelope),
    ];

    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $xmlEnvelope,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_SSLCERT => $certFile,
      CURLOPT_SSLKEY => $keyFile ?? $certFile,
      CURLOPT_SSLKEYTYPE => 'PEM',
      CURLOPT_SSLCERTTYPE => 'PEM',
      CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      throw new Exception("Erro cURL: " . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode >= 400) {
      throw new Exception("Erro HTTP {$httpCode}: {$response}");
    }

    return $response;
  }
}
