<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  public function gerarLote(array $rpsAssinadosXml, array $emitente, int $numeroLote): string
  {
    $itensXml = implode("\n", $rpsAssinadosXml);
    $quantidade = count($rpsAssinadosXml);

    return <<<XML
<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <LoteRps Id="lote{$numeroLote}" versao="2.04">
    <NumeroLote>{$numeroLote}</NumeroLote>
    <Cnpj>{$emitente['cnpj']}</Cnpj>
    <InscricaoMunicipal>{$emitente['inscricao_municipal']}</InscricaoMunicipal>
    <QuantidadeRps>{$quantidade}</QuantidadeRps>
    <ListaRps>
      {$itensXml}
    </ListaRps>
  </LoteRps>
</EnviarLoteRpsEnvio>
XML;
  }
}
