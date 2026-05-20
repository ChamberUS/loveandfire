<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/byx/byx_wallet.php';
require_once dirname(__FILE__) . '/byx/byx_purchase.php';

$user = auth_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('pets.php?wallet_msg=error');
}

byx_validate_csrf_or_die();

$usuarioId = intval($user['id']);
$purchase = array('order_id' => 0, 'payment_request_id' => '');
$paymentRequestId = '';
$pacote = array('name' => 'Pacote', 'diamantes' => 0, 'bonus' => 0);
$diamantes = 0;
$bonus = 0;
$totalDiamantes = 0;
$totalByx = 0.0;
$valorReais = 0.0;

if (isset($_POST['devnet_pay']) && intval($_POST['devnet_pay']) === 1) {
    $paymentRequestId = isset($_POST['payment_request_id']) ? trim($_POST['payment_request_id']) : '';
    if ($paymentRequestId !== '') {
        $payResp = byx_purchase_mark_paid_devnet($paymentRequestId);
        if ($payResp['ok']) {
            redirect('pets.php?wallet_msg=order_created');
        }
    }
    redirect('pets.php?wallet_msg=error');
} else {
    $diamantesBase = isset($_POST['diamantes']) ? intval($_POST['diamantes']) : 0;
    $pacote = byx_get_package_by_diamonds($diamantesBase);

    if (!$pacote) {
        redirect('pets.php?wallet_msg=invalid_package');
    }

    $diamantes = intval($pacote['diamantes']);
    $bonus = intval($pacote['bonus']);
    $totalDiamantes = $diamantes + $bonus;
    $totalByx = byx_diamond_to_byx($totalDiamantes);
    $valorReais = $totalByx * byx_get_brl_rate();
    $descricao = 'Compra de diamantes (' . $pacote['name'] . ')';
    $metadata = array(
        'package_name' => $pacote['name'],
        'diamantes_base' => $diamantes,
        'bonus' => $bonus,
        'total_diamantes' => $totalDiamantes,
        'valor_nao_reembolsavel' => 1
    );

    $purchase = byx_purchase_create_payment_request($usuarioId, 'diamond_purchase', $diamantes, $descricao, $valorReais, $metadata);
    if (!$purchase['ok']) {
        redirect('pets.php?wallet_msg=error');
    }
    $paymentRequestId = $purchase['payment_request_id'];
}

$qrData = byx_purchase_get_qr($paymentRequestId);
$statusData = byx_purchase_get_status($paymentRequestId);

render_header('Pedido de Diamantes', 'pets');
?>
<style>
.lford-wrap{max-width:920px;margin:16px auto;padding:16px;}
.lford-card{background:#fff;border:1px solid #ffd7e4;border-radius:18px;padding:18px;box-shadow:0 10px 24px rgba(0,0,0,.06);}
.lford-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-top:14px;}
.lford-item{border:1px solid #ffe5ee;border-radius:12px;padding:12px;background:#fff9fc;}
.lford-k{display:block;color:#8b5a6d;font-size:12px;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px;}
.lford-v{font-weight:700;color:#d5275f;font-size:18px;}
.lford-note{margin-top:14px;padding:12px;border-radius:10px;background:#fff4f8;color:#7b4c5f;}
.lford-actions{margin-top:16px;}
.lford-btn{display:inline-block;padding:10px 16px;border-radius:999px;background:#ff4f8f;color:#fff;text-decoration:none;font-weight:700;}
</style>

<section class="lford-wrap">
    <div class="lford-card">
        <h2>Pedido criado</h2>
        <p>Seu pedido foi criado e está aguardando confirmação do pagamento.</p>

        <div class="lford-grid">
            <div class="lford-item"><span class="lford-k">ID do pedido</span><div class="lford-v">#<?php echo intval($purchase['order_id']); ?></div></div>
            <div class="lford-item"><span class="lford-k">Payment Request</span><div class="lford-v"><?php echo h($paymentRequestId); ?></div></div>
            <div class="lford-item"><span class="lford-k">Pacote escolhido</span><div class="lford-v"><?php echo h($pacote['name']); ?></div></div>
            <div class="lford-item"><span class="lford-k">Diamantes comprados</span><div class="lford-v"><?php echo intval($diamantes); ?> 💎</div></div>
            <div class="lford-item"><span class="lford-k">Bônus</span><div class="lford-v"><?php echo intval($bonus); ?> 💎</div></div>
            <div class="lford-item"><span class="lford-k">Total de diamantes</span><div class="lford-v"><?php echo intval($totalDiamantes); ?> 💎</div></div>
            <div class="lford-item"><span class="lford-k">Equivalente em BYX</span><div class="lford-v"><?php echo byx_format($totalByx); ?> BYX</div></div>
            <div class="lford-item"><span class="lford-k">Valor em reais</span><div class="lford-v"><?php echo byx_format_brl($valorReais); ?></div></div>
        </div>

        <div class="lford-note">
            Após confirmação do pagamento, os diamantes serão creditados.<br>
            Valores convertidos em BYX são não reembolsáveis.
        </div>

        <div class="lford-note">
            <strong>Status:</strong>
            <?php echo $statusData['ok'] ? h(json_encode($statusData['data'])) : h($statusData['error']); ?>
        </div>

        <div class="lford-note">
            <strong>QR:</strong><br>
            <?php echo $qrData['ok'] ? h(json_encode($qrData['data'])) : h($qrData['error']); ?>
        </div>

        <div class="lford-actions">
            <form method="post" style="display:inline-block;">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="devnet_pay" value="1">
                <input type="hidden" name="payment_request_id" value="<?php echo h($paymentRequestId); ?>">
                <button type="submit" class="lford-btn">Simular pagamento (devnet)</button>
            </form>
        </div>

        <div class="lford-actions">
            <a class="lford-btn" href="pets.php?wallet_msg=order_created">Voltar para LovenPets</a>
        </div>
    </div>
</section>
<?php render_footer(); ?>
