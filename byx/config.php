<?php
/*
|------------------------------------------------------------------
| Configuracao local da API BYX (somente servidor)
|------------------------------------------------------------------
| Nunca exponha BYX_API_TOKEN em frontend/JS/HTML.
| Em ambiente real, use token privado e rotacione se vazou.
*/
if (!defined('BYX_API_BASE_URL')) {
    define('BYX_API_BASE_URL', 'https://URL-DA-API-BYX-AQUI');
}

if (!defined('BYX_API_TOKEN')) {
    define('BYX_API_TOKEN', 'TOKEN-REAL-AQUI');
}

if (!defined('BYX_API_TIMEOUT')) {
    define('BYX_API_TIMEOUT', 20);
}

if (!defined('BYX_DIAMOND_RATE')) {
    define('BYX_DIAMOND_RATE', 0.01000000);
}

if (!defined('BYX_BRL_RATE')) {
    define('BYX_BRL_RATE', 1.00);
}

if (!defined('BYX_MERCHANT_ID')) {
    define('BYX_MERCHANT_ID', 'ID-DO-MERCHANT-AQUI');
}
