<?php

namespace Laravel\NFSe\Helpers;

class SoapRequestHelper
{
  /**
   * Envia uma requisição SOAP para o provedor NFSe.
   *
   * @param string $url URL do endpoint SOAP
   * @param string $operation Nome da operação SOAP (ex: RecepcionarLoteRps)
   * @param string $cabecalhoXml Cabeçalho no padrão Abrasf (CDATA)
   * @param string $xmlEnviado XML da requisição (CDATA)
   * @return string XML de resposta do provedor
   * @throws \Exception em caso de falha na requisição
   */
  public static function enviar(string $url, string $operation, string $cabecalhoXml, string $xmlEnviado): string
  {
    $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns="http://nfse.abrasf.org.br">
    <soapenv:Header/>
    <soapenv:Body>
        <ns:$operation>
            <ns:nfseCabecMsg><![CDATA[$cabecalhoXml]]></ns:nfseCabecMsg>
            <ns:nfseDadosMsg><![CDATA[$xmlEnviado]]></ns:nfseDadosMsg>
        </ns:$operation>
    </soapenv:Body>
</soapenv:Envelope>
XML;

    $soapAction = str_contains($operation, '://')
      ? $operation
      : "http://nfse.abrasf.org.br/{$operation}";

    $headers = [
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($xml),
      'SOAPAction: "' . $soapAction . '"',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      throw new \Exception('Erro ao enviar SOAP: ' . curl_error($ch));
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($statusCode !== 200) {
      throw new \Exception("Erro HTTP $statusCode: $response");
    }

    curl_close($ch);

    return $response;
  }

  public static function enviarIssnet(string $url, string $operation, string $xmlDados): string
  {
    // SOAPAction vendor-specific (ISSNet)
    $soapAction = "http://www.issnetonline.com.br/webservice/nfd/{$operation}";

    // Envelope esperado pelo ASMX: um argumento <xml> com o conteúdo Abrasf
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
