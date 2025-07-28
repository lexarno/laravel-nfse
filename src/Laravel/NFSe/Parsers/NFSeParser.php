<?php

namespace Laravel\NFSe\Parsers;

use Laravel\NFSe\DTO\RpsData;
use Laravel\NFSe\Helpers\XmlParser;

class NFSeParser
{
  public static function fromXml(string $xml): array
  {
    $xmlObj = XmlParser::load($xml);

    $lista = [];

    foreach ($xmlObj->ListaNfse->children() as $nfse) {
      $inf = $nfse->InfNfse;

      $servico = $inf->Servico;
      $tomador = $inf->TomadorServico ?? null;
      $valores = $servico->Valores;

      $data = new RpsData();
      $data->numero = (string) $inf->Numero;
      $data->serie = (string) $inf->IdentificacaoRps->Serie;
      $data->tipo = (string) $inf->IdentificacaoRps->Tipo;
      $data->dataEmissao = (string) $inf->DataEmissao;
      $data->codigoMunicipio = (string) $servico->CodigoMunicipio;
      $data->codigoCnae = (string) $servico->CodigoCnae;
      $data->codigoTributacao = (string) $servico->CodigoTributacaoMunicipio;
      $data->itemListaServico = (string) $servico->ItemListaServico;
      $data->descricao = (string) $servico->Discriminacao;

      $data->valorServicos = (float) $valores->ValorServicos;
      $data->valorDeducoes = (float) $valores->ValorDeducoes;
      $data->valorPis = (float) $valores->ValorPis;
      $data->valorCofins = (float) $valores->ValorCofins;
      $data->valorInss = (float) $valores->ValorInss;
      $data->valorIr = (float) $valores->ValorIr;
      $data->valorCsll = (float) $valores->ValorCsll;
      $data->outrasRetencoes = (float) $valores->OutrasRetencoes;
      $data->valorIss = (float) $valores->ValorIss;
      $data->aliquota = (float) $valores->Aliquota;
      $data->descontoIncondicionado = (float) $valores->DescontoIncondicionado;
      $data->descontoCondicionado = (float) $valores->DescontoCondicionado;
      $data->valorLiquido = (float) $valores->ValorLiquidoNfse;

      $data->issRetido = ((int) $servico->IssRetido ?? 2) === 1;
      $data->regimeTributario = (int) ($inf->RegimeEspecialTributacao ?? 1);
      $data->optanteSimples = ((int) $inf->OptanteSimplesNacional ?? 2) === 1;
      $data->incentivadorCultural = ((int) $inf->IncentivadorCultural ?? 2) === 1;

      if ($tomador) {
        $doc = $tomador->IdentificacaoTomador->CpfCnpj ?? null;
        $data->cpfCnpjTomador = (string) ($doc->Cnpj ?? $doc->Cpf ?? '');
        $data->razaoSocialTomador = (string) $tomador->RazaoSocial;
        $endereco = $tomador->Endereco ?? null;
        $data->logradouro = (string) ($endereco->Endereco ?? '');
        $data->numeroEndereco = (string) ($endereco->Numero ?? '');
        $data->bairro = (string) ($endereco->Bairro ?? '');
        $data->cep = (string) ($endereco->Cep ?? '');
        $data->uf = (string) ($endereco->Uf ?? '');
        $data->codigoMunicipioTomador = (string) ($endereco->CodigoMunicipio ?? '');
        $data->emailTomador = (string) ($tomador->Contato->Email ?? '');
      }

      $lista[] = $data;
    }

    return $lista;
  }

  public static function processarRetorno(string $xml): array
  {
    $parser = new XmlParser($xml);

    return [
      'protocolo' => $parser->get('Protocolo'),
      'numero_nfse' => $parser->get('Numero'),
      'codigo_verificacao' => $parser->get('CodigoVerificacao'),
      'data_emissao' => $parser->get('DataEmissao'),
      'valor_servico' => $parser->get('Servico.Valores.ValorServicos'),
      'discriminacao' => $parser->get('Servico.Discriminacao'),
      'xml' => $xml,
    ];
  }
}
