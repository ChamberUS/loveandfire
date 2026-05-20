<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/byx/byx_wallet.php';
require_once dirname(__FILE__) . '/byx/byx_game.php';

$user = auth_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pets.php?wallet_msg=error');
}

byx_validate_csrf_or_die();

$usuarioId = intval($user['id']);
$diamantes = isset($_POST['diamantes']) ? intval($_POST['diamantes']) : 0;
if ($diamantes <= 0) {
    redirect('pets.php?wallet_msg=error');
}

if ($diamantes <= 0) {
    redirect('pets.php?wallet_msg=error');
}

$byxGerado = byx_diamond_to_byx($diamantes);
if ($byxGerado <= 0) {
    redirect('pets.php?wallet_msg=error');
}

db_query('START TRANSACTION');
$deb = byx_diamond_debit($usuarioId, $diamantes, 'diamond_convert_debit', 'Conversao local de diamantes para BYX.', 'lf_wallet_transacoes', 0);
if (!$deb[0]) {
    db_query('ROLLBACK');
    redirect('pets.php?wallet_msg=insufficient_diamonds');
}

$result = byx_game_reward(
    $usuarioId,
    $byxGerado,
    'diamond_conversion',
    null,
    'Conversao de diamantes em BYX',
    array(
        'module' => 'diamonds',
        'diamonds_converted' => $diamantes,
        'diamond_rate' => number_format(BYX_DIAMOND_RATE, 8, '.', ''),
        'non_refundable' => true
    )
);

if (!$result['ok']) {
    db_query('ROLLBACK');
    redirect('pets.php?wallet_msg=error');
}

$wAfter = byx_wallet_get($usuarioId, true);
$saldoAfter = isset($wAfter['saldo_byx']) ? (float)$wAfter['saldo_byx'] : 0.0;
$saldoBefore = $saldoAfter - $byxGerado;
if ($saldoBefore < 0) {
    $saldoBefore = 0.0;
}
byx_wallet_log(
    $usuarioId,
    'diamond_convert_reward',
    'credito',
    $byxGerado,
    $saldoBefore,
    $saldoAfter,
    'lf_wallet_transacoes',
    0,
    'Conversao de diamantes em BYX confirmada pela API. Valor nao reembolsavel.'
);

db_query('COMMIT');
redirect('pets.php?wallet_msg=converted');
