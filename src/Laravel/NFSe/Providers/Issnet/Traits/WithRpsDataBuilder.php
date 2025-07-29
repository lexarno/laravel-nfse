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
        <DataEmissao>{$dados['dataEmissao']}</DataEmissao>
        <NaturezaOperacao>{$dados['naturezaOperacao']}</NaturezaOperacao>
        <Servico>
            <Valores>
                <ValorServicos>{$dados['valorServicos']}</ValorServicos>
            </Valores>
            <ItemListaServico>{$dados['itemListaServico']}</ItemListaServico>
            <Discriminacao>{$dados['descricao']}</Discriminacao>
            <CodigoMunicipio>{$dados['codigoMunicipio']}</CodigoMunicipio>
        </Servico>
        <Tomador>
            <IdentificacaoTomador>
                <CpfCnpj>
                    <Cnpj>{$dados['cpfCnpjTomador']}</Cnpj>
                </CpfCnpj>
            </IdentificacaoTomador>
            <RazaoSocial>{$dados['razaoSocialTomador']}</RazaoSocial>
        </Tomador>
    </InfRps>
</Rps>
XML;

        return $xml;
    }
}
