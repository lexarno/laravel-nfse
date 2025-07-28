<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  public function gerarLote(string $rpsXml, int $numeroLote): string
  {
    return <<<XML
<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
    <LoteRps Id="lote{$numeroLote}" versao="2.04">
        <NumeroLote>{$numeroLote}</NumeroLote>
        <Cnpj>SEU_CNPJ_AQUI</Cnpj>
        <InscricaoMunicipal>SUA_IM_AQUI</InscricaoMunicipal>
        <QuantidadeRps>1</QuantidadeRps>
        <ListaRps>
            {$rpsXml}
        </ListaRps>
    </LoteRps>
</EnviarLoteRpsEnvio>
XML;
  }
}
