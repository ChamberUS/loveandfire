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

$messageId = intval($_POST['message_id']);
if ($messageId <= 0) {
    json_response(array('ok' => false, 'error' => 'Mensagem invalida.'));
}

$message = db_fetch_one('SELECT * FROM messages WHERE id = ' . intval($messageId) . ' LIMIT 1');
if (!$message) {
    json_response(array('ok' => false, 'error' => 'Mensagem nao encontrada.'));
}

$now = now_sql();
db_query("UPDATE messages SET body = '', is_deleted = 1, deleted_at = " . db_escape($now) . ', deleted_by = ' . intval($user['id']) . ', updated_at = ' . db_escape($now) . ", status = 'deleted' WHERE id = " . intval($messageId));
db_query("UPDATE reports SET status = 'actioned', reviewed_by = " . intval($user['id']) . ', reviewed_at = ' . db_escape($now) . ' WHERE message_id = ' . intval($messageId) . " AND status = 'open'");

flash_set('ok', 'Mensagem apagada pela moderacao.');
redirect('../moderation_messages.php');
?>
