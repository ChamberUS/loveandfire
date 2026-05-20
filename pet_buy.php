<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/byx/byx_game.php';

$user = auth_require();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('err', 'Metodo invalido.');
    redirect('pets.php');
}

csrf_check();

function lf_notify($usuarioId, $tipo, $titulo, $mensagem, $link)
{
    $usuarioId = intval($usuarioId);
    if ($usuarioId <= 0) {
        return;
    }
    db_query('INSERT INTO lf_pet_notificacoes (usuario_id, tipo, titulo, mensagem, link, lida, created_at) VALUES (' . $usuarioId . ', ' . db_escape($tipo) . ', ' . db_escape($titulo) . ', ' . db_escape($mensagem) . ', ' . db_escape($link) . ', 0, ' . db_escape(now_sql()) . ')');
}

$petBlockId = isset($_POST['pet_block_id']) ? intval($_POST['pet_block_id']) : 0;
if ($petBlockId <= 0) {
    flash_set('err', 'Card social invalido.');
    redirect('pets.php');
}

$pet = db_fetch_one("SELECT pb.*, po.id AS ownership_id, po.dono_atual_id, po.usuario_original_id
FROM lf_pet_blocks pb
LEFT JOIN lf_pet_ownership po ON po.pet_block_id = pb.id
WHERE pb.id = " . $petBlockId . " AND pb.mundo = 'pets' AND pb.status = 'active' LIMIT 1");

if (!$pet) {
    flash_set('err', 'Card social nao encontrado.');
    redirect('pets.php');
}

if (intval($pet['usuario_id']) === intval($user['id'])) {
    flash_set('err', 'Voce nao pode disputar seu proprio card social.');
    redirect('pet_view.php?id=' . $petBlockId);
}

if (intval($pet['dono_atual_id']) === intval($user['id'])) {
    flash_set('err', 'Voce ja e o guardiao simbolico atual deste card.');
    redirect('pet_view.php?id=' . $petBlockId);
}

$valorCompra = (float)$pet['valor_atual'];
$percentualAumento = 0.25;
if ($valorCompra <= 100) {
    $percentualAumento = 3;
} elseif ($valorCompra <= 1000) {
    $percentualAumento = 2;
} elseif ($valorCompra <= 10000) {
    $percentualAumento = 1.5;
} elseif ($valorCompra <= 100000) {
    $percentualAumento = 1;
} elseif ($valorCompra <= 1000000) {
    $percentualAumento = 0.5;
}
$novoValor = $valorCompra + ($valorCompra * ($percentualAumento / 100));

$vendedorId = intval($pet['dono_atual_id']) > 0 ? intval($pet['dono_atual_id']) : 0;
$compradorId = intval($user['id']);
$usuarioOriginalId = intval($pet['usuario_id']);

$bonusOriginal = $valorCompra * 0.03;
$taxaPlataforma = $valorCompra * 0.05;
$taxaReserva = $valorCompra * 0.01;
$lucroVendedor = 0;
if ($vendedorId > 0 && $vendedorId !== $compradorId) {
    $lucroVendedor = $valorCompra * 0.90;
}

$purchase = byx_game_spend(
    $compradorId,
    $valorCompra,
    'pet_dispute',
    $petBlockId,
    'Disputa de card social #' . $petBlockId,
    array('mundo' => 'pets', 'valor_nao_reembolsavel' => 1)
);

if (!$purchase['ok']) {
    $msg = isset($purchase['error']) && $purchase['error'] !== '' ? $purchase['error'] : 'Não foi possível concluir a compra.';
    flash_set('err', $msg);
    redirect('pet_view.php?id=' . $petBlockId);
}
$orderRef = 'LF-PET-DISPUTE-' . date('Ymd-His') . '-' . $compradorId . '-' . $petBlockId;

if ($lucroVendedor > 0) {
    $vendResp = byx_game_reward($vendedorId, $lucroVendedor, 'pet_dispute_seller_reward', $petBlockId, 'Recompensa de guardiao anterior em disputa.', array('reference' => $orderRef));
    if (!$vendResp['ok']) {
        flash_set('err', 'Não foi possível concluir a compra.');
        redirect('pet_view.php?id=' . $petBlockId);
    }
}

$origResp = byx_game_reward($usuarioOriginalId, $bonusOriginal, 'pet_dispute_profile_reward', $petBlockId, 'Bonus ao perfil social original.', array('reference' => $orderRef));
if (!$origResp['ok']) {
    flash_set('err', 'Não foi possível concluir a compra.');
    redirect('pet_view.php?id=' . $petBlockId);
}

db_query('START TRANSACTION');
$now = now_sql();
$okOwn = db_query('UPDATE lf_pet_ownership SET dono_atual_id = ' . $compradorId . ', valor_atual = ' . db_escape(number_format($novoValor, 8, '.', '')) . ', ultima_compra_at = ' . db_escape($now) . ', updated_at = ' . db_escape($now) . ' WHERE pet_block_id = ' . $petBlockId . ' LIMIT 1');
if (!$okOwn) {
    db_query('ROLLBACK');
    flash_set('err', 'Não foi possível concluir a compra.');
    redirect('pet_view.php?id=' . $petBlockId);
}

$okBlock = db_query('UPDATE lf_pet_blocks SET valor_atual = ' . db_escape(number_format($novoValor, 8, '.', '')) . ', updated_at = ' . db_escape($now) . ' WHERE id = ' . $petBlockId . ' LIMIT 1');
if (!$okBlock) {
    db_query('ROLLBACK');
    flash_set('err', 'Não foi possível concluir a compra.');
    redirect('pet_view.php?id=' . $petBlockId);
}

$okPetTx = db_query('INSERT INTO lf_pet_transacoes (pet_block_id, usuario_original_id, comprador_id, vendedor_id, valor_anterior, valor_compra, novo_valor, lucro_vendedor, bonus_usuario_original, taxa_plataforma, taxa_reserva, percentual_aumento, created_at) VALUES (' . $petBlockId . ', ' . $usuarioOriginalId . ', ' . $compradorId . ', ' . ($vendedorId > 0 ? $vendedorId : 'NULL') . ', ' . db_escape(number_format($valorCompra, 8, '.', '')) . ', ' . db_escape(number_format($valorCompra, 8, '.', '')) . ', ' . db_escape(number_format($novoValor, 8, '.', '')) . ', ' . db_escape(number_format($lucroVendedor, 8, '.', '')) . ', ' . db_escape(number_format($bonusOriginal, 8, '.', '')) . ', ' . db_escape(number_format($taxaPlataforma, 8, '.', '')) . ', ' . db_escape(number_format($taxaReserva, 8, '.', '')) . ', ' . db_escape(number_format($percentualAumento, 4, '.', '')) . ', ' . db_escape($now) . ')');
if (!$okPetTx) {
    db_query('ROLLBACK');
    flash_set('err', 'Não foi possível concluir a compra.');
    redirect('pet_view.php?id=' . $petBlockId);
}

$txId = db_insert_id();
$okPlataformaLog = db_query('INSERT INTO lf_wallet_transacoes (usuario_id, tipo, direcao, valor, saldo_antes, saldo_depois, referencia_tipo, referencia_id, descricao, created_at) VALUES (0, \'pet_taxa_plataforma\', \'reserva\', ' . db_escape(number_format($taxaPlataforma, 8, '.', '')) . ', 0.00000000, 0.00000000, \'lf_pet_transacoes\', ' . intval($txId) . ', ' . db_escape('Taxa da plataforma em disputa simbolica #' . $txId) . ', ' . db_escape($now) . ')');
$okReservaLog = db_query('INSERT INTO lf_wallet_transacoes (usuario_id, tipo, direcao, valor, saldo_antes, saldo_depois, referencia_tipo, referencia_id, descricao, created_at) VALUES (0, \'pet_taxa_reserva\', \'reserva\', ' . db_escape(number_format($taxaReserva, 8, '.', '')) . ', 0.00000000, 0.00000000, \'lf_pet_transacoes\', ' . intval($txId) . ', ' . db_escape('Reserva simbolica em disputa #' . $txId) . ', ' . db_escape($now) . ')');

if (!$okPlataformaLog || !$okReservaLog) {
    db_query('ROLLBACK');
    flash_set('err', 'Não foi possível concluir a compra.');
    redirect('pet_view.php?id=' . $petBlockId);
}

db_query('COMMIT');

lf_notify($usuarioOriginalId, 'pet_disputado', 'Seu card social teve nova disputa', 'Seu card social teve nova guarda simbólica no LovenPets.', 'pet_view.php?id=' . $petBlockId);
if ($vendedorId > 0 && $vendedorId !== $compradorId) {
    lf_notify($vendedorId, 'pet_guardia_transferida', 'Guarda simbólica transferida', 'Outro jogador disputou o card social que estava sob sua guarda.', 'pet_view.php?id=' . $petBlockId);
}
lf_notify($compradorId, 'pet_guardiao_atual', 'Você é o guardião atual do card', 'A guarda simbólica deste card social agora está no seu perfil de jogo.', 'pet_view.php?id=' . $petBlockId);

flash_set('ok', 'Compra realizada com sucesso. Valor BYX não reembolsável.');
redirect('pet_view.php?id=' . $petBlockId);
