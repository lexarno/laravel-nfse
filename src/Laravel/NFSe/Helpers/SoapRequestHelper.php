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

    $headers = [
      'Content-Type: text/xml; charset=utf-8',
      'Content-Length: ' . strlen($xml),
      "SOAPAction: \"$operation\"",
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
}
