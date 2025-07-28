<?php

namespace Laravel\NFSe\Helpers;

class SoapRequestHelper
{
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

    $headers = [
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($xml),
      "SOAPAction: \"$operation\""
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resposta = curl_exec($ch);

    if (curl_errno($ch)) {
      throw new \Exception('Erro ao enviar SOAP: ' . curl_error($ch));
    }

    curl_close($ch);

    return $resposta;
  }
}
