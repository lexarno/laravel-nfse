<?php

use Laravel\NFSe\Providers\Issnet\EnviarRps;

require __DIR__ . '/../../vendor/autoload.php';

$service = new EnviarRps(
    config('nfse.issnet.endpoints.envio_rps')
);

$xml = $service->enviar([
    'numero' => '1',
    'serie' => 'RPS',
    'data_emissao' => now()->format('Y-m-d\TH:i:s'),
    'prestador' => [
        'cnpj' => '12345678000195',
        'inscricao_municipal' => '123456'
    ],
    'tomador' => [
        'tipo' => 1,
        'documento' => '98765432100',
        'razao_social' => 'Cliente Exemplo',
        'email' => 'cliente@exemplo.com',
        'telefone' => '11999999999',
        'endereco' => [
            'logradouro' => 'Rua das Flores',
            'numero' => '123',
            'bairro' => 'Centro',
            'codigo_municipio' => '3550308',
            'uf' => 'SP',
            'cep' => '01001000'
        ]
    ],
    'codigo_municipio' => '3550308',
    'natureza_operacao' => 1,
    'item_lista_servico' => '7.02',
    'codigo_cnae' => '6201500',
    'codigo_tributacao' => '123456',
    'descricao' => 'Serviço prestado de exemplo',
    'regime_tributario' => 1,
    'simples_nacional' => 1,
    'incentivador_cultural' => 2,
    'iss_retido' => 2,
    'aliquota_iss' => 0.03,
    'valor_servicos' => 100.00,
    'desconto_condicionado' => 0.00,
    'desconto_incondicionado' => 0.00,
    'base_calculo' => 100.00,
    'valor_iss' => 3.00,
    'valor_pis' => 0.00,
    'valor_cofins' => 0.00,
    'valor_csll' => 0.00,
    'valor_inss' => 0.00,
    'valor_ir' => 0.00,
    'valor_liquido' => 97.00
], storage_path('certs/cert.pem'), 'senha-certificado');

echo $xml;
