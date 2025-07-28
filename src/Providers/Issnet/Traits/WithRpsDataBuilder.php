<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

use Carbon\Carbon;

trait WithRpsDataBuilder
{
  protected function montarDadosRps(array $dados, string $numeroRps, string $serie = '8'): array
  {
    $tomador = $dados['tomador'];

    return [
      'numero_rps' => $numeroRps,
      'serie' => $serie,
      'tipo' => 1, // RPS
      'data_emissao' => Carbon::now()->format('Y-m-d\TH:i:s'),
      'natureza_operacao' => 1,
      'regime_especial_tributacao' => 6,
      'optante_simples_nacional' => $dados['optante_simples_nacional'] ?? 1,
      'incentivador_cultural' => $dados['incentivador_cultural'] ?? 2,
      'status' => 1, // normal

      'tomador' => [
        'id' => $tomador['id'],
        'type' => $tomador['type'],
        'document' => $tomador['document'],
        'corporate_reason' => $tomador['corporate_reason'],
        'email' => $tomador['email'] ?? null,
        'phone' => $tomador['phone'] ?? null,
        'address' => $tomador['address'] ?? [],
      ],

      'codigo_tributacao_municipio' => $dados['codigo_tributacao_municipio'],
      'item_lista_servico' => $dados['item_lista_servico'],
      'codigo_cnae' => $dados['codigo_cnae'],
      'discriminacao' => $dados['discriminacao'],
      'municipio_prestacao' => $dados['municipio_prestacao'],

      'valor_servicos' => number_format($dados['valor_servicos'], 2, '.', ''),
      'aliquota' => number_format($dados['aliquota'], 4, '.', ''),
      'valor_iss' => number_format($dados['valor_iss'], 2, '.', ''),
      'valor_liquido_nfse' => number_format($dados['valor_liquido_nfse'], 2, '.', ''),

      'desconto_condicionado' => 0.00,
      'desconto_incondicionado' => 0.00,
      'valor_pis' => 0.00,
      'valor_cofins' => 0.00,
      'valor_inss' => 0.00,
      'valor_ir' => 0.00,
      'valor_csll' => 0.00,
      'base_calculo' => number_format($dados['valor_servicos'], 2, '.', ''),
    ];
  }
}
