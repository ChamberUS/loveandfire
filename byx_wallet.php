<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/core/byx_client.php';

$user = auth_require();
$defaultLojaId = defined('BYX_DEFAULT_LOJA_ID') ? intval(BYX_DEFAULT_LOJA_ID) : 1;
$resultMessage = '';
$resultPayload = null;
$selectedRequestId = '';
$requestSummary = null;

function byx_wallet_is_positive_int_string($value)
{
    return is_string($value) && (bool)preg_match('/^[1-9][0-9]*$/', trim($value));
}

function byx_wallet_friendly_not_found_message()
{
    return 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
}

function byx_wallet_to_summary($data)
{
    if (!is_array($data)) {
        return null;
    }
    $id = byx_extract_numeric_payment_request_id_from_data($data);
    $status = byx_extract_payment_request_status_from_data($data);
    $amount = byx_extract_payment_request_amount_microbyx_from_data($data);

    if ($id <= 0 && $status === '' && $amount <= 0) {
        return null;
    }

    return array(
        'id' => $id,
        'status' => $status,
        'amount_microbyx' => $amount
    );
}

function byx_wallet_prepare_payload_for_view($payload)
{
    if (!is_array($payload)) {
        return $payload;
    }
    if (isset($payload['data']) && is_array($payload['data'])) {
        $id = byx_extract_numeric_payment_request_id_from_data($payload['data']);
        if ($id > 0) {
            $payload['data']['payment_request_id'] = $id;
        }
        if (isset($payload['data']['request_id']) && !byx_is_positive_int_value($payload['data']['request_id'])) {
            $payload['data']['request_id'] = '[uuid-interno-oculto]';
        }
        if (isset($payload['data']['payment_request']) && is_array($payload['data']['payment_request']) && isset($payload['data']['payment_request']['request_id']) && !byx_is_positive_int_value($payload['data']['payment_request']['request_id'])) {
            $payload['data']['payment_request']['request_id'] = '[uuid-interno-oculto]';
        }
    }
    return $payload;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';

    if ($action === 'create_request') {
        $lojaId = isset($_POST['loja_id']) ? intval($_POST['loja_id']) : $defaultLojaId;
        $amount = isset($_POST['amount_microbyx']) ? intval($_POST['amount_microbyx']) : 0;
        $memo = isset($_POST['memo']) ? trim((string)$_POST['memo']) : '';
        $expires = isset($_POST['expires_in_seconds']) ? intval($_POST['expires_in_seconds']) : 900;

        $resultPayload = byx_create_payment_request($lojaId, $amount, $memo, $expires);

        if ($resultPayload['ok']) {
            $numericId = 0;
            if (isset($resultPayload['data']) && is_array($resultPayload['data'])) {
                $numericId = byx_extract_numeric_payment_request_id_from_data($resultPayload['data']);
            }

            if ($numericId <= 0) {
                $resultPayload['ok'] = false;
                $resultPayload['status'] = 502;
                $resultPayload['error'] = 'Cobranca criada na blockchain, mas a API nao retornou ID numerico valido. Verifique logs BYX.';
                $resultMessage = $resultPayload['error'];
            } else {
                $selectedRequestId = (string)$numericId;
                $resultMessage = 'Cobranca criada com sucesso. Use o ID ' . $numericId . ' para consultar, gerar QR ou pagar.';
                $requestInfo = byx_get_payment_request($numericId);
                if ($requestInfo['ok'] && isset($requestInfo['data']) && is_array($requestInfo['data'])) {
                    $requestSummary = byx_wallet_to_summary($requestInfo['data']);
                }
                if ($requestSummary === null) {
                    $requestSummary = byx_wallet_to_summary(isset($resultPayload['data']) ? $resultPayload['data'] : null);
                }
            }
        } else {
            $resultMessage = isset($resultPayload['error']) && $resultPayload['error'] ? $resultPayload['error'] : 'Falha ao criar cobranca.';
        }
    } elseif ($action === 'get_request' || $action === 'get_qr' || $action === 'pay_request') {
        $rawRequestId = isset($_POST['request_id']) ? trim((string)$_POST['request_id']) : '';
        $selectedRequestId = $rawRequestId;

        if (!byx_wallet_is_positive_int_string($rawRequestId)) {
            $resultPayload = array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe o ID numerico da cobranca BYX (inteiro positivo).');
            $resultMessage = $resultPayload['error'];
        } else {
            $requestId = intval($rawRequestId);
            $requestInfo = byx_get_payment_request($requestId);

            if (!$requestInfo['ok']) {
                $errorText = isset($requestInfo['error']) ? strtolower((string)$requestInfo['error']) : '';
                if (intval($requestInfo['status']) === 502 || strpos($errorText, 'rpc') !== false) {
                    $requestInfo['error'] = byx_wallet_friendly_not_found_message();
                }
                $resultPayload = $requestInfo;
                $resultMessage = $requestInfo['error'];
            } else {
                $requestSummary = byx_wallet_to_summary(isset($requestInfo['data']) ? $requestInfo['data'] : null);

                if ($action === 'get_request') {
                    $resultPayload = $requestInfo;
                    $resultMessage = 'Request consultado com sucesso.';
                } elseif ($action === 'get_qr') {
                    $qrResult = byx_get_payment_qr($requestId);
                    if (!$qrResult['ok']) {
                        $errorText = isset($qrResult['error']) ? strtolower((string)$qrResult['error']) : '';
                        if (intval($qrResult['status']) === 502 || strpos($errorText, 'rpc') !== false) {
                            $qrResult['error'] = byx_wallet_friendly_not_found_message();
                        }
                    }
                    $resultPayload = $qrResult;
                    $resultMessage = $qrResult['ok'] ? 'QR consultado com sucesso.' : $qrResult['error'];
                } else {
                    $statusText = $requestSummary ? $requestSummary['status'] : '';
                    if ($statusText === 'PAYMENT_STATUS_PAID') {
                        $resultPayload = array('ok' => false, 'status' => 409, 'data' => isset($requestInfo['data']) ? $requestInfo['data'] : null, 'error' => 'Essa cobranca ja foi paga.');
                        $resultMessage = 'Essa cobranca ja foi paga.';
                    } elseif ($statusText !== 'PAYMENT_STATUS_PENDING') {
                        $resultPayload = array('ok' => false, 'status' => 409, 'data' => isset($requestInfo['data']) ? $requestInfo['data'] : null, 'error' => 'Essa cobranca nao esta pendente e nao pode ser paga.');
                        $resultMessage = 'Essa cobranca nao esta pendente e nao pode ser paga.';
                    } else {
                        $payResult = byx_pay_payment_request_devnet($requestId);
                        if (!$payResult['ok']) {
                            $errorText = isset($payResult['error']) ? strtolower((string)$payResult['error']) : '';
                            if (intval($payResult['status']) === 502 || strpos($errorText, 'rpc') !== false) {
                                $payResult['error'] = byx_wallet_friendly_not_found_message();
                            }
                        }
                        $resultPayload = $payResult;
                        $resultMessage = $payResult['ok'] ? 'Pagamento DEVNET executado.' : $payResult['error'];

                        if ($payResult['ok']) {
                            $refresh = byx_get_payment_request($requestId);
                            if ($refresh['ok'] && isset($refresh['data']) && is_array($refresh['data'])) {
                                $requestSummary = byx_wallet_to_summary($refresh['data']);
                            }
                        }
                    }
                }
            }
        }
    }
}

