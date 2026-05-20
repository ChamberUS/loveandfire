<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
ensure_message_edit_columns();

if (!hc_is_moderator($user)) {
    json_response(array('ok' => false, 'error' => 'Acesso restrito a moderadores.'));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Metodo invalido.'));
}
csrf_check();

$targetId = intval($_POST['target_id']);
if ($targetId <= 0 || $targetId === intval($user['id'])) {
    json_response(array('ok' => false, 'error' => 'Usuario invalido.'));
}

$target = db_fetch_one('SELECT id FROM users WHERE id = ' . intval($targetId) . ' LIMIT 1');
if (!$target) {
    json_response(array('ok' => false, 'error' => 'Usuario nao encontrado.'));
}

$now = now_sql();
db_query("UPDATE users SET status = 'banned', updated_at = " . db_escape($now) . ' WHERE id = ' . intval($targetId));
db_query("UPDATE conversations SET status = 'blocked', updated_at = " . db_escape($now) . ' WHERE user_one_id = ' . intval($targetId) . ' OR user_two_id = ' . intval($targetId));
db_query("UPDATE reports SET status = 'actioned', reviewed_by = " . intval($user['id']) . ', reviewed_at = ' . db_escape($now) . ' WHERE reported_id = ' . intval($targetId) . " AND status = 'open'");

flash_set('ok', 'Usuario bloqueado pela moderacao.');
redirect('../moderation_messages.php');
?>
