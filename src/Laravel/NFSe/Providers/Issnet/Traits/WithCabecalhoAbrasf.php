<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCabecalhoAbrasf
{
  /**
   * Cabeçalho ABRASF aceito pelo ISSNet:
   * - sem atributo versao
   * - apenas <versaoDados> no namespace ABRASF
   */
  protected function gerarCabecalhoAbrasf(?string $versao = null): string
  {
    $v = $versao ?: (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    return <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd">
  <versaoDados>{$v}</versaoDados>
</cabecalho>
XML;
  }
}
