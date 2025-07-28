<?php

namespace Laravel\NFSe\DTO;

class RpsData
{
  public string $numero;
  public string $serie;
  public string $tipo;
  public string $dataEmissao;
  public string $codigoMunicipio;
  public string $codigoCnae;
  public string $codigoTributacao;
  public string $itemListaServico;
  public string $descricao;
  public float $valorServicos;
  public float $valorDeducoes = 0.00;
  public float $valorPis = 0.00;
  public float $valorCofins = 0.00;
  public float $valorInss = 0.00;
  public float $valorIr = 0.00;
  public float $valorCsll = 0.00;
  public float $outrasRetencoes = 0.00;
  public float $valorIss = 0.00;
  public float $aliquota = 0.00;
  public float $descontoIncondicionado = 0.00;
  public float $descontoCondicionado = 0.00;
  public float $valorLiquido = 0.00;
  public bool $issRetido = false;
  public int $regimeTributario = 1;
  public bool $optanteSimples = false;
  public bool $incentivadorCultural = false;
  public string $cpfCnpjTomador = '';
  public string $razaoSocialTomador = '';
  public string $logradouro = '';
  public string $numeroEndereco = '';
  public string $bairro = '';
  public string $cep = '';
  public string $uf = '';
  public string $codigoMunicipioTomador = '';
  public string $emailTomador = '';
}
