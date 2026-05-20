<?php
if (!defined('HC_APP')) { die('Acesso negado'); }
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/byx_api.php';

function byx_game_ref($prefix, $usuarioId, $itemId)
{
    return $prefix . '-' . date('Ymd-His') . '-' . intval($usuarioId) . '-' . intval($itemId) . '-' . strtoupper(substr(sha1(uniqid((string)mt_rand(), true)), 0, 6));
}

function byx_game_get_balance($usuarioId)
{
    return byx_api_game_user_balance(intval($usuarioId));
}

function byx_game_get_transactions($usuarioId)
{
    return byx_api_game_user_transactions(intval($usuarioId));
}

function byx_game_spend($usuarioId, $valorByx, $tipo, $itemId, $descricao, $metadata)
{
    $usuarioId = intval($usuarioId);
    $valorByx = (float)$valorByx;
    if ($usuarioId <= 0 || $valorByx <= 0) {
        return array('ok' => false, 'error' => 'Nao foi possivel concluir a compra.');
    }

    $payload = array(
        'external_reference' => byx_game_ref('LF-PET-SPEND', $usuarioId, $itemId),
        'user_id' => $usuarioId,
        'amount_byx' => number_format($valorByx, 8, '.', ''),
        'type' => (string)$tipo,
        'item_id' => ($itemId === null ? null : intval($itemId)),
        'description' => (string)$descricao,
        'metadata' => is_array($metadata) ? $metadata : array()
    );

    return byx_api_game_petz_spend($payload);
}

function byx_game_reward($usuarioId, $valorByx, $tipo, $itemId, $descricao, $metadata)
{
    $usuarioId = intval($usuarioId);
    $valorByx = (float)$valorByx;
    if ($usuarioId <= 0 || $valorByx <= 0) {
        return array('ok' => false, 'error' => 'Nao foi possivel concluir a operacao.');
    }

    $payload = array(
        'external_reference' => byx_game_ref('LF-PET-REWARD', $usuarioId, $itemId),
        'user_id' => $usuarioId,
        'amount_byx' => number_format($valorByx, 8, '.', ''),
        'type' => (string)$tipo,
        'item_id' => ($itemId === null ? null : intval($itemId)),
        'description' => (string)$descricao,
        'metadata' => is_array($metadata) ? $metadata : array()
    );

    return byx_api_game_petz_reward($payload);
}
