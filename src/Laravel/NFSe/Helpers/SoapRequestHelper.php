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

  public static function enviarIssnet(string $url, string $operation, string $xmlDados, array $opts = []): string
  {
    $soapVersion = $opts['soap_version'] ?? config('nfse.issnet.soap_version', '1.1');
    $actionBase  = $opts['action_base']  ?? config('nfse.issnet.soap_action_base');

    if (!$actionBase) {
      // Deriva de .../homologaabrasf/webservicenfse204/nfse.asmx → http://www.issnetonline.com.br/webservicenfse204/
      $parts  = parse_url($url);
      $scheme = $parts['scheme'] ?? 'https';
      $host   = $parts['host']   ?? 'www.issnetonline.com.br';
      $path   = $parts['path']   ?? '';
      if (preg_match('~/(webservicenfse\d+)/~i', $path, $m)) {
        $actionBase = "{$scheme}://{$host}/{$m[1]}/";
      } else {
        $actionBase = "{$scheme}://{$host}/webservicenfse204/";
      }
    }
    if (!str_ends_with($actionBase, '/')) $actionBase .= '/';

    $soapAction = $actionBase . $operation;

    if ($soapVersion === '1.2') {
      $envelope = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                 xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <{$operation} xmlns="{$actionBase}">
      <xml><![CDATA[{$xmlDados}]]></xml>
    </{$operation}>
  </soap12:Body>
</soap12:Envelope>
XML;
      $headers = [
        'Content-Type: application/soap+xml; charset=utf-8; action="' . $soapAction . '"',
        'Content-Length: ' . strlen($envelope),
      ];
    } else {
      $envelope = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <{$operation} xmlns="{$actionBase}">
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
    }

    if (!empty($opts['debug'])) {
      \Log::info('[NFSE][ASMX] Request', [
        'url'        => $url,
        'actionBase' => $actionBase,
        'soapAction' => $soapAction,
        'version'    => $soapVersion,
        // comente a linha abaixo se o XML for muito grande/sensível:
        'envelope_head' => substr($envelope, 0, 300),
      ]);
    }

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
    if (curl_errno($ch)) throw new \Exception('Erro ao enviar SOAP: ' . curl_error($ch));
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200) throw new \Exception("Erro HTTP {$code}: {$resp}");
    return $resp;
  }

  // SoapRequestHelper.php
  public static function enviarIssnetAuto(string $url, string $operation, string $xmlDados): string
  {
    $combos = [
      ['ver' => '1.1', 'base' => 'http://www.issnetonline.com.br/webservicenfse204/'],
      ['ver' => '1.1', 'base' => 'http://www.issnetonline.com.br/webservice/nfd/'],
      ['ver' => '1.2', 'base' => 'http://www.issnetonline.com.br/webservice/nfd/'],
      ['ver' => '1.2', 'base' => 'http://www.issnetonline.com.br/webservicenfse204/'],
    ];

    $lastErr = null;
    foreach ($combos as $c) {
      try {
        return self::enviarIssnet($url, $operation, $xmlDados, [
          'action_base'  => $c['base'],
          'soap_version' => $c['ver'],
          'debug'        => true,
        ]);
      } catch (\Exception $e) {
        $msg = $e->getMessage();
        if (stripos($msg, 'No operation found for specified action') === false) {
          // Erro diferente → repropaga
          throw $e;
        }
        // Tenta o próximo combo
        $lastErr = $e;
      }
    }
    // Se nenhum combo funcionou
    throw $lastErr ?? new \RuntimeException('Falha ao resolver SOAPAction/versão do ASMX.');
  }
}
