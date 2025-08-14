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
   * Consulta o conteúdo/status do lote pelo PROTOCOLO (ABRASF).
   * Retorna o XML (pode ser o SOAP bruto ou o ABRASF dentro do SOAP,
   * de acordo com o provedor).
   */
  public function consultar(string $cnpj, string $inscricaoMunicipal, string $protocolo): string
  {
    // Versão do leiaute usada no cabeçalho (ajustável por config se necessário)
    $versao = (string) (config('nfse.issnet.versao_dados') ?? '2.04');

    // Sanitize para casar com o XSD
    $cnpj = preg_replace('/\D+/', '', (string) $cnpj);
    $im   = preg_replace('/\D+/', '', (string) $inscricaoMunicipal);
    $protocolo = trim((string) $protocolo);

    // ===== CABEÇALHO ABRASF (apenas versaoDados) =====
    $cabecalho = <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd">
  <versaoDados>{$versao}</versaoDados>
</cabecalho>
XML;

    // ===== DADOS (SEM prolog, no namespace ABRASF) =====
    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    // Endpoint de consulta do lote
    $endpoint = (string) config('nfse.issnet.endpoints.consultar_lote');

    // Descobre base/operação publicadas no WSDL e monta SOAPAction exata
    [$base, $op] = SoapRequestHelper::descobrirAsmxOperacao(
      $endpoint,
      ['Lote', 'Consultar', 'Rps', 'RPS'] // prioriza métodos de lote
    );
    $soapAction = rtrim($base, '/') . '/' . $op; // ex.: http://nfse.abrasf.org.br/ConsultarLoteRps

    // Envia no estilo ABRASF "bare" (nfseCabecMsg + nfseDadosMsg)
    return SoapRequestHelper::enviar(
      $endpoint,
      $op,            // normalmente "ConsultarLoteRps"
      $cabecalho,
      $dados,
      ['style' => 'bare', 'soap_action' => $soapAction]
    );
  }
}
