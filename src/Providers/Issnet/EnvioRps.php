<?php

namespace Laravel\NFSe\Provedores\Issnet;

use NFePHP\Common\Certificate;
use NFePHP\NFSe\NFSe;
use NFePHP\NFSe\Models\Issnet\Rps;
use NFePHP\Common\Soap\SoapCurl;
use Exception;

class EnviarRps
{
  protected NFSe $nfse;

  public function __construct(array $config, string $certificadoPfx, string $senha)
  {
    $cert = Certificate::readPfx($certificadoPfx, $senha);
    $this->nfse = new NFSe(json_encode($config), $cert);
    $this->nfse->tools->loadSoapClass(new SoapCurl());
    $this->nfse->tools->setDebugSoapMode(false);
  }

  public function enviar(array $rpsData, int $numeroLote): string
  {
    $rps = new Rps();

    // Prestador
    $rps->prestador($rps::CNPJ, $rpsData['prestador']['cnpj'], $rpsData['prestador']['inscricao_municipal']);

    // Tomador
    $rps->tomador(
      $rpsData['tomador']['tipo'],
      $rpsData['tomador']['documento'],
      '',
      $rpsData['tomador']['razao_social'],
      $rpsData['tomador']['telefone'] ?? '',
      $rpsData['tomador']['email'] ?? ''
    );

    if (!empty($rpsData['tomador']['endereco'])) {
      $endereco = $rpsData['tomador']['endereco'];
      $rps->tomadorEndereco(
        $endereco['logradouro'],
        $endereco['numero'],
        $endereco['complemento'] ?? '',
        $endereco['bairro'],
        $endereco['codigo_municipio'],
        $endereco['uf'],
        $endereco['cep']
      );
    }

    $rps->numero($rpsData['numero']);
    $rps->serie($rpsData['serie']);
    $rps->status($rps::STATUS_NORMAL);
    $rps->tipo($rps::TIPO_RPS);

    $rps->dataEmissao(new \DateTime($rpsData['data_emissao']));
    $rps->municipioPrestacaoServico($rpsData['codigo_municipio']);
    $rps->naturezaOperacao($rpsData['natureza_operacao']);
    $rps->itemListaServico($rpsData['item_lista_servico']);
    $rps->codigoCnae($rpsData['codigo_cnae']);
    $rps->codigoTributacaoMunicipio($rpsData['codigo_tributacao']);
    $rps->discriminacao($rpsData['descricao']);

    $rps->regimeEspecialTributacao($rpsData['regime_tributario']);
    $rps->optanteSimplesNacional($rpsData['simples_nacional']);
    $rps->incentivadorCultural($rpsData['incentivador_cultural']);
    $rps->issRetido($rpsData['iss_retido']);

    $rps->aliquota($rpsData['aliquota_iss']);
    $rps->valorServicos($rpsData['valor_servicos']);
    $rps->descontoCondicionado($rpsData['desconto_condicionado']);
    $rps->descontoIncondicionado($rpsData['desconto_incondicionado']);
    $rps->baseCalculo($rpsData['base_calculo']);

    $rps->valorIss($rpsData['valor_iss']);
    $rps->valorPis($rpsData['valor_pis']);
    $rps->valorCofins($rpsData['valor_cofins']);
    $rps->valorCsll($rpsData['valor_csll']);
    $rps->valorInss($rpsData['valor_inss']);
    $rps->valorIr($rpsData['valor_ir']);

    $rps->valorLiquidoNfse($rpsData['valor_liquido']);

    try {
      return $this->nfse->tools->recepcionarLoteRps($numeroLote, [$rps]);
    } catch (Exception $e) {
      throw new Exception("Erro ao enviar RPS: " . $e->getMessage(), $e->getCode(), $e);
    }
  }
}
