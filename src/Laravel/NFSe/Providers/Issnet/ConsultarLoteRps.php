<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath     = $certPath;
    $this->certPassword = $certPassword;
  }

  /**
   * Consulta o lote pelo PROTOCOLO (ABRASF) usando o endpoint nfse.asmx (ABRASF 2.04)
   * e envelope "bare" (nfseCabecMsg / nfseDadosMsg).
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    // Versão do leiaute (permita override via config, se a prefeitura exigir 2.03/2.02)
    $versao = (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    // Sanitize rigoroso para bater no XSD
    $cnpj = preg_replace('/\D+/', '', $cnpj);
    $im   = preg_replace('/\D+/', '', $inscricaoMunicipal);
    $protocolo = trim($protocolo);

    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj>
      <Cnpj>{$cnpj}</Cnpj>
    </CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    // ===== ÁREA DE CABEÇALHO ABRASF =====
    // versao -> atributo; versaoDados -> elemento (tamanho 4, ex: 2.04)
    $cabecalho = <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="{$versao}">
  <versaoDados>{$versao}</versaoDados>
</cabecalho>
XML;

    // Endpoint ABRASF (o seu já está em config)
    $url = (string) config('nfse.issnet.endpoints.consultar_lote');

    // IMPORTANTE:
    // - operation: "ConsultarLoteRps"
    // - soap_action: base ABRASF, sem barra dupla estranha no final
    // - style: 'bare' (gera <ConsultarLoteRps><nfseCabecMsg/><nfseDadosMsg/></ConsultarLoteRps>)
    return SoapRequestHelper::enviar(
      $url,
      'ConsultarLoteRps',
      $cabecalho,
      $dados,
      [
        'style'       => 'bare',
        'soap_action' => 'http://nfse.abrasf.org.br/ConsultarLoteRps',
      ]
    );
  }
}
