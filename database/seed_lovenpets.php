<?php
require_once dirname(__FILE__) . '/../core/bootstrap.php';
$user = auth_require();

if (!isset($user['role']) || ($user['role'] !== 'admin' && $user['role'] !== 'moderator')) {
    die('Acesso restrito.');
}

function lf_seed_ensure_wallet($usuarioId)
{
    $wallet = db_fetch_one('SELECT id FROM lf_wallets WHERE usuario_id = ' . intval($usuarioId) . ' LIMIT 1');
    if ($wallet) {
        return array('exists', intval($wallet['id']));
    }

    $now = now_sql();
    $ok = db_query('INSERT INTO lf_wallets (usuario_id, saldo_byx, saldo_bloqueado, status, created_at, updated_at) VALUES (' . intval($usuarioId) . ', 0.00000000, 0.00000000, \'active\', ' . db_escape($now) . ', ' . db_escape($now) . ')');
    if (!$ok) {
        return array('error', 0);
    }

    return array('created', db_insert_id());
}

function lf_seed_ensure_pet($usuario)
{
    $usuarioId = intval($usuario['id']);
    $block = db_fetch_one('SELECT * FROM lf_pet_blocks WHERE usuario_id = ' . $usuarioId . ' AND mundo = \'pets\' LIMIT 1');
    $now = now_sql();

    if ($block) {
        $blockId = intval($block['id']);
        $statusBlock = 'exists';
    } else {
        $codigo = 'LF-PET-' . $usuarioId . '-PETS';
        $ok = db_query('INSERT INTO lf_pet_blocks (usuario_id, mundo, codigo_publico, valor_inicial, valor_atual, status, created_at, updated_at) VALUES (' . $usuarioId . ', \'pets\', ' . db_escape($codigo) . ', 10.00000000, 10.00000000, \'active\', ' . db_escape($now) . ', ' . db_escape($now) . ')');
        if (!$ok) {
            return array('error', 0, 0);
        }
        $blockId = db_insert_id();
        $statusBlock = 'created';
    }

    $own = db_fetch_one('SELECT id FROM lf_pet_ownership WHERE pet_block_id = ' . $blockId . ' LIMIT 1');
    if ($own) {
        $statusOwn = 'exists';
    } else {
        $okOwn = db_query('INSERT INTO lf_pet_ownership (pet_block_id, usuario_original_id, dono_atual_id, valor_atual, ultima_compra_at, status, created_at, updated_at) VALUES (' . $blockId . ', ' . $usuarioId . ', NULL, 10.00000000, NULL, \'active\', ' . db_escape($now) . ', ' . db_escape($now) . ')');
        if (!$okOwn) {
            return array('error', $statusBlock === 'created' ? 1 : 0, 0);
        }
        $statusOwn = 'created';
    }

    return array($statusBlock, $statusOwn, $blockId);
}

$usuarios = db_fetch_all("SELECT id, name, status FROM users WHERE status = 'active' ORDER BY id ASC");

$walletCreated = 0;
$walletIgnored = 0;
$walletErrors = 0;
$blocksCreated = 0;
$blocksIgnored = 0;
$blocksErrors = 0;
$ownCreated = 0;
$ownIgnored = 0;
$ownErrors = 0;

foreach ($usuarios as $u) {
    $walletStatus = lf_seed_ensure_wallet($u['id']);
    if ($walletStatus[0] === 'created') {
        $walletCreated++;
    } elseif ($walletStatus[0] === 'exists') {
        $walletIgnored++;
    } else {
        $walletErrors++;
    }

    $petStatus = lf_seed_ensure_pet($u);
    if ($petStatus[0] === 'created') {
        $blocksCreated++;
    } elseif ($petStatus[0] === 'exists') {
        $blocksIgnored++;
    } else {
        $blocksErrors++;
    }

    if (isset($petStatus[1]) && $petStatus[1] === 'created') {
        $ownCreated++;
    } elseif (isset($petStatus[1]) && $petStatus[1] === 'exists') {
        $ownIgnored++;
    } else {
        $ownErrors++;
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo "LovenPets seed concluido\n";
echo "Usuarios ativos: " . count($usuarios) . "\n\n";
echo "Wallets BYX\n";
echo "- Criadas: " . $walletCreated . "\n";
echo "- Ignoradas: " . $walletIgnored . "\n";
echo "- Erros: " . $walletErrors . "\n\n";
echo "Pet Blocks\n";
echo "- Criados: " . $blocksCreated . "\n";
echo "- Ignorados: " . $blocksIgnored . "\n";
echo "- Erros: " . $blocksErrors . "\n\n";
echo "Ownership\n";
echo "- Criados: " . $ownCreated . "\n";
echo "- Ignorados: " . $ownIgnored . "\n";
echo "- Erros: " . $ownErrors . "\n";
