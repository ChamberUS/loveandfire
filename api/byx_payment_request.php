<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

$requestId = request_int('request_id', 0);
$result = byx_get_payment_request($requestId);
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 400);
http_response_code($status);
json_response($result);
