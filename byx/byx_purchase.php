<?php
if (!defined('HC_APP')) { die('Acesso negado'); }
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/byx_api.php';
require_once dirname(__FILE__) . '/byx_orders.php';

function byx_purchase_ref($usuarioId, $tipo, $itemId)
{
    return 'LF-ORDER-' . date('Ymd-His') . '-' . intval($usuarioId) . '-' . strtoupper(substr(sha1((string)$tipo . '-' . (string)$itemId . '-' . uniqid((string)mt_rand(), true)), 0, 8));
}

function byx_purchase_create_payment_request($usuarioId, $tipo, $itemId, $descricao, $valorReais, $metadata)
{
    $usuarioId = intval($usuarioId);
    $valorReais = (float)$valorReais;
    if ($usuarioId <= 0 || $valorReais <= 0) {
        return array('ok' => false, 'error' => 'Nao foi possivel concluir a compra.');
    }

    byx_orders_ensure_schema();
    $orderId = byx_order_create($usuarioId, $tipo, $itemId, $descricao, 0.00000000, $valorReais, $metadata);
    if ($orderId <= 0) {
        return array('ok' => false, 'error' => 'Nao foi possivel criar pedido local.');
    }
    $order = byx_order_get($orderId);
    $extRef = byx_purchase_ref($usuarioId, $tipo, $itemId);

    $payload = array(
        'merchant_id' => BYX_MERCHANT_ID,
        'external_reference' => $extRef,
        'user_id' => $usuarioId,
        'type' => (string)$tipo,
        'item_id' => ($itemId === null ? null : intval($itemId)),
        'description' => (string)$descricao,
        'amount_brl' => number_format($valorReais, 2, '.', ''),
        'currency' => 'BRL',
        'callback_url' => 'https://lovenfire.com/byx_callback.php',
        'metadata' => is_array($metadata) ? $metadata : array()
    );

    $api = byx_api_create_payment_request($payload);
    if (!$api['ok']) {
        byx_order_mark_failed($orderId, 'Falha API create_payment_request: ' . $api['error']);
        return array('ok' => false, 'error' => 'API BYX indisponivel no momento. Tente novamente.', 'order_id' => $orderId);
    }

    $paymentId = '';
    if (isset($api['data']['id'])) {
        $paymentId = (string)$api['data']['id'];
    } elseif (isset($api['data']['payment_request_id'])) {
        $paymentId = (string)$api['data']['payment_request_id'];
    }

    db_query('UPDATE lf_orders SET payment_provider = \'byx_devnet\', payment_reference = ' . db_escape($paymentId) . ', metadata = ' . db_escape(json_encode(array('external_reference' => $extRef, 'api' => $api['data']))) . ', updated_at = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($orderId) . ' LIMIT 1');
    byx_order_add_log($orderId, 'pending', 'Payment request criado na API BYX.');

    return array(
        'ok' => true,
        'order_id' => $orderId,
        'payment_request_id' => $paymentId,
        'external_reference' => $extRef,
        'api' => $api['data']
    );
}

function byx_purchase_get_status($paymentRequestId)
{
    return byx_api_get_payment_request($paymentRequestId);
}

function byx_purchase_get_qr($paymentRequestId)
{
    return byx_api_get_payment_request_qr($paymentRequestId);
}

function byx_purchase_mark_paid_devnet($paymentRequestId)
{
    return byx_api_pay_payment_request($paymentRequestId);
}
