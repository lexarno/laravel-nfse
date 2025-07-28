<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsDataBuilder
{
  public function montarRps(array $dados): string
  {
    // Monte o XML do RPS com base nos dados recebidos.
    // Aqui você pode usar SimpleXMLElement ou DOMDocument.
    // Retorne o XML como string.
    // Exemplo simplificado:
    $xml = <<<XML
<Rps>
    <InfRps>
        <IdentificacaoRps>
            <Numero>{$dados['numero']}</Numero>
            <Serie>{$dados['serie']}</Serie>
            <Tipo>1</Tipo>
        </IdentificacaoRps>
        <DataEmissao>{$dados['data_emissao']}</DataEmissao>
        <NaturezaOperacao>{$dados['natureza_operacao']}</NaturezaOperacao>
        <Servico>
            <Valores>
                <ValorServicos>{$dados['valor_servicos']}</ValorServicos>
            </Valores>
            <ItemListaServico>{$dados['item_lista_servico']}</ItemListaServico>
            <Discriminacao>{$dados['descricao']}</Discriminacao>
            <CodigoMunicipio>{$dados['codigo_municipio']}</CodigoMunicipio>
        </Servico>
        <Prestador>
            <Cnpj>{$dados['prestador']['cnpj']}</Cnpj>
            <InscricaoMunicipal>{$dados['prestador']['inscricao_municipal']}</InscricaoMunicipal>
        </Prestador>
        <Tomador>
            <IdentificacaoTomador>
                <CpfCnpj><Cnpj>{$dados['tomador']['documento']}</Cnpj></CpfCnpj>
            </IdentificacaoTomador>
            <RazaoSocial>{$dados['tomador']['razao_social']}</RazaoSocial>
        </Tomador>
    </InfRps>
</Rps>
XML;

    return $xml;
  }
}
