<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCabecalhoAbrasf
{
  protected function gerarCabecalhoAbrasf(): string
  {
    return <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04">
    <versaoDados>2.04</versaoDados>
</cabecalho>
XML;
  }
}
