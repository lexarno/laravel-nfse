<?php

namespace Laravel\NFSe;

use Illuminate\Support\ServiceProvider;

class NFSeServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    // Mescla o config com valores padrão, se o usuário não publicar
    $this->mergeConfigFrom(__DIR__ . '/../../config/nfse.php', 'nfse');
  }

  public function boot(): void
  {
    // Publica o arquivo config/nfse.php no config do app Laravel
    $this->publishes([
      __DIR__ . '/../../config/nfse.php' => config_path('nfse.php'),
    ], 'nfse-config');
  }
}
