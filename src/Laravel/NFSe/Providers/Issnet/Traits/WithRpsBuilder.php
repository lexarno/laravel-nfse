<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  /**
   * Gera o EnviarLoteRpsEnvio com <Cnpj> e <InscricaoMunicipal>
   * diretamente sob <LoteRps> (padrão ABRASF 2.04).
   */
  public function gerarLote(array $rpsAssinadosXml, array $emitente, int $numeroLote): string
  {
    $itensXml   = implode("\n", $rpsAssinadosXml);
    $quantidade = count($rpsAssinadosXml);
    $loteId     = "lote{$numeroLote}";

    $cnpj = preg_replace('/\D+/', '', (string) ($emitente['cnpj'] ?? ''));
    $im   = preg_replace('/\D+/', '', (string) ($emitente['inscricao_municipal'] ?? ''));

    return <<<XML
<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <LoteRps Id="{$loteId}" versao="2.04">
    <NumeroLote>{$numeroLote}</NumeroLote>
    <Cnpj>{$cnpj}</Cnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
    <QuantidadeRps>{$quantidade}</QuantidadeRps>
    <ListaRps>
      {$itensXml}
    </ListaRps>
  </LoteRps>
</EnviarLoteRpsEnvio>
XML;
  }
}
