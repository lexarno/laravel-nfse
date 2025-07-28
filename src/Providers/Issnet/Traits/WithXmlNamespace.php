<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithXmlNamespace
{
  protected function gerarTagComNamespace(string $tag, string $conteudo, string $xmlns): string
  {
    return "<{$tag} xmlns=\"{$xmlns}\">{$conteudo}</{$tag}>";
  }
}
