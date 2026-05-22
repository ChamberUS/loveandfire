<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    json_response(array('ok' => false, 'status' => 405, 'data' => null, 'error' => 'Metodo invalido.'));
}

$raw = file_get_contents('php://input');
$jsonBody = array();
if (is_string($raw) && trim($raw) !== '') {
    $decoded = json_decode($raw, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        json_response(array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'JSON invalido.'));
    }
    if (is_array($decoded)) {
        $jsonBody = $decoded;
    }
}

$requestId = isset($jsonBody['request_id']) ? intval($jsonBody['request_id']) : (isset($_POST['request_id']) ? intval($_POST['request_id']) : 0);
if ($requestId <= 0) {
    http_response_code(400);
    json_response(array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe o ID numerico da cobranca BYX (inteiro positivo).'));
}

$requestInfo = byx_get_payment_request($requestId);
if (!$requestInfo['ok']) {
    $errorText = isset($requestInfo['error']) ? strtolower((string)$requestInfo['error']) : '';
    if (intval($requestInfo['status']) === 502 || strpos($errorText, 'rpc') !== false) {
        $requestInfo['error'] = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
    }
    $status = $requestInfo['status'] > 0 ? intval($requestInfo['status']) : 400;
    http_response_code($status);
    json_response($requestInfo);
}

$statusText = byx_extract_payment_request_status_from_data(isset($requestInfo['data']) ? $requestInfo['data'] : null);
if ($statusText === 'PAYMENT_STATUS_PAID') {
    http_response_code(409);
    json_response(array('ok' => false, 'status' => 409, 'data' => isset($requestInfo['data']) ? $requestInfo['data'] : null, 'error' => 'Essa cobranca ja foi paga.'));
}
if ($statusText !== 'PAYMENT_STATUS_PENDING') {
    http_response_code(409);
    json_response(array('ok' => false, 'status' => 409, 'data' => isset($requestInfo['data']) ? $requestInfo['data'] : null, 'error' => 'Essa cobranca nao esta pendente e nao pode ser paga.'));
}

$result = byx_pay_payment_request_devnet($requestId);
$errorText = isset($result['error']) ? strtolower((string)$result['error']) : '';
if (!$result['ok'] && (intval($result['status']) === 502 || strpos($errorText, 'rpc') !== false)) {
    $result['error'] = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
}
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
