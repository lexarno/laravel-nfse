<?php

use Laravel\NFSe\Providers\Issnet\CancelarNfse;

require __DIR__ . '/../../vendor/autoload.php';

$service = new CancelarNfse(
    config('nfse.issnet.endpoints.cancelar_nfse')
);

$xml = $service->cancelar([
    'numero' => '12345',
    'cnpj' => '12345678000195',
    'inscricao_municipal' => '123456',
    'codigo_municipio' => '3550308',
    'codigo_cancelamento' => '1'
], storage_path('certs/cert.pem'), 'senha-certificado');

echo $xml;
