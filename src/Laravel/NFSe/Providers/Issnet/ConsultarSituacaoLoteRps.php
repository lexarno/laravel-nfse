<?php

namespace Laravel\NFSe\Providers\Issnet;

use Laravel\NFSe\Helpers\SoapRequestHelper;

class ConsultarSituacaoLoteRps
{
  public function __construct(private string $certPath, private string $certPassword) {}

  public function consultar(string $cnpj, string $im, string $protocolo): string
  {
    $endpoint = config('nfse.issnet.endpoints.consultar_situacao');

    $cnpj = preg_replace('/\D+/', '', $cnpj);
    $im   = preg_replace('/\D+/', '', $im);

    // Payload Abrasf (sem declaração XML)
    $dadosAbrasf = <<<XML
<ConsultarSituacaoLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">
  <Prestador>
    <CpfCnpj><Cnpj>{$cnpj}</Cnpj></CpfCnpj>
    <InscricaoMunicipal>{$im}</InscricaoMunicipal>
  </Prestador>
  <Protocolo>{$protocolo}</Protocolo>
</ConsultarSituacaoLoteRpsEnvio>
XML;

    $isAsmx = (bool) preg_match('~\.asmx(\?|$)~i', $endpoint);

    try {
      if ($isAsmx) {
        // ISSNet ASMX: SOAPAction e wrapper <ConsultarSituacaoLoteRPS><xml>...</xml>
        return SoapRequestHelper::enviarIssnet(
          $endpoint,
          'ConsultarSituacaoLoteRPS',    // RPS em MAIÚSCULAS, como no ASMX
          $dadosAbrasf
        );
      }

      // SVC/Abrasf: envelope bare com nfseCabecMsg/nfseDadosMsg
      $versao = (string) config('nfse.issnet.versao_dados', '2.04');
      $cabecalho = sprintf(
        '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
        $versao,
        $versao
      );

      return SoapRequestHelper::enviar(
        $endpoint,
        'ConsultarSituacaoLoteRps',
        $cabecalho,
        $dadosAbrasf,
        ['style' => 'bare']
      );
    } catch (\Exception $e) {
      $msg = $e->getMessage();

      // Fallback automático se o SOAPAction não for reconhecido
      if (stripos($msg, 'No operation found for specified action') !== false) {
        if ($isAsmx) {
          // Tentou ASMX → cai para SVC
          $versao = (string) config('nfse.issnet.versao_dados', '2.04');
          $cabecalho = sprintf(
            '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="%s"><versaoDados>%s</versaoDados></cabecalho>',
            $versao,
            $versao
          );
          return SoapRequestHelper::enviar(
            $endpoint,
            'ConsultarSituacaoLoteRps',
            $cabecalho,
            $dadosAbrasf,
            ['style' => 'bare']
          );
        } else {
          // Tentou SVC → cai para ASMX
          return SoapRequestHelper::enviarIssnet(
            $endpoint,
            'ConsultarSituacaoLoteRPS',
            $dadosAbrasf
          );
        }
      }

      throw $e; // repropaga se for outro erro
    }
  }
}
