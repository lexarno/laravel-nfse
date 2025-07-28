<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithSoapHeader
{
  protected function soapEnvelope(string $body, string $namespace, string $header = ''): string
  {
    return <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns="{$namespace}">
    <soapenv:Header/>
    <soapenv:Body>
        {$body}
    </soapenv:Body>
</soapenv:Envelope>
XML;
  }
}
