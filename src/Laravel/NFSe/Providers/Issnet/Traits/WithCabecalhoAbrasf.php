<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCabecalhoAbrasf
{
  /**
   * Gera o nfseCabecMsg no padrão aceito pelo ISSNet:
   * apenas <versaoDados> dentro do namespace ABRASF, sem atributo "versao".
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
