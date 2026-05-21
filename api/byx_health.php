<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
require_once dirname(dirname(__FILE__)) . '/core/byx_client.php';

auth_require();

$result = byx_health();
$status = $result['ok'] ? 200 : ($result['status'] > 0 ? intval($result['status']) : 503);
http_response_code($status);
json_response($result);
