<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

$GLOBALS['HC_BYX_CONFIG_LOADED'] = false;
$GLOBALS['HC_BYX_CONFIG_ERROR'] = null;

function byx_load_config()
{
    if ($GLOBALS['HC_BYX_CONFIG_LOADED']) {
        return;
    }
    $GLOBALS['HC_BYX_CONFIG_LOADED'] = true;

    $configPath = dirname(dirname(__FILE__)) . '/config/byx.php';
    if (!is_file($configPath)) {
        $GLOBALS['HC_BYX_CONFIG_ERROR'] = 'BYX not configured';
        return;
    }

    require_once $configPath;
}

function byx_not_configured_response()
{
    return array(
        'ok' => false,
        'status' => 0,
        'data' => null,
        'error' => 'BYX not configured'
    );
}

function byx_is_enabled()
{
    byx_load_config();
    if ($GLOBALS['HC_BYX_CONFIG_ERROR']) {
        return false;
    }
    if (!defined('BYX_ENABLED') || !BYX_ENABLED) {
        return false;
    }
    if (!defined('BYX_API_BASE_URL') || trim((string)BYX_API_BASE_URL) === '') {
        return false;
    }
    return true;
}

function byx_request($method, $path, $body = null, $auth = true)
{
    byx_load_config();

    if (!byx_is_enabled()) {
        return byx_not_configured_response();
    }

    $baseUrl = rtrim((string)BYX_API_BASE_URL, '/');
    $path = '/' . ltrim((string)$path, '/');
    $url = $baseUrl . $path;

    $timeout = defined('BYX_DEFAULT_TIMEOUT') ? intval(BYX_DEFAULT_TIMEOUT) : 20;
    if ($timeout <= 0) {
        $timeout = 20;
    }

    $headers = array('Content-Type: application/json');
    if ($auth) {
        if (!defined('BYX_API_TOKEN') || trim((string)BYX_API_TOKEN) === '') {
            return byx_not_configured_response();
        }
        $headers[] = 'Authorization: Bearer ' . BYX_API_TOKEN;
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return array(
            'ok' => false,
            'status' => 0,
            'data' => null,
            'error' => 'Unable to initialize HTTP client'
        );
    }

    $method = strtoupper((string)$method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($body !== null) {
        $encoded = json_encode($body);
        if ($encoded === false) {
            curl_close($ch);
            return array(
                'ok' => false,
                'status' => 0,
                'data' => null,
                'error' => 'Invalid payload'
            );
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
    }

    $raw = curl_exec($ch);
    if ($raw === false) {
        $curlError = curl_error($ch);
        curl_close($ch);
        return array(
            'ok' => false,
            'status' => 0,
            'data' => null,
            'error' => $curlError ? ('Connection error: ' . $curlError) : 'Connection error'
        );
    }

    $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    curl_close($ch);

    $decoded = null;
    if ($raw !== '' && $raw !== null) {
        $decoded = json_decode($raw, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'ok' => false,
                'status' => $status,
                'data' => null,
                'error' => 'Invalid JSON response'
            );
        }
    }

    $ok = ($status >= 200 && $status < 300);
    $error = null;
    if (!$ok) {
        if (is_array($decoded)) {
            if (isset($decoded['error']) && is_string($decoded['error']) && $decoded['error'] !== '') {
                $error = $decoded['error'];
            } elseif (isset($decoded['message']) && is_string($decoded['message']) && $decoded['message'] !== '') {
                $error = $decoded['message'];
            }
        }
        if ($error === null) {
            $error = 'BYX request failed';
        }
    }

    return array(
        'ok' => $ok,
        'status' => $status,
        'data' => is_array($decoded) ? $decoded : null,
        'error' => $error
    );
}

function byx_health()
{
    return byx_request('GET', '/v1/devnet/health', null, false);
}

function byx_get_merchant($lojaId)
{
    $lojaId = intval($lojaId);
    if ($lojaId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid loja_id');
    }
    return byx_request('GET', '/v1/devnet/merchants/' . $lojaId);
}

function byx_get_merchant_saldo($lojaId)
{
    $lojaId = intval($lojaId);
    if ($lojaId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid loja_id');
    }
    return byx_request('GET', '/v1/devnet/merchants/' . $lojaId . '/saldo');
}

function byx_create_payment_request($lojaId, $amountMicrobyx, $memo = '', $expiresInSeconds = 900)
{
    $lojaId = intval($lojaId);
    $amountMicrobyx = intval($amountMicrobyx);
    $expiresInSeconds = intval($expiresInSeconds);

    if ($lojaId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid loja_id');
    }
    if ($amountMicrobyx <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid amount_microbyx');
    }
    if ($expiresInSeconds <= 0) {
        $expiresInSeconds = 900;
    }

    $payload = array(
        'loja_id' => $lojaId,
        'amount_microbyx' => $amountMicrobyx,
        'memo' => (string)$memo,
        'expires_in_seconds' => $expiresInSeconds
    );

    return byx_request('POST', '/v1/devnet/payment-requests', $payload);
}

function byx_get_payment_request($requestId)
{
    $requestId = intval($requestId);
    if ($requestId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid request_id');
    }
    return byx_request('GET', '/v1/devnet/payment-requests/' . $requestId);
}

function byx_get_payment_qr($requestId)
{
    $requestId = intval($requestId);
    if ($requestId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid request_id');
    }
    return byx_request('GET', '/v1/devnet/payment-requests/' . $requestId . '/qr');
}

function byx_pay_payment_request_devnet($requestId)
{
    $requestId = intval($requestId);
    if ($requestId <= 0) {
        return array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Invalid request_id');
    }
    return byx_request('POST', '/v1/devnet/payment-requests/' . $requestId . '/pay', array());
}

function byx_is_positive_int_value($value)
{
    if (is_int($value)) {
        return $value > 0;
    }
    if (is_string($value)) {
        return (bool)preg_match('/^[1-9][0-9]*$/', trim($value));
    }
    return false;
}

function byx_extract_numeric_payment_request_id_from_data($data)
{
    if (!is_array($data)) {
        return 0;
    }

    $candidates = array();
    if (isset($data['payment_request_id'])) { $candidates[] = $data['payment_request_id']; }
    if (isset($data['id'])) { $candidates[] = $data['id']; }
    if (isset($data['request_id']) && byx_is_positive_int_value($data['request_id'])) { $candidates[] = $data['request_id']; }

    if (isset($data['payment_request']) && is_array($data['payment_request'])) {
        if (isset($data['payment_request']['payment_request_id'])) { $candidates[] = $data['payment_request']['payment_request_id']; }
        if (isset($data['payment_request']['id'])) { $candidates[] = $data['payment_request']['id']; }
        if (isset($data['payment_request']['request_id']) && byx_is_positive_int_value($data['payment_request']['request_id'])) {
            $candidates[] = $data['payment_request']['request_id'];
        }
    }

    foreach ($candidates as $candidate) {
        if (byx_is_positive_int_value($candidate)) {
            return intval($candidate);
        }
    }

    return 0;
}

function byx_extract_payment_request_status_from_data($data)
{
    if (!is_array($data)) {
        return '';
    }
    if (isset($data['status']) && is_string($data['status'])) {
        return trim($data['status']);
    }
    if (isset($data['payment_status']) && is_string($data['payment_status'])) {
        return trim($data['payment_status']);
    }
    if (isset($data['payment_request']) && is_array($data['payment_request'])) {
        if (isset($data['payment_request']['status']) && is_string($data['payment_request']['status'])) {
            return trim($data['payment_request']['status']);
        }
        if (isset($data['payment_request']['payment_status']) && is_string($data['payment_request']['payment_status'])) {
            return trim($data['payment_request']['payment_status']);
        }
    }
    return '';
}

function byx_extract_payment_request_amount_microbyx_from_data($data)
{
    if (!is_array($data)) {
        return 0;
    }
    $candidates = array();
    if (isset($data['amount_microbyx'])) { $candidates[] = $data['amount_microbyx']; }
    if (isset($data['amount'])) { $candidates[] = $data['amount']; }
    if (isset($data['payment_request']) && is_array($data['payment_request'])) {
        if (isset($data['payment_request']['amount_microbyx'])) { $candidates[] = $data['payment_request']['amount_microbyx']; }
        if (isset($data['payment_request']['amount'])) { $candidates[] = $data['payment_request']['amount']; }
    }
    foreach ($candidates as $candidate) {
        if (is_numeric($candidate) && intval($candidate) > 0) {
            return intval($candidate);
        }
    }
    return 0;
}
