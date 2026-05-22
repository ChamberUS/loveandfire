<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

$requestId = request_int('request_id', 0);
if ($requestId <= 0) {
    http_response_code(400);
    json_response(array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe o ID numerico da cobranca BYX (inteiro positivo).'));
}

$result = byx_get_payment_qr($requestId);
$errorText = isset($result['error']) ? strtolower((string)$result['error']) : '';
if (!$result['ok'] && (intval($result['status']) === 502 || strpos($errorText, 'rpc') !== false)) {
    $result['error'] = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
}
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
