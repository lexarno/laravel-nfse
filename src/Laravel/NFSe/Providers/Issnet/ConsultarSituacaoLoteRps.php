<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;
use Laravel\NFSe\Providers\Issnet\Traits\WithCabecalhoAbrasf;

class ConsultarSituacaoLoteRps
{
  use WithCabecalhoAbrasf;

  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath     = $certPath;
    $this->certPassword = $certPassword;
  }

  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $versao = (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    $cnpj      = preg_replace('/\D+/', '', (string) $cnpj);
    $im        = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim((string) $protocolo);

    $cabecalho = $this->gerarCabecalhoAbrasf($versao);

    $dados = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    $endpoint = (string) config('nfse.issnet.endpoints.consultar_situacao');

    if (!method_exists(SoapRequestHelper::class, 'descobrirAsmxOperacao')) {
      throw new \RuntimeException('Operação de situação requer WSDL discovery; use ConsultarLoteRps.');
    }

    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['ConsultarSituacaoLoteRps', 'Situacao', 'Lote', 'Rps', 'RPS']
    );

    if (stripos($op, 'ConsultarSituacaoLoteRps') === false) {
      throw new \RuntimeException('Este ASMX não expõe ConsultarSituacaoLoteRps. Use ConsultarLoteRps.');
    }

    $soapAction = rtrim($base, '/') . '/' . $op;

    $soapResp = SoapRequestHelper::enviar(
      $endpoint,
      $op,
      $cabecalho,
      $dados,
      ['style' => 'bare', 'soap_action' => $soapAction]
    );

    return $this->extrairMioloAbrasf($soapResp, 'ConsultarSituacaoLoteRpsResposta');
  }

  private function extrairMioloAbrasf(string $soapXml, string $tagResposta): string
  {
    if (preg_match('~<(' . preg_quote($tagResposta, '~') . ')\b[^>]*>.*?</\1>~is', $soapXml, $m)) {
      return $m[0];
    }
    if (preg_match('~<nfseDadosMsg>\s*<!\[CDATA\[(.+?)\]\]>\s*</nfseDadosMsg>~is', $soapXml, $m)) {
      return trim($m[1]);
    }
    return $soapXml;
  }
}
