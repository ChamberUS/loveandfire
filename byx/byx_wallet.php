<?php
if (!defined('HC_APP')) { die('Acesso negado'); }
require_once dirname(__FILE__) . '/config.php';

if (!defined('BYX_DIAMOND_RATE')) {
    define('BYX_DIAMOND_RATE', 0.01000000);
}

if (!defined('BYX_BRL_RATE')) {
    define('BYX_BRL_RATE', 1.00);
}

function byx_validate_csrf_or_die()
{
    csrf_check();
}

function byx_format($valor)
{
    return number_format((float)$valor, 8, ',', '.');
}

function byx_format_brl($valor)
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function byx_get_diamond_rate()
{
    return (float)BYX_DIAMOND_RATE;
}

function byx_get_brl_rate()
{
    return (float)BYX_BRL_RATE;
}

function byx_diamond_to_byx($diamantes)
{
    return ((float)intval($diamantes)) * byx_get_diamond_rate();
}

function byx_wallet_table_exists($table)
{
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$table);
    if ($table === '') {
        return false;
    }
    $row = db_fetch_one("SHOW TABLES LIKE " . db_escape($table));
    return $row ? true : false;
}

function byx_wallet_column_exists($table, $column)
{
    if (!byx_wallet_table_exists($table)) {
        return false;
    }
    $row = db_fetch_one("SHOW COLUMNS FROM `" . $table . "` LIKE " . db_escape($column));
    return $row ? true : false;
}

