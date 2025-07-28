<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsBuilder
{
  protected function gerarRpsId(string $numeroLote): string
  {
    return 'pfx' . md5($numeroLote . uniqid());
  }

  protected function gerarTagLoteRps(string $idLote, string $versao = '2.04'): string
  {
    return "<LoteRps Id=\"{$idLote}\" versao=\"{$versao}\">";
  }

  protected function xmlComCData(string $xml): string
  {
    return "<![CDATA[{$xml}]]>";
  }
}
