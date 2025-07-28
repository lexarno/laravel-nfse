<?php

use Laravel\NFSe\Providers\Issnet\ConsultarSituacaoLoteRps;

require __DIR__ . '/../../vendor/autoload.php';

$service = new ConsultarSituacaoLoteRps(
    config('nfse.issnet.endpoints.consultar_situacao_lote')
);

$xml = $service->consultar('12345678000195', '123456', '001', storage_path('certs/cert.pem'), 'senha-certificado');

echo $xml;
