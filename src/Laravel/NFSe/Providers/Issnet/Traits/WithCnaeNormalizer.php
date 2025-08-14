<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCnaeNormalizer
{
  /**
   * Normaliza o Código CNAE para 7 dígitos (sem pontos/hífens).
   * Ex.: "62.02-3-00" -> "6202300"
   */
  protected function normalizarCodigoCnae($valor): string
  {
    $d = preg_replace('/\D+/', '', (string)$valor);
    $d = substr($d, 0, 7);
    return str_pad($d, 7, '0', STR_PAD_LEFT);
  }
}
