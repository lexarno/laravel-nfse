<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCabecalhoAbrasf
{
  protected function gerarCabecalho(string $versao = '2.04'): string
  {
    return <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="{$versao}">
    <versaoDados>{$versao}</versaoDados>
</cabecalho>
XML;
  }

  protected function cabecalhoComCData(string $versao = '2.04'): string
  {
    return "<![CDATA[" . $this->gerarCabecalho($versao) . "]]>";
  }
}
