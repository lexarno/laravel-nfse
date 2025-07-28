<?php

use Laravel\NFSe\Providers\Issnet\ConsultarLoteRps;

require __DIR__ . '/../../vendor/autoload.php';

$service = new ConsultarLoteRps(
    config('nfse.issnet.endpoints.consultar_lote_rps')
);

$xml = $service->consultar('12345678000195', '123456', '001', storage_path('certs/cert.pem'), 'senha-certificado');

echo $xml;
