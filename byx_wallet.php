<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/core/byx_client.php';

$user = auth_require();
$defaultLojaId = defined('BYX_DEFAULT_LOJA_ID') ? intval(BYX_DEFAULT_LOJA_ID) : 1;
$resultMessage = '';
$resultPayload = null;
$resultAction = '';

function byx_wallet_is_positive_int_string($value)
{
    if (!is_string($value)) {
        return false;
    }
    $value = trim($value);
    return (bool)preg_match('/^[1-9][0-9]*$/', $value);
}

function byx_wallet_extract_numeric_request_id($payload)
{
    if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
        return 0;
    }

    $candidates = array();
    $data = $payload['data'];

    if (isset($data['request_id'])) { $candidates[] = $data['request_id']; }
    if (isset($data['id'])) { $candidates[] = $data['id']; }
    if (isset($data['payment_request']) && is_array($data['payment_request'])) {
        if (isset($data['payment_request']['request_id'])) { $candidates[] = $data['payment_request']['request_id']; }
        if (isset($data['payment_request']['id'])) { $candidates[] = $data['payment_request']['id']; }
    }

    foreach ($candidates as $candidate) {
        if (is_int($candidate) && $candidate > 0) {
            return $candidate;
        }
        if (is_string($candidate) && byx_wallet_is_positive_int_string($candidate)) {
            return intval($candidate);
        }
    }

    return 0;
}

function byx_wallet_is_not_found_or_rpc_error($payload)
{
    if (!is_array($payload)) {
        return false;
    }
    $status = isset($payload['status']) ? intval($payload['status']) : 0;
    $error = isset($payload['error']) ? strtolower((string)$payload['error']) : '';

    if ($status === 502) {
        return true;
    }
    if (strpos($error, 'rpc') !== false) {
        return true;
    }
    return false;
}

function byx_wallet_mask_internal_request_id($value)
{
    if (is_array($value)) {
        $masked = array();
        foreach ($value as $k => $v) {
            if ($k === 'request_id' && is_string($v) && !byx_wallet_is_positive_int_string($v)) {
                $masked[$k] = '[uuid-interno-oculto]';
            } else {
                $masked[$k] = byx_wallet_mask_internal_request_id($v);
            }
        }
        return $masked;
    }
    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = isset($_POST['action']) ? trim((string)$_POST['action']) : '';
    $resultAction = $action;

    if ($action === 'create_request') {
        $lojaId = isset($_POST['loja_id']) ? intval($_POST['loja_id']) : $defaultLojaId;
        $amount = isset($_POST['amount_microbyx']) ? intval($_POST['amount_microbyx']) : 0;
        $memo = isset($_POST['memo']) ? trim((string)$_POST['memo']) : '';
        $expires = isset($_POST['expires_in_seconds']) ? intval($_POST['expires_in_seconds']) : 900;
        $resultPayload = byx_create_payment_request($lojaId, $amount, $memo, $expires);
        if ($resultPayload['ok']) {
            $numericId = byx_wallet_extract_numeric_request_id($resultPayload);
            if ($numericId > 0) {
                $resultMessage = 'Cobranca criada com sucesso. Use o ID ' . $numericId . ' para consultar, gerar QR ou pagar.';
            } else {
                $resultMessage = 'Cobranca criada com sucesso, mas o ID numerico nao foi identificado no retorno.';
            }
        } else {
            $resultMessage = 'Falha ao criar cobranca.';
        }
    } elseif ($action === 'get_qr') {
        $rawRequestId = isset($_POST['request_id']) ? trim((string)$_POST['request_id']) : '';
        if (!byx_wallet_is_positive_int_string($rawRequestId)) {
            $resultPayload = array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe um ID numerico da cobranca BYX valido.');
            $resultMessage = 'Informe um ID numerico da cobranca BYX valido.';
        } else {
            $requestId = intval($rawRequestId);
            $resultPayload = byx_get_payment_qr($requestId);
            if (!$resultPayload['ok'] && byx_wallet_is_not_found_or_rpc_error($resultPayload)) {
                $resultMessage = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
            } else {
                $resultMessage = $resultPayload['ok'] ? 'QR consultado com sucesso.' : 'Falha ao consultar QR.';
            }
        }
    } elseif ($action === 'pay_request') {
        $rawRequestId = isset($_POST['request_id']) ? trim((string)$_POST['request_id']) : '';
        if (!byx_wallet_is_positive_int_string($rawRequestId)) {
            $resultPayload = array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe um ID numerico da cobranca BYX valido.');
            $resultMessage = 'Informe um ID numerico da cobranca BYX valido.';
        } else {
            $requestId = intval($rawRequestId);
            $resultPayload = byx_pay_payment_request_devnet($requestId);
            if (!$resultPayload['ok'] && byx_wallet_is_not_found_or_rpc_error($resultPayload)) {
                $resultMessage = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
            } else {
                $resultMessage = $resultPayload['ok'] ? 'Pagamento DEVNET executado.' : 'Falha ao pagar request na DEVNET.';
            }
        }
    } elseif ($action === 'get_request') {
        $rawRequestId = isset($_POST['request_id']) ? trim((string)$_POST['request_id']) : '';
        if (!byx_wallet_is_positive_int_string($rawRequestId)) {
            $resultPayload = array('ok' => false, 'status' => 400, 'data' => null, 'error' => 'Informe um ID numerico da cobranca BYX valido.');
            $resultMessage = 'Informe um ID numerico da cobranca BYX valido.';
        } else {
            $requestId = intval($rawRequestId);
            $resultPayload = byx_get_payment_request($requestId);
            if (!$resultPayload['ok'] && byx_wallet_is_not_found_or_rpc_error($resultPayload)) {
                $resultMessage = 'Cobranca nao encontrada, expirada, ja paga ou invalida.';
            } else {
                $resultMessage = $resultPayload['ok'] ? 'Request consultado com sucesso.' : 'Falha ao consultar request.';
            }
        }
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
    <form method="post" style="margin-bottom:12px;" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_request">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required></label><br><br>
        <button type="submit">Consultar request</button>
    </form>

    <h2>Consultar QR por request_id</h2>
    <form method="post" style="margin-bottom:12px;" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="get_qr">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required></label><br><br>
        <button type="submit">Consultar QR DEVNET</button>
    </form>

    <h2>Pagar request na DEVNET (teste fechado)</h2>
    <form method="post" class="js-byx-request-form">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="pay_request">
        <label>ID numerico da cobranca BYX<br><input type="number" inputmode="numeric" pattern="[0-9]+" name="request_id" min="1" required></label><br><br>
        <button type="submit">Pagar request DEVNET</button>
    </form>

    <?php if ($resultPayload !== null): ?>
        <hr>
        <h2>Resultado da acao</h2>
        <p><strong><?php echo h($resultMessage); ?></strong></p>
        <pre style="background:#111;color:#eee;padding:12px;border-radius:8px;overflow:auto;"><?php echo h(json_encode(byx_wallet_mask_internal_request_id($resultPayload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
    <?php endif; ?>
</section>
<script>
(function () {
    var forms = document.querySelectorAll('.js-byx-request-form');
    for (var i = 0; i < forms.length; i++) {
        forms[i].addEventListener('submit', function (event) {
            var input = this.querySelector('input[name="request_id"]');
            var value = input ? String(input.value || '').trim() : '';
            if (!/^[1-9][0-9]*$/.test(value)) {
                event.preventDefault();
                alert('Informe o ID numerico da cobranca BYX (inteiro positivo).');
                if (input) { input.focus(); }
            }
        });
    }
})();
</script>
<?php render_footer(); ?>
