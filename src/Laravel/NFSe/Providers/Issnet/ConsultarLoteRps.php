<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;
use Laravel\NFSe\Providers\Issnet\Traits\WithCabecalhoAbrasf;

class ConsultarLoteRps
{
  use WithCabecalhoAbrasf;

  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath     = $certPath;
    $this->certPassword = $certPassword;
  }

  /**
   * Consulta o Lote pelo PROTOCOLO (ABRASF).
   * Retorna o XML ABRASF PURO (<ConsultarLoteRpsResposta .../>) — sem envelope SOAP —
   * para o seu NFSeRetornoProcessor não reclamar de "XML inválido".
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $versao = (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    $cnpj      = preg_replace('/\D+/', '', (string) $cnpj);
    $im        = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim((string) $protocolo);

    // Cabeçalho ABRASF (sem atributo "versao")
    $cabecalho = $this->gerarCabecalhoAbrasf($versao);

    // Dados ABRASF da consulta (SEM prolog)
    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    $endpoint = (string) config('nfse.issnet.endpoints.consultar_lote');

    // Monta SOAPAction exata a partir do WSDL se disponível; senão usa o padrão ABRASF
    if (method_exists(SoapRequestHelper::class, 'descobrirAsmxOperacao')) {
      [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
        $endpoint,
        ['ConsultarLoteRps', 'Lote', 'Rps', 'RPS']
      );
      $soapAction = rtrim($base, '/') . '/' . $op;
    } else {
      $op         = 'ConsultarLoteRps';
      $soapAction = "http://nfse.abrasf.org.br/{$op}";
    }

    // Envia no estilo ABRASF "bare": nfseCabecMsg + nfseDadosMsg
    $soapResp = SoapRequestHelper::enviar(
      $endpoint,
      $op,
      $cabecalho,
      $dados,
      ['style' => 'bare', 'soap_action' => $soapAction]
    );

    // Retorna só o miolo ABRASF para o processor (sem o envelope SOAP)
    return $this->extrairMioloAbrasf($soapResp, 'ConsultarLoteRpsResposta');
  }

  /**
   * Extrai e retorna o <TagResposta ...>...</TagResposta> do SOAP.
   * Se não encontrar, tenta extrair o CDATA de <nfseDadosMsg>.
   * Como fallback, retorna a resposta original.
   */
  private function extrairMioloAbrasf(string $soapXml, string $tagResposta): string
  {
    // 1) Miolo direto (sem CDATA)
    if (preg_match('~<(' . preg_quote($tagResposta, '~') . ')\b[^>]*>.*?</\1>~is', $soapXml, $m)) {
      return $m[0];
    }
    // 2) CDATA dentro de nfseDadosMsg
    if (preg_match('~<nfseDadosMsg>\s*<!\[CDATA\[(.+?)\]\]>\s*</nfseDadosMsg>~is', $soapXml, $m)) {
      return trim($m[1]);
    }
    // 3) Fallback
    return $soapXml;
  }
}
