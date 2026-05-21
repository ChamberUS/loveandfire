<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(array('ok' => false, 'status' => 405, 'data' => null, 'error' => 'Metodo invalido.'));
}

function byx_api_read_json_body()
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '') {
        return array();
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return array('__invalid_json' => true);
    }
    return $decoded;
}

$jsonBody = byx_api_read_json_body();
if (isset($jsonBody['__invalid_json'])) {
    http_response_code(400);
    json_response(array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'JSON invalido.'));
}

$defaultLoja = defined('BYX_DEFAULT_LOJA_ID') ? intval(BYX_DEFAULT_LOJA_ID) : 1;
$lojaId = isset($jsonBody['loja_id']) ? intval($jsonBody['loja_id']) : (isset($_POST['loja_id']) ? intval($_POST['loja_id']) : $defaultLoja);
$amount = isset($jsonBody['amount_microbyx']) ? intval($jsonBody['amount_microbyx']) : (isset($_POST['amount_microbyx']) ? intval($_POST['amount_microbyx']) : 0);
$memo = isset($jsonBody['memo']) ? (string)$jsonBody['memo'] : (isset($_POST['memo']) ? (string)$_POST['memo'] : '');
$expires = isset($jsonBody['expires_in_seconds']) ? intval($jsonBody['expires_in_seconds']) : (isset($_POST['expires_in_seconds']) ? intval($_POST['expires_in_seconds']) : 900);

$result = byx_create_payment_request($lojaId, $amount, $memo, $expires);
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