$health = byx_health();
$saldo = byx_get_merchant_saldo($defaultLojaId);

if ($requestSummary === null && $selectedRequestId !== '' && byx_wallet_is_positive_int_string($selectedRequestId)) {
    $summarySource = byx_get_payment_request(intval($selectedRequestId));
    if ($summarySource['ok'] && isset($summarySource['data']) && is_array($summarySource['data'])) {
        $requestSummary = byx_wallet_to_summary($summarySource['data']);
    }
}

$canPaySummary = $requestSummary && isset($requestSummary['status']) && $requestSummary['status'] === 'PAYMENT_STATUS_PENDING';

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
    <form method="post" style="margin-bottom:12px;" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_request">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required value="<?php echo h($selectedRequestId); ?>"></label><br><br>
        <button type="submit">Consultar request</button>
    </form>

    <h2>Consultar QR por ID numerico da cobranca</h2>
    <form method="post" style="margin-bottom:12px;" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_qr">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required value="<?php echo h($selectedRequestId); ?>"></label><br><br>
        <button type="submit">Consultar QR DEVNET</button>
    </form>

    <h2>Pagar request na DEVNET (teste fechado)</h2>
    <form method="post" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="pay_request">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required value="<?php echo h($selectedRequestId); ?>"></label><br><br>
        <button type="submit">Pagar request DEVNET</button>
    </form>

    <?php if ($requestSummary): ?>
        <hr>
        <h2>Resumo da cobranca</h2>
        <p><strong>ID numerico da cobranca:</strong> <?php echo intval($requestSummary['id']); ?></p>
        <p><strong>Status:</strong> <?php echo h($requestSummary['status'] !== '' ? $requestSummary['status'] : 'Nao informado'); ?></p>
        <p><strong>Valor (microBYX):</strong> <?php echo intval($requestSummary['amount_microbyx']); ?></p>
        <?php if ($canPaySummary): ?>
            <form method="post" class="js-byx-request-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="pay_request">
                <input type="hidden" name="request_id" value="<?php echo intval($requestSummary['id']); ?>">
                <button type="submit">Pagar agora (somente pending)</button>
            </form>
        <?php else: ?>
            <p>Pagamento habilitado apenas para status <strong>PAYMENT_STATUS_PENDING</strong>.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($resultPayload !== null): ?>
        <hr>
        <h2>Resultado da acao</h2>
        <p><strong><?php echo h($resultMessage); ?></strong></p>
        <pre style="background:#111;color:#eee;padding:12px;border-radius:8px;overflow:auto;"><?php echo h(json_encode(byx_wallet_prepare_payload_for_view($resultPayload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    <?php endif; ?>
</section>
<script>
(function () {
    var forms = document.querySelectorAll('.js-byx-request-form');
    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('submit', function (event) {
            var input = this.querySelector('input[name="request_id"]');
            if (!input) {
                return;
            }
            var value = String(input.value || '').trim();
            if (!/^[1-9][0-9]*$/.test(value)) {
                event.preventDefault();
                alert('Informe o ID numerico da cobranca BYX (inteiro positivo).');
                input.focus();
            }
        });
    }
})();
</script>
<?php render_footer(); ?>
