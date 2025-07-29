<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithCertificado
{
  protected string $certPath = '';
  protected string $certPassword = '';

  public function setCertificado(string $path, string $password): void
  {
    $this->certPath = $path;
    $this->certPassword = $password;
  }

  public function getCertPath(): string
  {
    return $this->certPath;
  }

  public function getCertPassword(): string
  {
    return $this->certPassword;
  }

  public function salvarCertificadoTemporario(string $conteudo, string $extensao = 'pem'): string
  {
    $arquivo = tempnam(sys_get_temp_dir(), 'cert_') . '.' . $extensao;
    file_put_contents($arquivo, $conteudo);
    return $arquivo;
  }

  public function excluirCertificadoTemporario(string $caminho): void
  {
    if (file_exists($caminho)) {
      unlink($caminho);
    }
  }
}
