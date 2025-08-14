<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(
    protected string $certPath,
    protected string $certPassword
  ) {}

  public function consultar(string $cnpj, string $im, string $protocolo): string
  {
    $cnpj = preg_replace('/\D+/', '', $cnpj);

    $cabecalho = <<<XML
<cabecalho versao="2.04" xmlns="http://www.abrasf.org.br/nfse.xsd">
  <versaoDados>2.04</versaoDados>
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

    $soapXml = SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_situacao'),
      'ConsultarSituacaoLoteRps',
      $cabecalho,
      $dados,
      [
        'style'       => 'bare',
        'soap_action' => 'http://nfse.abrasf.org.br/ConsultarSituacaoLoteRps',
      ]
    );

    // Alguns provedores não implementam ConsultarSituacaoLoteRps.
    // Se o WSDL não tiver essa operação, use apenas ConsultarLoteRps (abaixo).
    return self::extrairRespostaAbrasf($soapXml, 'ConsultarSituacaoLoteRpsResposta');
  }

  private static function extrairRespostaAbrasf(string $soap, string $root): string
  {
    if (preg_match('~<' . $root . '[^>]*xmlns="http://www\.abrasf\.org\.br/nfse\.xsd"[^>]*>(.*)</' . $root . '>~is', $soap, $m)) {
      return '<' . $root . ' xmlns="http://www.abrasf.org.br/nfse.xsd">' . $m[1] . '</' . $root . '>';
    }
    return $soap;
  }
}
