<?php
require_once dirname(__FILE__) . '/../core/bootstrap.php';
require_once dirname(__FILE__) . '/byx_game.php';
require_once dirname(__FILE__) . '/byx_wallet.php';

$user = auth_require();
$resp = byx_game_get_balance($user['id']);
$saldo = 0.0;
if ($resp['ok']) {
    if (isset($resp['data']['saldo_byx'])) {
        $saldo = (float)$resp['data']['saldo_byx'];
    } elseif (isset($resp['data']['balance_byx'])) {
        $saldo = (float)$resp['data']['balance_byx'];
    } elseif (isset($resp['data']['amount_byx'])) {
        $saldo = (float)$resp['data']['amount_byx'];
    }
}

render_header('BYX Saldo', 'pets');
?>
<div style="max-width:760px;margin:20px auto;background:#fff;border:1px solid #ffd7e4;border-radius:16px;padding:16px;">
    <h2 style="margin-top:0;">Saldo BYX</h2>
    <p><strong>Usuário:</strong> #<?php echo intval($user['id']); ?> · <?php echo h($user['name']); ?></p>
    <?php if ($resp['ok']): ?>
        <p><strong>Saldo:</strong> <?php echo byx_format($saldo); ?> BYX</p>
        <p style="font-size:12px;color:#7f5362;">valor não reembolsável</p>
    <?php else: ?>
        <p><strong>Erro:</strong> <?php echo h($resp['error']); ?></p>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
