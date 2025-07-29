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

  public function enviar(object $dados, int $numeroLote): string
  {
    $rps = $this->montarRps($dados);
    $rpsAssinado = $this->assinarRps($rps);
    $lote = $this->gerarLote($rpsAssinado, $numeroLote, $dados);
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
