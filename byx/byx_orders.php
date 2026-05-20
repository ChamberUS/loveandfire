<?php
if (!defined('HC_APP')) { die('Acesso negado'); }
require_once dirname(__FILE__) . '/config.php';

function byx_orders_ensure_schema()
{
    db_query("CREATE TABLE IF NOT EXISTS lf_orders (
        id INT NOT NULL AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        referencia VARCHAR(120) NOT NULL,
        tipo VARCHAR(60) NOT NULL,
        item_id INT DEFAULT NULL,
        descricao VARCHAR(255) NOT NULL,
        valor_byx DECIMAL(20,8) NOT NULL DEFAULT 0.00000000,
        valor_reais DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        payment_provider VARCHAR(60) DEFAULT NULL,
        payment_reference VARCHAR(160) DEFAULT NULL,
        metadata TEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_lf_orders_referencia (referencia),
        KEY idx_lf_orders_usuario (usuario_id),
        KEY idx_lf_orders_status (status),
        KEY idx_lf_orders_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

    db_query("CREATE TABLE IF NOT EXISTS lf_order_logs (
        id INT NOT NULL AUTO_INCREMENT,
        order_id INT NOT NULL,
        usuario_id INT NOT NULL,
        status VARCHAR(30) NOT NULL,
        observacao TEXT,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_lf_order_logs_order (order_id),
        KEY idx_lf_order_logs_usuario (usuario_id),
        KEY idx_lf_order_logs_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
}

function byx_order_generate_reference($usuarioId)
{
    $usuarioId = intval($usuarioId);
    $prefix = 'LF-ORDER-' . date('Y-m-d') . '-' . $usuarioId . '-';
    $tries = 0;
    do {
        $tries++;
        $rand = strtoupper(substr(sha1(uniqid((string)mt_rand(), true)), 0, 10));
        $ref = $prefix . $rand;
        $exists = db_fetch_one('SELECT id FROM lf_orders WHERE referencia = ' . db_escape($ref) . ' LIMIT 1');
    } while ($exists && $tries < 8);
    return $ref;
}

function byx_order_add_log($orderId, $status, $observacao)
{
    $order = byx_order_get($orderId);
    if (!$order) {
        return false;
    }
    return db_query('INSERT INTO lf_order_logs (order_id, usuario_id, status, observacao, created_at) VALUES (' . intval($orderId) . ', ' . intval($order['usuario_id']) . ', ' . db_escape($status) . ', ' . db_escape($observacao) . ', ' . db_escape(now_sql()) . ')');
}

function byx_order_create($usuarioId, $tipo, $itemId, $descricao, $valorByx, $valorReais, $metadata)
{
    byx_orders_ensure_schema();

    $usuarioId = intval($usuarioId);
    $itemId = ($itemId === null ? 'NULL' : intval($itemId));
    $referencia = byx_order_generate_reference($usuarioId);
    $now = now_sql();
    $meta = is_array($metadata) ? json_encode($metadata) : (string)$metadata;

    $ok = db_query('INSERT INTO lf_orders (usuario_id, referencia, tipo, item_id, descricao, valor_byx, valor_reais, status, payment_provider, payment_reference, metadata, created_at, updated_at) VALUES (' . $usuarioId . ', ' . db_escape($referencia) . ', ' . db_escape($tipo) . ', ' . $itemId . ', ' . db_escape($descricao) . ', ' . db_escape(number_format((float)$valorByx, 8, '.', '')) . ', ' . db_escape(number_format((float)$valorReais, 2, '.', '')) . ', \'pending\', NULL, NULL, ' . db_escape($meta) . ', ' . db_escape($now) . ', ' . db_escape($now) . ')');
    if (!$ok) {
        return 0;
    }
    $orderId = db_insert_id();
    byx_order_add_log($orderId, 'pending', 'Pedido criado.');
    return intval($orderId);
}

function byx_order_get($orderId)
{
    byx_orders_ensure_schema();
    return db_fetch_one('SELECT * FROM lf_orders WHERE id = ' . intval($orderId) . ' LIMIT 1');
}

function byx_order_get_by_reference($referencia)
{
    byx_orders_ensure_schema();
    return db_fetch_one('SELECT * FROM lf_orders WHERE referencia = ' . db_escape($referencia) . ' LIMIT 1');
}

function byx_order_find_recent_pending($usuarioId, $tipo, $itemId, $valorByx, $seconds)
{
    byx_orders_ensure_schema();
    $usuarioId = intval($usuarioId);
    $itemWhere = ($itemId === null ? 'item_id IS NULL' : 'item_id = ' . intval($itemId));
    $seconds = intval($seconds);
    if ($seconds <= 0) {
        $seconds = 30;
    }
    return db_fetch_one("SELECT * FROM lf_orders
    WHERE usuario_id = " . $usuarioId . "
      AND tipo = " . db_escape($tipo) . "
      AND " . $itemWhere . "
      AND valor_byx = " . db_escape(number_format((float)$valorByx, 8, '.', '')) . "
      AND status = 'pending'
      AND created_at >= DATE_SUB(" . db_escape(now_sql()) . ", INTERVAL " . $seconds . " SECOND)
    ORDER BY id DESC
    LIMIT 1");
}

function byx_order_mark_paid($orderId, $referenciaPagamento)
{
    $order = byx_order_get($orderId);
    if (!$order) {
        return false;
    }
    if ($order['status'] === 'paid') {
        return true;
    }
    if ($order['status'] === 'cancelled') {
        return false;
    }

    $ok = db_query('UPDATE lf_orders SET status = \'paid\', payment_reference = ' . db_escape($referenciaPagamento) . ', updated_at = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($orderId) . ' LIMIT 1');
    if ($ok) {
        byx_order_add_log($orderId, 'paid', 'Pagamento confirmado: ' . $referenciaPagamento);
    }
    return $ok ? true : false;
}

function byx_order_mark_failed($orderId, $motivo)
{
    $order = byx_order_get($orderId);
    if (!$order) {
        return false;
    }
    if ($order['status'] === 'paid') {
        return false;
    }
    $ok = db_query('UPDATE lf_orders SET status = \'failed\', updated_at = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($orderId) . ' LIMIT 1');
    if ($ok) {
        byx_order_add_log($orderId, 'failed', $motivo);
    }
    return $ok ? true : false;
}

function byx_order_mark_cancelled($orderId, $motivo)
{
    $order = byx_order_get($orderId);
    if (!$order) {
        return false;
    }
    if ($order['status'] === 'paid') {
        return false;
    }
    $ok = db_query('UPDATE lf_orders SET status = \'cancelled\', updated_at = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($orderId) . ' LIMIT 1');
    if ($ok) {
        byx_order_add_log($orderId, 'cancelled', $motivo);
    }
    return $ok ? true : false;
}
