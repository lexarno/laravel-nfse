<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCertificado
{
  protected function salvarCertificadoTemporario(string $conteudo, string $nomeArquivo): string
  {
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $nomeArquivo;
    file_put_contents($path, $conteudo);
    return $path;
  }

  protected function excluirCertificadoTemporario(string $path): void
  {
    if (file_exists($path)) {
      unlink($path);
    }
  }
}
