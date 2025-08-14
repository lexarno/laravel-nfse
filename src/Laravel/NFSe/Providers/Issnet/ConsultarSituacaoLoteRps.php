<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath     = $certPath;
    $this->certPassword = $certPassword;
  }

  /**
   * Consulta a SITUAÇÃO do lote (1=processando, 2=erro, 3=processado).
   * Só funciona se o WSDL desse ASMX expuser essa operação; caso contrário,
   * lance exceção orientando a usar ConsultarLoteRps.
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    $versao = (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim((string) $protocolo);

    $cabecalho = <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd">
  <versaoDados>{$versao}</versaoDados>
</cabecalho>
XML;

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

    // Tenta localizar exatamente a operação de "situação"
    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['ConsultarSituacaoLoteRps', 'Situacao', 'Lote', 'Rps', 'RPS']
    );

    // Se o WSDL não publicou a operação de situação, avisa para usar ConsultarLoteRps
    if (stripos($op, 'ConsultarSituacaoLoteRps') === false) {
      throw new \RuntimeException(
        'Este endpoint não expõe a operação ConsultarSituacaoLoteRps. ' .
          'Use ConsultarLoteRps, que também retorna <Situacao>.'
      );
    }

    $soapAction = rtrim($base, '/') . '/' . $op; // ex.: http://nfse.abrasf.org.br/ConsultarSituacaoLoteRps

    return SoapRequestHelper::enviar(
      $endpoint,
      $op,            // "ConsultarSituacaoLoteRps"
      $cabecalho,
      $dados,
      ['style' => 'bare', 'soap_action' => $soapAction]
    );
  }
}
