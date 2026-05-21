# BYX Devnet Integration (Love & Fire)

## Visao geral
Integracao de teste fechado entre Love & Fire (PHP puro) e API BYX Devnet.

Base URL atual de teste:
`http://200.234.218.43:3001`

Aviso: **DEVNET / TESTE FECHADO - nao e pagamento real**.

## Configuracao
1. Copie `config/byx.example.php` para `config/byx.php`.
2. Edite `config/byx.php` e configure:
   - `BYX_API_BASE_URL`
   - `BYX_API_TOKEN`
   - `BYX_DEFAULT_LOJA_ID`
   - `BYX_DEFAULT_TIMEOUT`
   - `BYX_ENABLED`

Exemplo:
```php
<?php
define('BYX_API_BASE_URL', 'http://200.234.218.43:3001');
define('BYX_API_TOKEN', 'seu-token-devnet-aqui');
define('BYX_DEFAULT_LOJA_ID', 1);
define('BYX_DEFAULT_TIMEOUT', 20);
define('BYX_ENABLED', true);
```

## Seguranca
- Nunca colocar token BYX no JavaScript.
- Nunca commitar `config/byx.php`.
- Nunca publicar token em README, issue, print ou log.

## Endpoints internos criados
- `api/byx_health.php`
- `api/byx_merchant_saldo.php`
- `api/byx_create_payment_request.php`
- `api/byx_payment_request.php`
- `api/byx_payment_qr.php`
- `api/byx_pay_devnet.php`

Todos retornam JSON padrao:
```json
{
  "ok": true,
  "status": 200,
  "data": {},
  "error": null
}
```

## Como testar
1. Login no Love & Fire.
2. Testar health:
   - abrir `api/byx_health.php`
3. Testar saldo:
   - abrir `api/byx_merchant_saldo.php?loja_id=1`
4. Criar payment request:
   - usar `byx_wallet.php` ou POST em `api/byx_create_payment_request.php`
5. Consultar request:
   - `api/byx_payment_request.php?request_id=<id>`
6. Consultar QR:
   - `api/byx_payment_qr.php?request_id=<id>`
7. Pagar request na devnet:
   - POST em `api/byx_pay_devnet.php` com `request_id`

## Tela de apoio
- `byx_wallet.php`

A tela permite:
- ver status BYX;
- ver saldo da loja padrao;
- criar cobranca;
- consultar request;
- consultar QR;
- pagar request na devnet para teste fechado.
