<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/core/byx_client.php';

$user = auth_require();
$defaultLojaId = defined('BYX_DEFAULT_LOJA_ID') ? intval(BYX_DEFAULT_LOJA_ID) : 1;
$resultMessage = '';
$resultPayload = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

    if ($action === 'create_request') {
        $lojaId = isset($_POST['loja_id']) ? intval($_POST['loja_id']) : $defaultLojaId;
        $amount = isset($_POST['amount_microbyx']) ? intval($_POST['amount_microbyx']) : 0;
        $memo = isset($_POST['memo']) ? trim((string)$_POST['memo']) : '';
        $expires = isset($_POST['expires_in_seconds']) ? intval($_POST['expires_in_seconds']) : 900;
        $resultPayload = byx_create_payment_request($lojaId, $amount, $memo, $expires);
        $resultMessage = $resultPayload['ok'] ? 'Cobranca criada com sucesso (DEVNET).' : 'Falha ao criar cobranca.';
    } elseif ($action === 'get_qr') {
        $requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
        $resultPayload = byx_get_payment_qr($requestId);
        $resultMessage = $resultPayload['ok'] ? 'QR consultado com sucesso.' : 'Falha ao consultar QR.';
    } elseif ($action === 'pay_request') {
        $requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
        $resultPayload = byx_pay_payment_request_devnet($requestId);
        $resultMessage = $resultPayload['ok'] ? 'Pagamento DEVNET executado.' : 'Falha ao pagar request na DEVNET.';
    } elseif ($action === 'get_request') {
        $requestId = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
        $resultPayload = byx_get_payment_request($requestId);
        $resultMessage = $resultPayload['ok'] ? 'Request consultado com sucesso.' : 'Falha ao consultar request.';
    }
}

$health = byx_health();
$saldo = byx_get_merchant_saldo($defaultLojaId);

render_header('Carteira BYX', 'byx_wallet');
?>
<section class="hc-card" style="max-width:980px;margin:0 auto 24px auto;padding:20px;">
    <h1 style="margin-top:0;">Carteira BYX</h1>
    <p><strong>DEVNET / TESTE FECHADO - nao e pagamento real.</strong></p>
    <p>Usuario: <?php echo h($user['name']); ?> | Loja padrao: <?php echo intval($defaultLojaId); ?></p>

    <h2>Status da API BYX</h2>
    <pre style="background:#111;color:#eee;padding:12px;border-radius:8px;overflow:auto;"><?php echo h(json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

    <h2>Saldo da loja padrao</h2>
    <pre style="background:#111;color:#eee;padding:12px;border-radius:8px;overflow:auto;"><?php echo h(json_encode($saldo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

    <hr>
    <h2>Criar cobranca (payment request)</h2>
    <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create_request">
        <label>Loja ID<br><input type="number" name="loja_id" min="1" value="<?php echo intval($defaultLojaId); ?>"></label><br><br>
        <label>Valor (microBYX)<br><input type="number" name="amount_microbyx" min="1" required></label><br><br>
        <label>Memo<br><input type="text" name="memo" maxlength="255" value="compra no Love &amp; Fire"></label><br><br>
        <label>Expira em (segundos)<br><input type="number" name="expires_in_seconds" min="1" value="900"></label><br><br>
        <button type="submit">Criar cobranca DEVNET</button>
    </form>

    <hr>
    <h2>Consultar payment request</h2>
    <form method="post" style="margin-bottom:12px;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_request">
        <label>Request ID<br><input type="number" name="request_id" min="1" required></label><br><br>
        <button type="submit">Consultar request</button>
    </form>

    <h2>Consultar QR por request_id</h2>
    <form method="post" style="margin-bottom:12px;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_qr">
        <label>Request ID<br><input type="number" name="request_id" min="1" required></label><br><br>
        <button type="submit">Consultar QR DEVNET</button>
    </form>

    <h2>Pagar request na DEVNET (teste fechado)</h2>
    <form method="post">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="pay_request">
        <label>Request ID<br><input type="number" name="request_id" min="1" required></label><br><br>
        <button type="submit">Pagar request DEVNET</button>
    </form>

    <?php if ($resultPayload !== null): ?>
        <hr>
        <h2>Resultado da acao</h2>
        <p><?php echo h($resultMessage); ?></p>
        <pre style="background:#111;color:#eee;padding:12px;border-radius:8px;overflow:auto;"><?php echo h(json_encode($resultPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
