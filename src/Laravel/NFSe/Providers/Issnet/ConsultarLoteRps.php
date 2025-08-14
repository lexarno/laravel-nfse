<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarLoteRps
{
  public function __construct(
    protected string $certPath,
    protected string $certPassword
  ) {}

  public function consultar(string $cnpj, string $im, string $protocolo): string
  {
    $cnpj = preg_replace('/\D+/', '', $cnpj);

    // Cabeçalho ABRASF 2.04 (exigido pelo manual)
    $cabecalho = <<<XML
<cabecalho versao="2.04" xmlns="http://www.abrasf.org.br/nfse.xsd">
  <versaoDados>2.04</versaoDados>
</cabecalho>
XML;

    // Dados da operação (ConsultarLoteRpsEnvio) – validar contra XSD nfse.xsd
    $dados = <<<XML
<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarLoteRpsEnvio>
XML;

    // SOAPAction ABRASF, sem barra extra no namespace
    $soapXml = SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_lote'),
      'ConsultarLoteRps',
      $cabecalho,
      $dados,
      [
        'style'       => 'bare',
        'soap_action' => 'http://nfse.abrasf.org.br/ConsultarLoteRps',
      ]
    );

    // Extrai o <ConsultarLoteRpsResposta> do envelope SOAP
    return self::extrairRespostaAbrasf($soapXml, 'ConsultarLoteRpsResposta');
  }

  private static function extrairRespostaAbrasf(string $soap, string $root): string
  {
    if (preg_match('~<' . $root . '[^>]*xmlns="http://www\.abrasf\.org\.br/nfse\.xsd"[^>]*>(.*)</' . $root . '>~is', $soap, $m)) {
      return '<' . $root . ' xmlns="http://www.abrasf.org.br/nfse.xsd">' . $m[1] . '</' . $root . '>';
    }
    // fallback: retorna o SOAP bruto para debug
    return $soap;
  }
}
