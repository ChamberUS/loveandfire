<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

$defaultLoja = defined('BYX_DEFAULT_LOJA_ID') ? intval(BYX_DEFAULT_LOJA_ID) : 1;
$lojaId = request_int('loja_id', $defaultLoja);

$result = byx_get_merchant_saldo($lojaId);
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
