<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  /**
   * EnviarLoteRpsEnvio (ABRASF 2.04) no formato usado pela sua prefeitura:
   * - <Prestador> DENTRO de <LoteRps>
   * - <CpfCnpj>/<Cnpj> e <InscricaoMunicipal> como filhos de <Prestador>
   */
  public function gerarLote(array $rpsAssinadosXml, array $emitente, int $numeroLote): string
  {
    $lista     = implode("\n", $rpsAssinadosXml);
    $qtd       = count($rpsAssinadosXml);
    $loteId    = "lote{$numeroLote}";

    // Apenas dígitos
    $cnpj = preg_replace('/\D+/', '', (string)($emitente['cnpj'] ?? ''));
    $im   = preg_replace('/\D+/', '', (string)($emitente['inscricao_municipal'] ?? ''));

    return <<<XML
<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <LoteRps Id="{$loteId}" versao="2.04">
    <NumeroLote>{$numeroLote}</NumeroLote>
    <Prestador>
      <CpfCnpj>
        <Cnpj>{$cnpj}</Cnpj>
      </CpfCnpj>
      <InscricaoMunicipal>{$im}</InscricaoMunicipal>
    </Prestador>
    <QuantidadeRps>{$qtd}</QuantidadeRps>
    <ListaRps>
      {$lista}
    </ListaRps>
  </LoteRps>
</EnviarLoteRpsEnvio>
XML;
  }
}
