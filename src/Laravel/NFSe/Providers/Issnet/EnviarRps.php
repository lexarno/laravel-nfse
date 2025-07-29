<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;
use Laravel\NFSe\Providers\Issnet\Traits\WithSoapHeader;
use Laravel\NFSe\Providers\Issnet\Traits\WithCabecalhoAbrasf;
use Laravel\NFSe\Providers\Issnet\Traits\WithRpsBuilder;
use Laravel\NFSe\Providers\Issnet\Traits\WithRpsDataBuilder;
use Laravel\NFSe\Providers\Issnet\Traits\WithSignedXml;
use Laravel\NFSe\Providers\Issnet\Traits\WithXmlNamespace;
use Laravel\NFSe\Providers\Issnet\Traits\WithCertificado;

class EnviarRps
{
  use WithSoapHeader;
  use WithCabecalhoAbrasf;
  use WithRpsBuilder;
  use WithRpsDataBuilder;
  use WithSignedXml;
  use WithXmlNamespace;
  use WithCertificado;

  public function enviar(array $rpsList, array $emitente, int $numeroLote, string $certPath, string $certPassword): string
  {
    // Inicializa os dados do certificado antes de qualquer uso
    $this->setCertificado($certPath, $certPassword);

    $rpsXmlList = [];

    foreach ($rpsList as $rpsData) {
      $xml = $this->montarRps($rpsData);
      $xmlAssinado = $this->assinarRps($xml); // aqui getCertPath é chamado
      $rpsXmlList[] = $xmlAssinado;
    }

    $lote = $this->gerarLote($rpsXmlList, $emitente, $numeroLote);
    $xmlAssinado = $this->assinarRps($lote);
    $cabecalho = $this->gerarCabecalhoAbrasf();

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.envio_rps'),
      'RecepcionarLoteRps',
      $cabecalho,
      $xmlAssinado
    );
  }
}
