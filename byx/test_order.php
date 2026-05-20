<?php
require_once dirname(__FILE__) . '/../core/bootstrap.php';
require_once dirname(__FILE__) . '/byx_purchase.php';

$user = auth_require();

if (!defined('APP_DEBUG') || !APP_DEBUG) {
    die('Teste de pedido permitido apenas em APP_DEBUG.');
}

$result = byx_purchase_create_payment_request(
    intval($user['id']),
    'manual_adjustment',
    null,
    'Pedido de teste BYX',
    0.01,
    array('test_mode' => 1, 'safe' => 1)
);

render_header('BYX Test Order', 'pets');
?>
<div style="max-width:760px;margin:20px auto;background:#fff;border:1px solid #ffd7e4;border-radius:16px;padding:16px;">
    <h2 style="margin-top:0;">Teste de pedido BYX</h2>
    <p><strong>Status:</strong> <?php echo $result['ok'] ? 'OK' : 'ERRO'; ?></p>
    <p><strong>Mensagem:</strong> <?php echo h($result['message']); ?></p>
    <p><strong>Order ID:</strong> <?php echo isset($result['order_id']) ? intval($result['order_id']) : 0; ?></p>
    <p><strong>Payment Request:</strong> <?php echo isset($result['payment_request_id']) ? h($result['payment_request_id']) : '-'; ?></p>
</div>
<?php render_footer(); ?>
