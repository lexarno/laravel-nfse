<?php

namespace Laravel\NFSe\Helpers;

class SoapRequestHelper
{
  /**
   * @param array $opts ['style' => 'bare'|'request', 'soap_action' => string]
   */
  public static function enviar(string $url, string $operation, string $cabecalhoXml, string $xmlEnviado, array $opts = []): string
  {
    $style = $opts['style'] ?? 'bare';
    $soapAction = $opts['soap_action'] ?? "http://nfse.abrasf.org.br/{$operation}";

    if ($style === 'request') {
      $envelope = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfse="http://nfse.abrasf.org.br">
  <soapenv:Header/>
  <soapenv:Body>
    <nfse:{$operation}Request>
      <nfseCabecMsg><![CDATA[{$cabecalhoXml}]]></nfseCabecMsg>
      <nfseDadosMsg><![CDATA[{$xmlEnviado}]]></nfseDadosMsg>
    </nfse:{$operation}Request>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    } else {
      $envelope = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns="http://nfse.abrasf.org.br">
  <soapenv:Header/>
  <soapenv:Body>
    <ns:{$operation}>
      <ns:nfseCabecMsg><![CDATA[{$cabecalhoXml}]]></ns:nfseCabecMsg>
      <ns:nfseDadosMsg><![CDATA[{$xmlEnviado}]]></ns:nfseDadosMsg>
    </ns:{$operation}>
  </soapenv:Body>
</soapenv:Envelope>
XML;
    }

    $headers = [
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($envelope),
      'SOAPAction: "' . $soapAction . '"',
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $envelope,
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
      throw new \Exception('Erro ao enviar SOAP: ' . curl_error($ch));
    }
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($statusCode !== 200) {
      throw new \Exception("Erro HTTP $statusCode: $response");
    }
    return $response;
  }

  public static function enviarIssnet(string $url, string $operation, string $xmlDados): string
  {
    // operation: ex. "ConsultarSituacaoLoteRPS" (RPS em MAIÚSCULAS)
    $soapAction = "http://www.issnetonline.com.br/webservice/nfd/{$operation}";

    $envelope = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <{$operation} xmlns="http://www.issnetonline.com.br/webservice/nfd">
      <xml><![CDATA[{$xmlDados}]]></xml>
    </{$operation}>
  </soap:Body>
</soap:Envelope>
XML;

    $headers = [
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($envelope),
      'SOAPAction: "' . $soapAction . '"',
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $envelope,
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
      throw new \Exception('Erro ao enviar SOAP: ' . curl_error($ch));
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) {
      throw new \Exception("Erro HTTP {$code}: {$resp}");
    }
    return $resp;
  }
}
