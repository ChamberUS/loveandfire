<?php
require_once dirname(__FILE__) . '/../core/bootstrap.php';
require_once dirname(__FILE__) . '/byx_purchase.php';

$user = auth_require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $create = byx_purchase_create_payment_request(
        intval($user['id']),
        'diamond_purchase',
        100,
        'Teste devnet compra de diamantes',
        1.00,
        array('test_mode' => 1, 'module' => 'diamonds')
    );
} else {
    $create = null;
}

render_header('BYX Test Payment Request', 'pets');
?>
<div style="max-width:820px;margin:20px auto;background:#fff;border:1px solid #ffd7e4;border-radius:16px;padding:16px;">
    <h2 style="margin-top:0;">Teste de Payment Request (devnet)</h2>
    <form method="post">
        <?php echo csrf_field(); ?>
        <button type="submit" style="border:0;border-radius:10px;padding:10px 14px;background:#ff4f8f;color:#fff;font-weight:700;cursor:pointer;">Criar pedido de teste</button>
    </form>

    <?php if ($create !== null): ?>
        <hr style="margin:14px 0;border:0;border-top:1px solid #ffd7e4;">
        <?php if ($create['ok']): ?>
            <p><strong>Order ID:</strong> <?php echo intval($create['order_id']); ?></p>
            <p><strong>Payment Request ID:</strong> <?php echo h($create['payment_request_id']); ?></p>
            <?php
                $qr = byx_purchase_get_qr($create['payment_request_id']);
                $status = byx_purchase_get_status($create['payment_request_id']);
            ?>
            <p><strong>Status:</strong> <?php echo $status['ok'] ? 'ok' : h($status['error']); ?></p>
            <?php if ($qr['ok']): ?>
                <p><strong>QR:</strong></p>
                <pre style="white-space:pre-wrap;background:#fff6fa;border:1px solid #ffd7e4;padding:10px;border-radius:10px;"><?php echo h(json_encode($qr['data'])); ?></pre>
            <?php else: ?>
                <p><strong>QR indisponível:</strong> <?php echo h($qr['error']); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p><strong>Erro:</strong> <?php echo h($create['error']); ?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
