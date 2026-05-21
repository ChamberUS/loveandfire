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

$result = byx_pay_payment_request_devnet($requestId);
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
