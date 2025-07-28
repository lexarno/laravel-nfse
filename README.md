
# 📄 Laravel NFSe - Integração com ISSNet

Este pacote fornece uma integração moderna e compatível com PHP 8.3 para emissão de Notas Fiscais de Serviço Eletrônica (NFSe), utilizando o padrão Abrasf 2.04 e comunicação com o provedor **ISSNet**.

---

## 🚀 Instalação ##

1. Adicione o pacote ao seu projeto Laravel:
```bash
composer require lexarno/laravel-nfse
```

2. Publique o arquivo de configuração:
```bash
php artisan vendor:publish --tag=laravel-nfse-config
```

---

## ⚙️ Configuração

Um arquivo será publicado em `config/nfse.php`. Exemplo:

```php
return [
    'issnet' => [
        'endpoints' => [
            'envio_rps' => 'https://example.com/issnet/recepcionar',
            'consultar_nfse' => 'https://example.com/issnet/consultar_nfse',
            'consultar_lote_rps' => 'https://example.com/issnet/consultar_lote',
            'consultar_situacao_lote_rps' => 'https://example.com/issnet/consultar_situacao',
            'cancelar_nfse' => 'https://example.com/issnet/cancelar',
        ],
    ],
];
```

---

## 📦 Funcionalidades

| Classe                             | Método       | Descrição                              |
|-----------------------------------|--------------|----------------------------------------|
| `EnviarRps`                       | `enviar()`   | Envia RPS e retorna protocolo          |
| `ConsultarSituacaoLoteRps`        | `consultar()`| Verifica situação do lote enviado      |
| `ConsultarLoteRps`               | `consultar()`| Retorna NFSe(s) de um lote             |
| `ConsultarNfse`                  | `consultar()`| Consulta NFSe por número ou tomador    |
| `CancelarNfse`                   | `cancelar()` | Cancela uma NFSe emitida               |

---

## 🔐 Certificado

Você deve utilizar um certificado `.pem` (e chave `.key`) exportado do seu `.pfx`.

---

## 🛠 Exemplo de Uso

### Envio de RPS

```php
use Laravel\NFSe\Provedores\Issnet\EnviarRps;

$service = new EnviarRps($certPath, $certPassword);

$responseXml = $service->enviar($rpsData, $numeroLote);
```

### Consulta Situação do Lote

```php
use Laravel\NFSe\Provedores\Issnet\ConsultarSituacaoLoteRps;

$service = new ConsultarSituacaoLoteRps();

$responseXml = $service->consultar(
    $cnpj,
    $inscricaoMunicipal,
    $protocolo,
    $certPath,
    $certPassword
);
```

### Cancelamento de NFSe

```php
use Laravel\NFSe\Provedores\Issnet\CancelarNfse;

$service = new CancelarNfse();

$responseXml = $service->cancelar($dadosCancelamento, $certPath, $certPassword);
```

---

## 📁 Estrutura Interna

- `Traits`: Reutilização de assinatura, construção XML, cabeçalhos SOAP.
- `Helpers`: Envio SOAP e assinatura XML centralizados.
- `Contracts`: Interfaces opcionais para cada funcionalidade.
- `Providers\Issnet`: Implementações específicas para ISSNet.

---

## 📬 Suporte

Em caso de dúvidas ou problemas, envie um pull request ou abra uma issue no repositório oficial.

---
