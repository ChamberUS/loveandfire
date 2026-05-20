<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Metodo invalido.'));
}
csrf_check();

ensure_message_edit_columns();

$messageId = intval($_POST['message_id']);
$conversationId = intval($_POST['conversation_id']);
$body = post_text('body', 1000);

if ($messageId <= 0 || $conversationId <= 0) {
    json_response(array('ok' => false, 'error' => 'Mensagem invalida.'));
}
if ($body === '') {
    json_response(array('ok' => false, 'error' => 'Mensagem vazia.'));
}

$message = db_fetch_one('SELECT m.*, c.user_one_id, c.user_two_id, c.status FROM messages m INNER JOIN conversations c ON c.id = m.conversation_id WHERE m.id = ' . intval($messageId) . ' AND m.conversation_id = ' . intval($conversationId) . ' LIMIT 1');
if (!$message || $message['status'] !== 'active') {
    json_response(array('ok' => false, 'error' => 'Mensagem nao encontrada.'));
}
if (intval($message['sender_id']) !== intval($user['id'])) {
    json_response(array('ok' => false, 'error' => 'Nao foi possivel editar esta mensagem.'));
}
if (intval($message['is_deleted']) === 1) {
    json_response(array('ok' => false, 'error' => 'Mensagem excluida nao pode ser editada.'));
}

$now = now_sql();
db_query("UPDATE messages SET body = " . db_escape($body) . ", is_edited = 1, edited_at = " . db_escape($now) . ', updated_at = ' . db_escape($now) . " WHERE id = " . intval($messageId) . ' AND sender_id = ' . intval($user['id']));
db_query('UPDATE conversations SET updated_at = ' . db_escape($now) . ' WHERE id = ' . intval($conversationId));

json_response(array('ok' => true, 'message_id' => $messageId, 'body' => $body, 'edited_at' => $now));
?>
