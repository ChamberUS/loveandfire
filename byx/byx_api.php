<?php
if (!defined('HC_APP')) { die('Acesso negado'); }
require_once dirname(__FILE__) . '/config.php';

function byx_api_response($ok, $httpCode, $data, $error)
{
    return array(
        'ok' => $ok ? true : false,
        'http_code' => intval($httpCode),
        'data' => is_array($data) ? $data : array(),
        'error' => (string)$error
    );
}

function byx_api_is_configured()
{
    if (!defined('BYX_API_BASE_URL') || !defined('BYX_API_TOKEN')) {
        return false;
    }
    $base = trim((string)BYX_API_BASE_URL);
    $token = trim((string)BYX_API_TOKEN);
    if ($base === '' || $token === '') {
        return false;
    }
    if ($base === 'https://URL-DA-API-BYX-AQUI' || $token === 'TOKEN-REAL-AQUI' || $token === 'TOKEN-EXEMPLO-AQUI') {
        return false;
    }
    return true;
}

function byx_api_request($method, $endpoint, $data)
{
    $method = strtoupper((string)$method);
    if ($method !== 'GET' && $method !== 'POST') {
        return byx_api_response(false, 0, array(), 'Metodo nao suportado.');
    }

    if (!byx_api_is_configured()) {
        return byx_api_response(false, 0, array(), 'API BYX indisponivel no momento. Tente novamente.');
    }

    if (!function_exists('curl_init')) {
        return byx_api_response(false, 0, array(), 'cURL nao disponivel no servidor.');
    }

    $base = rtrim((string)BYX_API_BASE_URL, '/');
    $url = $base . '/' . ltrim((string)$endpoint, '/');
    if ($method === 'GET' && is_array($data) && count($data) > 0) {
        $qs = http_build_query($data, '', '&');
        if ($qs !== '') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, intval(BYX_API_TIMEOUT));
    curl_setopt($ch, CURLOPT_TIMEOUT, intval(BYX_API_TIMEOUT));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . BYX_API_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    if ($method === 'POST') {
        $payload = is_array($data) ? json_encode($data) : '{}';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $raw = curl_exec($ch);
    $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return byx_api_response(false, $httpCode, array(), 'API BYX indisponivel no momento. Tente novamente.');
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        $json = array();
    }

    if ($httpCode !== 200 && $httpCode !== 201) {
        $err = 'Nao foi possivel concluir a operacao na API BYX.';
        if (isset($json['error']) && $json['error'] !== '') {
            $err = $json['error'];
        }
        if ($curlErr !== '') {
            $err = 'API BYX indisponivel no momento. Tente novamente.';
        }
        return byx_api_response(false, $httpCode, $json, $err);
    }

    return byx_api_response(true, $httpCode, $json, '');
}

function byx_api_health()
{
    return byx_api_request('GET', '/v1/devnet/health', array());
}

function byx_api_get_merchant($merchantId)
{
    return byx_api_request('GET', '/v1/devnet/merchants/' . urlencode((string)$merchantId), array());
}

function byx_api_get_merchant_saldo($merchantId)
{
    return byx_api_request('GET', '/v1/devnet/merchants/' . urlencode((string)$merchantId) . '/saldo', array());
}

function byx_api_create_payment_request($payload)
{
    return byx_api_request('POST', '/v1/devnet/payment-requests', is_array($payload) ? $payload : array());
}

function byx_api_get_payment_request($id)
{
    return byx_api_request('GET', '/v1/devnet/payment-requests/' . urlencode((string)$id), array());
}

function byx_api_get_payment_request_qr($id)
{
    return byx_api_request('GET', '/v1/devnet/payment-requests/' . urlencode((string)$id) . '/qr', array());
}

function byx_api_pay_payment_request($id)
{
    return byx_api_request('POST', '/v1/devnet/payment-requests/' . urlencode((string)$id) . '/pay', array());
}

function byx_api_game_petz_reward($payload)
{
    return byx_api_request('POST', '/v1/devnet/game/petz/reward', is_array($payload) ? $payload : array());
}

function byx_api_game_petz_spend($payload)
{
    return byx_api_request('POST', '/v1/devnet/game/petz/spend', is_array($payload) ? $payload : array());
}

function byx_api_game_user_balance($userId)
{
    return byx_api_request('GET', '/v1/devnet/game/users/' . intval($userId) . '/balance', array());
}

function byx_api_game_user_transactions($userId)
{
    return byx_api_request('GET', '/v1/devnet/game/users/' . intval($userId) . '/transactions', array());
}
