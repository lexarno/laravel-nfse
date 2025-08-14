<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsDataBuilder
{
    use WithCnaeNormalizer;

    public function montarRps(array $dados): string
    {
        // Decide o nó com base em cpfCnpjTomador
        $temDocTomador = !empty($dados['cpfCnpjTomador']);

        $cpf  = htmlspecialchars((string)($dados['cpfTomador'] ?? ''), ENT_XML1);
        $cnpj = htmlspecialchars((string)($dados['cnpjTomador'] ?? ''), ENT_XML1);

        $cpfCnpjXml = $temDocTomador
            ? "<CpfCnpj>\n                    <Cnpj>{$cnpj}</Cnpj>\n                </CpfCnpj>"
            : "<CpfCnpj>\n                    <Cpf>{$cpf}</Cpf>\n                </CpfCnpj>";

        $xml = <<<XML
<Rps>
    <InfDeclaracaoPrestacaoServico>
        <Rps>
            <IdentificacaoRps>
                <Numero>{$dados['numero']}</Numero>
                <Serie>{$dados['serie']}</Serie>
                <Tipo>1</Tipo>
            </IdentificacaoRps>
            <DataEmissao>{$dados['dataEmissao']}</DataEmissao>
            <Status>1</Status>
        </Rps>
        <Competencia>{$dados['dataEmissao']}</Competencia>
        <Servico>
            <Valores>
                <ValorServicos>{$dados['valorServicos']}</ValorServicos>
                <ValorDeducoes>0.00</ValorDeducoes>
                <ValorPis>{$dados['valorPis']}</ValorPis>
                <ValorCofins>{$dados['valorCofins']}</ValorCofins>
                <ValorInss>0.00</ValorInss>
                <ValorIr>{$dados['valorIr']}</ValorIr>
                <ValorCsll>{$dados['valorCsll']}</ValorCsll>
                <OutrasRetencoes>0.00</OutrasRetencoes>
                <ValorIss>{$dados['valorIss']}</ValorIss>
                <Aliquota>{$dados['aliquota']}</Aliquota>
                <DescontoIncondicionado>0.00</DescontoIncondicionado>
                <DescontoCondicionado>0.00</DescontoCondicionado>
            </Valores>
            <IssRetido>{$dados['issRetido']}</IssRetido>
            <ItemListaServico>{$dados['itemListaServico']}</ItemListaServico>
            <CodigoCnae>{$this->normalizarCodigoCnae($dados['codigoCnae'] ?? '')}</CodigoCnae>
            <Discriminacao>{$dados['descricao']}</Discriminacao>
            <CodigoMunicipio>{$dados['codigoMunicipio']}</CodigoMunicipio>
        </Servico>
        <Prestador>
            <CpfCnpj>
                <Cnpj>{$dados['cnpjPrestador']}</Cnpj>
            </CpfCnpj>
            <InscricaoMunicipal>{$dados['inscricaoMunicipio']}</InscricaoMunicipal>
        </Prestador>
        <TomadorServico>
            <IdentificacaoTomador>
                {$cpfCnpjXml}
            </IdentificacaoTomador>
            <RazaoSocial>{$dados['razaoSocialTomador']}</RazaoSocial>
            <Contato>
                <Email>{$dados['emailTomador']}</Email>
            </Contato>
        </TomadorServico>
        <RegimeEspecialTributacao>{$dados['regimeTributario']}</RegimeEspecialTributacao>
        <OptanteSimplesNacional>{$dados['optanteSimples']}</OptanteSimplesNacional>
        <IncentivoFiscal>{$dados['incentivadorCultural']}</IncentivoFiscal>
    </InfDeclaracaoPrestacaoServico>
</Rps>
XML;

        return $xml;
    }
}
