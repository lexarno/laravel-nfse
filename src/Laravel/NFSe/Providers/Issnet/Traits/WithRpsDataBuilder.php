<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithRpsDataBuilder
{
    use WithCnaeNormalizer;

    /**
     * Formata valores monetários com 2 casas e ponto.
     */
    private function fmtMoney($v): string
    {
        return number_format((float)$v, 2, '.', '');
    }

    /**
     * Escapa texto simples para XML (quando não for usar CDATA).
     */
    private function x(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1);
    }

    public function montarRps(array $dados): string
    {
        // --------- Normalizações / defaults ----------
        $valorServicos           = (float)($dados['valorServicos']            ?? 0);
        $valorDeducoes           = (float)($dados['valorDeducoes']            ?? 0);
        $descontoIncondicionado  = (float)($dados['descontoIncondicionado']   ?? 0);
        $descontoCondicionado    = (float)($dados['descontoCondicionado']     ?? 0);
        $valorPis                = (float)($dados['valorPis']                 ?? 0);
        $valorCofins             = (float)($dados['valorCofins']              ?? 0);
        $valorInss               = (float)($dados['valorInss']                ?? 0);
        $valorIr                 = (float)($dados['valorIr']                  ?? 0);
        $valorCsll               = (float)($dados['valorCsll']                ?? 0);
        $outrasRetencoes         = (float)($dados['outrasRetencoes']          ?? 0);
        $aliquotaPercent         = (float)($dados['aliquota']                 ?? 0); // em %
        $issRetido               = (string)($dados['issRetido']               ?? '2'); // 1=sim, 2=não
        $itemListaServico        = (string)($dados['itemListaServico']        ?? '');
        $codigoCnae              = (string)($dados['codigoCnae']              ?? '');
        $descricao               = (string)($dados['descricao']               ?? '');
        $codigoMunicipio         = (string)($dados['codigoMunicipio']         ?? '');
        $cnpjPrestador           = (string)($dados['cnpjPrestador']           ?? '');
        $inscricaoMunicipal      = (string)($dados['inscricaoMunicipio']      ?? ''); // ajuste se o índice real for outro
        $razaoSocialTomador      = (string)($dados['razaoSocialTomador']      ?? '');
        $emailTomador            = (string)($dados['emailTomador']            ?? '');
        $numeroTomador           = (string)($dados['numeroTomador']           ?? '');
        $enderecoTomador         = (string)($dados['enderecoTomador']         ?? '');
        $bairroTomador           = (string)($dados['bairroTomador']           ?? '');
        $ufTomador               = (string)($dados['ufTomador']               ?? '');
        $cepTomador              = preg_replace('/\D+/', '', (string)($dados['cepTomador'])              ?? '');
        $codigoMunicipioTomador  = (string)($dados['codigoMunicipioTomador']  ?? '');
        $regimeTributario        = (string)($dados['regimeTributario']        ?? '');
        $optanteSimples          = (string)($dados['optanteSimples']          ?? '');
        $incentivadorCultural    = (string)($dados['incentivadorCultural']    ?? '');
        $numero                  = (string)($dados['numero']                  ?? '');
        $serie                   = (string)($dados['serie']                   ?? '');
        $dataEmissao             = (string)($dados['dataEmissao']             ?? date('Y-m-d\TH:i:s'));

        // Base de cálculo: serviços - deduções - desconto incondicionado
        $baseCalculo = max(0, $valorServicos - $valorDeducoes - $descontoIncondicionado);

        // ValorIss coerente com a alíquota em %
        $valorIss = round($baseCalculo * ($aliquotaPercent / 100), 2);

        // --------- CPF/CNPJ Tomador ----------
        $cpfTomador  = trim((string)($dados['cpfTomador']  ?? ''));
        $cnpjTomador = trim((string)($dados['cnpjTomador'] ?? ''));

        if ($cnpjTomador !== '') {
            $cpfCnpjTomadorXml = "<CpfCnpj>\n                    <Cnpj>{$this->x($cnpjTomador)}</Cnpj>\n                </CpfCnpj>";
        } elseif ($cpfTomador !== '') {
            $cpfCnpjTomadorXml = "<CpfCnpj>\n                    <Cpf>{$this->x($cpfTomador)}</Cpf>\n                </CpfCnpj>";
        } else {
            // Se por algum motivo não houver documento (alguns municípios aceitam),
            // envie o bloco vazio, ou trate antes de montar o RPS.
            $cpfCnpjTomadorXml = "<CpfCnpj/>\n";
        }

        // --------- XML ----------
        // OBS: Campos monetários com 2 casas para ficar consistente com validadores mais rígidos.
        $xml = <<<XML
<Rps>
    <InfDeclaracaoPrestacaoServico>
        <Rps>
            <IdentificacaoRps>
                <Numero>{$this->x($numero)}</Numero>
                <Serie>{$this->x($serie)}</Serie>
                <Tipo>1</Tipo>
            </IdentificacaoRps>
            <DataEmissao>{$this->x($dataEmissao)}</DataEmissao>
            <Status>1</Status>
        </Rps>
        <Competencia>{$this->x($dataEmissao)}</Competencia>
        <Servico>
            <Valores>
                <ValorServicos>{$this->fmtMoney($valorServicos)}</ValorServicos>
                <ValorDeducoes>{$this->fmtMoney($valorDeducoes)}</ValorDeducoes>
                <ValorPis>{$this->fmtMoney($valorPis)}</ValorPis>
                <ValorCofins>{$this->fmtMoney($valorCofins)}</ValorCofins>
                <ValorInss>{$this->fmtMoney($valorInss)}</ValorInss>
                <ValorIr>{$this->fmtMoney($valorIr)}</ValorIr>
                <ValorCsll>{$this->fmtMoney($valorCsll)}</ValorCsll>
                <OutrasRetencoes>{$this->fmtMoney($outrasRetencoes)}</OutrasRetencoes>
                <ValorIss>{$this->fmtMoney($valorIss)}</ValorIss>
                <Aliquota>{$this->x((string)$aliquotaPercent)}</Aliquota>
                <DescontoIncondicionado>{$this->fmtMoney($descontoIncondicionado)}</DescontoIncondicionado>
                <DescontoCondicionado>{$this->fmtMoney($descontoCondicionado)}</DescontoCondicionado>
            </Valores>
            <IssRetido>{$this->x($issRetido)}</IssRetido>
            <ItemListaServico>{$this->x($itemListaServico)}</ItemListaServico>
            <CodigoCnae>{$this->x($this->normalizarCodigoCnae($codigoCnae))}</CodigoCnae>
            <CodigoTributacaoMunicipio>370319</CodigoTributacaoMunicipio>
            <Discriminacao><![CDATA[{$descricao}]]></Discriminacao>
            <CodigoMunicipio>{$this->x($codigoMunicipio)}</CodigoMunicipio>
            <ExigibilidadeISS>1</ExigibilidadeISS>
        </Servico>
        <Prestador>
            <CpfCnpj>
                <Cnpj>{$this->x($cnpjPrestador)}</Cnpj>
            </CpfCnpj>
            <InscricaoMunicipal>{$this->x($inscricaoMunicipal)}</InscricaoMunicipal>
        </Prestador>
        <TomadorServico>
            <IdentificacaoTomador>
                {$cpfCnpjTomadorXml}
            </IdentificacaoTomador>
            <RazaoSocial>{$this->x($razaoSocialTomador)}</RazaoSocial>
            <Endereco>
                <Endereco>{$this->x($enderecoTomador)}</Endereco>
                <Numero>{$this->x($numeroTomador)}</Numero>
                <Bairro>{$this->x($bairroTomador)}</Bairro>
                <CodigoMunicipio>{$codigoMunicipioTomador}</CodigoMunicipio>
                <Uf>{$ufTomador}</Uf>
                <Cep>{$cepTomador}</Cep>
            </Endereco>
            <Contato>
                <Email>{$this->x($emailTomador)}</Email>
            </Contato>
        </TomadorServico>
        <RegimeEspecialTributacao>{$this->x($regimeTributario)}</RegimeEspecialTributacao>
        <OptanteSimplesNacional>{$this->x($optanteSimples)}</OptanteSimplesNacional>
        <IncentivoFiscal>{$this->x($incentivadorCultural)}</IncentivoFiscal>
    </InfDeclaracaoPrestacaoServico>
</Rps>
XML;

        return $xml;
    }
}
