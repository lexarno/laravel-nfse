<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\XmlSigner;
use Laravel\NFSe\Helpers\SoapRequestHelper;

class CancelarNfse
{
  protected string $certPath;
  protected string $certPassword;

  public function __construct(string $certPath, string $certPassword)
  {
    $this->certPath = $certPath;
    $this->certPassword = $certPassword;
  }

  public function cancelar(array $data): string
  {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;

    $xml = <<<XML
<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Pedido>
    <InfPedidoCancelamento>
      <IdentificacaoNfse>
        <Numero>{$data['numero']}</Numero>
        <CpfCnpj>
          <Cnpj>{$data['cnpj']}</Cnpj>
        </CpfCnpj>
        <InscricaoMunicipal>{$data['inscricao_municipal']}</InscricaoMunicipal>
        <CodigoMunicipio>{$data['codigo_municipio']}</CodigoMunicipio>
      </IdentificacaoNfse>
      <CodigoCancelamento>{$data['codigo_cancelamento']}</CodigoCancelamento>
    </InfPedidoCancelamento>
  </Pedido>
</CancelarNfseEnvio>
XML;

    $dom->loadXML($xml);

    $xmlAssinado = XmlSigner::sign(
      $dom,
      'CancelarNfseEnvio',
      null,
      $this->certPath,
      $this->certPassword
    );

    return SoapRequestHelper::enviar(
      config('nfse.issnet.endpoints.cancelar_nfse'),
      'CancelarNfse',
      $this->gerarCabecalhoAbrasf(),
      $xmlAssinado
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
