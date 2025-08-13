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

  // SoapRequestHelper.php
  public static function enviarIssnetAuto(string $url, string $operation, string $xmlDados): string
  {
    // 4 combinações mais comuns
    $combos = [
      ['ver' => '1.1', 'base' => 'http://www.issnetonline.com.br/webservice/nfd/',      'quoted' => true],
      ['ver' => '1.1', 'base' => 'http://www.issnetonline.com.br/webservice/nfd/',      'quoted' => false], // sem aspas no SOAPAction
      ['ver' => '1.2', 'base' => 'http://www.issnetonline.com.br/webservice/nfd/',      'quoted' => true],
      ['ver' => '1.1', 'base' => 'http://www.issnetonline.com.br/webservicenfse204/',   'quoted' => true],
      ['ver' => '1.2', 'base' => 'http://www.issnetonline.com.br/webservicenfse204/',   'quoted' => true],
    ];

    $lastErr = null;
    foreach ($combos as $i => $c) {
      try {
        \Log::info('[NFSE][ASMX] Tentativa', [
          'i'         => $i + 1,
          'url'       => $url,
          'version'   => $c['ver'],
          'actionBase' => $c['base'],
          'operation' => $operation,
          'soapAction' => rtrim($c['base'], '/') . '/' . $operation,
          'quoted'    => $c['quoted'],
        ]);

        return self::enviarIssnet($url, $operation, $xmlDados, [
          'action_base'  => $c['base'],
          'soap_version' => $c['ver'],
          'quoted'       => $c['quoted'],
          'debug'        => true,
        ]);
      } catch (\Exception $e) {
        $msg = $e->getMessage();
        // Só tenta a próxima combinação se for exatamente "No operation found..."
        if (stripos($msg, 'No operation found for specified action') !== false) {
          $lastErr = $e;
          continue;
        }
        // erro diferente → repropaga
        throw $e;
      }
    }
    throw $lastErr ?? new \RuntimeException('Falha ao resolver SOAPAction/versão do ASMX.');
  }

  public static function enviarIssnet(string $url, string $operation, string $xmlDados, array $opts = []): string
  {
    $soapVersion = $opts['soap_version'] ?? '1.1';
    $actionBase  = rtrim($opts['action_base'] ?? '', '/') . '/';
    $quoted      = array_key_exists('quoted', $opts) ? (bool)$opts['quoted'] : true;
    $soapAction  = $actionBase . $operation;

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
        // SOAP 1.2: action vai no Content-Type
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
        // Alguns ASMX recusam com aspas, outros exigem as aspas
        $quoted ? 'SOAPAction: "' . $soapAction . '"' : 'SOAPAction: ' . $soapAction,
      ];
    }

    if (!empty($opts['debug'])) {
      \Log::info('[NFSE][ASMX] Request efetivo', [
        'url'        => $url,
        'actionBase' => $actionBase,
        'soapAction' => $soapAction,
        'version'    => $soapVersion,
        'headers'    => $headers[0], // mostra o primeiro header (para ver versão)
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

  public static function enviarIssnetAuto11(string $url, string $xmlDados): string
  {
    // bases de namespace mais vistas no ISSNet
    $bases = [
      'http://www.issnetonline.com.br/webservice/nfd/',
      'http://www.issnetonline.com.br/webservicenfse204/',
      'http://www.issnetonline.com.br/webservicenfse/',
    ];

    // variações do nome da operação (alguns ASMX são sensíveis)
    $ops = ['ConsultarSituacaoLoteRPS', 'ConsultarSituacaoLoteRps'];

    // SOAPAction com/sem aspas
    $quotedFlags = [true, false];

    $lastErr = null;

    foreach ($bases as $base) {
      foreach ($ops as $op) {
        foreach ($quotedFlags as $quoted) {
          try {
            \Log::info('[NFSE][ASMX] Tentativa 1.1', [
              'url'        => $url,
              'actionBase' => $base,
              'operation'  => $op,
              'soapAction' => rtrim($base, '/') . '/' . $op,
              'quoted'     => $quoted,
            ]);

            return self::enviarIssnet11($url, $base, $op, $xmlDados, $quoted);
          } catch (\Exception $e) {
            $msg = $e->getMessage();
            // loga o primeiro trecho do SOAP Fault para sabermos exatamente o motivo
            if (preg_match('~<faultstring>(.*?)</faultstring>~is', $msg, $m)) {
              \Log::warning('[NFSE][ASMX] Fault', ['fault' => html_entity_decode($m[1])]);
            }
            // se for "No operation found for specified action", tenta próxima combinação
            if (stripos($msg, 'No operation found for specified action') !== false) {
              $lastErr = $e;
              continue;
            }
            // qualquer outro erro (ex.: auth, schema etc.) já é um sinal de que acertamos o endpoint/operação
            throw $e;
          }
        }
      }
    }

    throw $lastErr ?? new \RuntimeException('Não foi possível resolver SOAPAction/namespace do ASMX (1.1).');
  }

  private static function enviarIssnet11(string $url, string $actionBase, string $operation, string $xmlDados, bool $quoted): string
  {
    $actionBase = rtrim($actionBase, '/') . '/';
    $soapAction = $actionBase . $operation;

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
      $quoted ? 'SOAPAction: "' . $soapAction . '"' : 'SOAPAction: ' . $soapAction,
      'Connection: close',
      'Host: ' . parse_url($url, PHP_URL_HOST),
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST           => true,
      CURLOPT_POSTFIELDS     => $envelope,
      CURLOPT_HTTPHEADER     => $headers,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 15,
      CURLOPT_TIMEOUT        => 40,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1, // força HTTP/1.1
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
