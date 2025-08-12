<?php

namespace Laravel\NFSe\Helpers;

class SoapRequestHelper
{
  /**
   * @param array $opts ['style' => 'bare'|'request', 'soap_action' => string]
   */
  public static function enviar(string $url, string $operation, string $cabecalhoXml, string $xmlEnviado, array $opts = []): string
  {
    $style = $opts['style'] ?? 'bare'; // padrão: mantém o que já funciona no EnvioRps
    $soapAction = $opts['soap_action'] ?? "http://nfse.abrasf.org.br/{$operation}";

    if ($style === 'request') {
      // Abrasf "Request wrapper" (recomendado nas consultas)
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
      // MODO ANTIGO (bare) — o que já estava funcionando no seu EnvioRps
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
}
