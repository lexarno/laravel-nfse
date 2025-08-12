<?php

return [

  'issnet' => [
    'versao_dados' => env('NFS_ISSNET_VERSAO_DADOS', '2.04'),
    'endpoints' => [
      'envio_rps' => env('NFS_ISSNET_ENVIO_RPS_URL', 'https://www.issnetonline.com.br/servicos/nfse.svc'),
      'consultar_lote' => env('NFS_ISSNET_CONSULTAR_LOTE_URL', 'https://www.issnetonline.com.br/servicos/nfse.svc'),
      'consultar_nfse' => env('NFS_ISSNET_CONSULTAR_NFSE_URL', 'https://www.issnetonline.com.br/servicos/nfse.svc'),
      'consultar_situacao' => env('NFS_ISSNET_CONSULTAR_SITUACAO_URL', 'https://www.issnetonline.com.br/servicos/nfse.svc'),
      'cancelar_nfse' => env('NFS_ISSNET_CANCELAR_NFSE_URL', 'https://www.issnetonline.com.br/servicos/nfse.svc'),
    ],

  ],
];
