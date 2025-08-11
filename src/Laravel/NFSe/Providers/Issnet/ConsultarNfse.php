<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\XmlSigner;
use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarNfse
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath = $certPath;
    $this->certPassword = $certPassword;
  }

  public function consultar(array $params): string
  {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;

    $cpfCnpjTag = strlen($params['cpf_cnpj_tomador'] ?? '') === 11 ? 'Cpf' : 'Cnpj';

    $cpfCnpjTomador = '';
    if (!empty($params['cpf_cnpj_tomador'])) {
      $cpfCnpjTomador = <<<XML
<Tomador>
  <CpfCnpj>
    <{$cpfCnpjTag}>{$params['cpf_cnpj_tomador']}</{$cpfCnpjTag}>
  </CpfCnpj>
</Tomador>
XML;
    }

    $numeroNfse = '';
    if (!empty($params['numero_nfse'])) {
      $numeroNfse = "<NumeroNfse>{$params['numero_nfse']}</NumeroNfse>";
    }

    $xml = <<<XML
<ConsultarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <Cnpj>{$params['cnpj']}</Cnpj>
    <InscricaoMunicipal>{$params['inscricao_municipal']}</InscricaoMunicipal>
  </Prestador>
  {$cpfCnpjTomador}
  {$numeroNfse}
</ConsultarNfseEnvio>
XML;

    $dom->loadXML($xml);

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.consultar_nfse'),
      'ConsultarNfse',
      $this->gerarCabecalhoAbrasf(),
      $xml
    );
  }

  protected function gerarCabecalhoAbrasf(): string
  {
    return <<<XML
<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04">
  <versaoDados>2.04</versaoDados>
</cabecalho>
XML;
  }
}
