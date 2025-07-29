<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  public function gerarLote(string $rpsXml, int $numeroLote, array $dados): string
  {
    return <<<XML
<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
    <LoteRps Id="lote{$numeroLote}" versao="2.04">
        <NumeroLote>{$numeroLote}</NumeroLote>
        <Cnpj>{$dados['cnpjPrestador']}</Cnpj>
        <InscricaoMunicipal>{$dados['codigoMunicipio']}</InscricaoMunicipal>
        <QuantidadeRps>1</QuantidadeRps>
        <ListaRps>
            {$rpsXml}
        </ListaRps>
    </LoteRps>
</EnviarLoteRpsEnvio>
XML;
  }
}
