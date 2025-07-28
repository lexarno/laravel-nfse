<?php

use Laravel\NFSe\Providers\Issnet\ConsultarNfse;

require __DIR__ . '/../../vendor/autoload.php';

$service = new ConsultarNfse(
    config('nfse.issnet.endpoints.consultar_nfse')
);

$xml = $service->consultar([
    'cnpj' => '12345678000195',
    'inscricao_municipal' => '123456',
    'cpf_cnpj_tomador' => '98765432100',
    'numero_nfse' => '123456'
], storage_path('certs/cert.pem'), 'senha-certificado');

echo $xml;
