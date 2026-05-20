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
$reason = post_text('reason', 180);
if ($reason === '') {
    $reason = 'mensagem_inadequada';
}

if ($messageId <= 0 || $conversationId <= 0) {
    json_response(array('ok' => false, 'error' => 'Mensagem invalida.'));
}

$message = db_fetch_one('SELECT m.*, c.user_one_id, c.user_two_id, c.status FROM messages m INNER JOIN conversations c ON c.id = m.conversation_id WHERE m.id = ' . intval($messageId) . ' AND m.conversation_id = ' . intval($conversationId) . ' LIMIT 1');
if (!$message || $message['status'] === 'blocked') {
    json_response(array('ok' => false, 'error' => 'Mensagem nao encontrada.'));
}
if (intval($message['is_deleted']) === 1) {
    json_response(array('ok' => false, 'error' => 'Mensagem apagada nao pode ser denunciada.'));
}
if (intval($message['sender_id']) === intval($user['id'])) {
    json_response(array('ok' => false, 'error' => 'Voce nao pode denunciar sua propria mensagem.'));
}
$isParticipant = intval($message['user_one_id']) === intval($user['id']) || intval($message['user_two_id']) === intval($user['id']);
if (!$isParticipant) {
    json_response(array('ok' => false, 'error' => 'Voce nao tem acesso a esta conversa.'));
}

$exists = db_fetch_one('SELECT id FROM reports WHERE reporter_id = ' . intval($user['id']) . ' AND message_id = ' . intval($messageId) . ' AND status = \'open\' LIMIT 1');
if (!$exists) {
    db_query("INSERT INTO reports (reporter_id, reported_id, post_id, message_id, conversation_id, reason, details, snapshot_body, status, created_at)
        VALUES (" . intval($user['id']) . ', ' . intval($message['sender_id']) . ', NULL, ' . intval($messageId) . ', ' . intval($conversationId) . ', ' . db_escape($reason) . ", 'denuncia_de_mensagem', " . db_escape($message['body']) . ", 'open', " . db_escape(now_sql()) . ')');
}
db_query("UPDATE messages SET is_reported = 1, status = 'reported', updated_at = " . db_escape(now_sql()) . ' WHERE id = ' . intval($messageId) . ' AND is_deleted = 0');

json_response(array('ok' => true, 'message_id' => $messageId, 'reported' => 1));
?>