function byx_wallet_ensure_schema()
{
    if (!byx_wallet_table_exists('lf_wallets')) {
        db_query("CREATE TABLE IF NOT EXISTS lf_wallets (
            id INT NOT NULL AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            saldo_byx DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
            saldo_bloqueado DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
            diamantes INT NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_lf_wallets_usuario (usuario_id),
            KEY idx_lf_wallets_usuario (usuario_id),
            KEY idx_lf_wallets_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    } elseif (!byx_wallet_column_exists('lf_wallets', 'diamantes')) {
        db_query("ALTER TABLE lf_wallets ADD COLUMN diamantes INT NOT NULL DEFAULT 0");
    }

    if (!byx_wallet_table_exists('lf_wallet_transacoes')) {
        db_query("CREATE TABLE IF NOT EXISTS lf_wallet_transacoes (
            id INT NOT NULL AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            tipo VARCHAR(40) NOT NULL,
            direcao VARCHAR(10) NOT NULL,
            valor DECIMAL(18,8) NOT NULL,
            saldo_antes DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
            saldo_depois DECIMAL(18,8) NOT NULL DEFAULT 0.00000000,
            referencia_tipo VARCHAR(60) DEFAULT NULL,
            referencia_id INT DEFAULT NULL,
            descricao VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_lf_wallet_transacoes_usuario (usuario_id),
            KEY idx_lf_wallet_transacoes_tipo (tipo),
            KEY idx_lf_wallet_transacoes_referencia_tipo (referencia_tipo),
            KEY idx_lf_wallet_transacoes_referencia_id (referencia_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
    }
}

function byx_wallet_ensure($usuarioId)
{
    $usuarioId = intval($usuarioId);
    byx_wallet_ensure_schema();

    $wallet = db_fetch_one('SELECT * FROM lf_wallets WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
    if ($wallet) {
        return $wallet;
    }

    $now = now_sql();
    db_query('INSERT INTO lf_wallets (usuario_id, saldo_byx, saldo_bloqueado, diamantes, status, created_at, updated_at) VALUES (' . $usuarioId . ', 0.00000000, 0.00000000, 0, \'active\', ' . db_escape($now) . ', ' . db_escape($now) . ')');
    return db_fetch_one('SELECT * FROM lf_wallets WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
}

function byx_wallet_get($usuarioId, $forUpdate)
{
    $usuarioId = intval($usuarioId);
    byx_wallet_ensure($usuarioId);

    $sql = 'SELECT * FROM lf_wallets WHERE usuario_id = ' . $usuarioId . ' LIMIT 1';
    if ($forUpdate) {
        $sql .= ' FOR UPDATE';
    }
    return db_fetch_one($sql);
}

function byx_wallet_log($usuarioId, $tipo, $direcao, $valor, $saldoAntes, $saldoDepois, $referenciaTipo, $referenciaId, $descricao)
{
    $usuarioId = intval($usuarioId);
    $now = now_sql();
    return db_query('INSERT INTO lf_wallet_transacoes (usuario_id, tipo, direcao, valor, saldo_antes, saldo_depois, referencia_tipo, referencia_id, descricao, created_at) VALUES (' . $usuarioId . ', ' . db_escape($tipo) . ', ' . db_escape($direcao) . ', ' . db_escape(number_format((float)$valor, 8, '.', '')) . ', ' . db_escape(number_format((float)$saldoAntes, 8, '.', '')) . ', ' . db_escape(number_format((float)$saldoDepois, 8, '.', '')) . ', ' . db_escape($referenciaTipo) . ', ' . intval($referenciaId) . ', ' . db_escape($descricao) . ', ' . db_escape($now) . ')');
}

function byx_wallet_credit($usuarioId, $valorByx, $tipo, $observacao, $referenciaTipo, $referenciaId)
{
    $usuarioId = intval($usuarioId);
    $valorByx = (float)$valorByx;
    if ($valorByx <= 0) {
        return true;
    }

    $wallet = byx_wallet_get($usuarioId, true);
    $saldoAntes = isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0;
    $saldoDepois = $saldoAntes + $valorByx;
    $now = now_sql();

    $ok = db_query('UPDATE lf_wallets SET saldo_byx = ' . db_escape(number_format($saldoDepois, 8, '.', '')) . ', updated_at = ' . db_escape($now) . ' WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
    if (!$ok) {
        return false;
    }

    return byx_wallet_log($usuarioId, $tipo, 'credito', $valorByx, $saldoAntes, $saldoDepois, $referenciaTipo, $referenciaId, $observacao);
}

function byx_wallet_debit($usuarioId, $valorByx, $tipo, $observacao, $referenciaTipo, $referenciaId)
{
    $usuarioId = intval($usuarioId);
    $valorByx = (float)$valorByx;
    if ($valorByx <= 0) {
        return array(true, 'ok');
    }

    $wallet = byx_wallet_get($usuarioId, true);
    $saldoAntes = isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0;
    if ($saldoAntes < $valorByx) {
        return array(false, 'Saldo BYX insuficiente.');
    }

    $saldoDepois = $saldoAntes - $valorByx;
    $now = now_sql();
    $ok = db_query('UPDATE lf_wallets SET saldo_byx = ' . db_escape(number_format($saldoDepois, 8, '.', '')) . ', updated_at = ' . db_escape($now) . ' WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
    if (!$ok) {
        return array(false, 'Erro ao debitar BYX.');
    }

    $okTx = byx_wallet_log($usuarioId, $tipo, 'debito', $valorByx, $saldoAntes, $saldoDepois, $referenciaTipo, $referenciaId, $observacao);
    if (!$okTx) {
        return array(false, 'Erro ao registrar debito de BYX.');
    }

    return array(true, 'ok');
}

function byx_diamond_credit($usuarioId, $diamantes, $tipo, $observacao, $referenciaTipo, $referenciaId)
{
    $usuarioId = intval($usuarioId);
    $diamantes = intval($diamantes);
    if ($diamantes <= 0) {
        return true;
    }

    $wallet = byx_wallet_get($usuarioId, true);
    $diamantesAntes = isset($wallet['diamantes']) ? intval($wallet['diamantes']) : 0;
    $diamantesDepois = $diamantesAntes + $diamantes;
    $now = now_sql();

    $ok = db_query('UPDATE lf_wallets SET diamantes = ' . intval($diamantesDepois) . ', updated_at = ' . db_escape($now) . ' WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
    if (!$ok) {
        return false;
    }

    $descricao = $observacao . ' | diamantes +' . $diamantes;
    return byx_wallet_log($usuarioId, $tipo, 'credito', 0.00000000, isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0, isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0, $referenciaTipo, $referenciaId, $descricao);
}

function byx_diamond_debit($usuarioId, $diamantes, $tipo, $observacao, $referenciaTipo, $referenciaId)
{
    $usuarioId = intval($usuarioId);
    $diamantes = intval($diamantes);
    if ($diamantes <= 0) {
        return array(true, 'ok');
    }

    $wallet = byx_wallet_get($usuarioId, true);
    $diamantesAntes = isset($wallet['diamantes']) ? intval($wallet['diamantes']) : 0;
    if ($diamantesAntes < $diamantes) {
        return array(false, 'Diamantes insuficientes.');
    }

    $diamantesDepois = $diamantesAntes - $diamantes;
    $now = now_sql();
    $ok = db_query('UPDATE lf_wallets SET diamantes = ' . intval($diamantesDepois) . ', updated_at = ' . db_escape($now) . ' WHERE usuario_id = ' . $usuarioId . ' LIMIT 1');
    if (!$ok) {
        return array(false, 'Erro ao debitar diamantes.');
    }

    $descricao = $observacao . ' | diamantes -' . $diamantes;
    $okTx = byx_wallet_log($usuarioId, $tipo, 'debito', 0.00000000, isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0, isset($wallet['saldo_byx']) ? (float)$wallet['saldo_byx'] : 0.0, $referenciaTipo, $referenciaId, $descricao);
    if (!$okTx) {
        return array(false, 'Erro ao registrar debito de diamantes.');
    }

    return array(true, 'ok');
}

function byx_get_diamond_packages()
{
    return array(
        array('name' => 'Pacote Inicial', 'diamantes' => 100, 'bonus' => 0),
        array('name' => 'Pacote Bronze', 'diamantes' => 500, 'bonus' => 50),
        array('name' => 'Pacote Prata', 'diamantes' => 1000, 'bonus' => 200),
        array('name' => 'Pacote Fogo', 'diamantes' => 5000, 'bonus' => 1500)
    );
}

function byx_get_package_by_diamonds($diamantes)
{
    $diamantes = intval($diamantes);
    $items = byx_get_diamond_packages();
    foreach ($items as $item) {
        if (intval($item['diamantes']) === $diamantes) {
            return $item;
        }
    }
    return null;
}

function byx_diamond_orders_ensure_table()
{
    if (byx_wallet_table_exists('lf_diamond_orders')) {
        return true;
    }

    return db_query("CREATE TABLE IF NOT EXISTS lf_diamond_orders (
        id INT NOT NULL AUTO_INCREMENT,
        usuario_id INT NOT NULL,
        pacote VARCHAR(100) NOT NULL,
        diamantes INT NOT NULL,
        bonus INT NOT NULL DEFAULT 0,
        total_diamantes INT NOT NULL,
        total_byx DECIMAL(20,8) NOT NULL DEFAULT 0.00000000,
        valor_reais DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        payment_provider VARCHAR(50) DEFAULT NULL,
        payment_reference VARCHAR(120) DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_lf_diamond_orders_usuario (usuario_id),
        KEY idx_lf_diamond_orders_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
}
