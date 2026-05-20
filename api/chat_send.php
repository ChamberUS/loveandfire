<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(array('ok' => false, 'error' => 'Metodo invalido.'));
}
csrf_check();
$conversationId = intval($_POST['conversation_id']);
$body = post_text('body', 1000);
if ($body === '') {
    json_response(array('ok' => false, 'error' => 'Mensagem vazia.'));
}

$conversation = db_fetch_one('SELECT * FROM conversations WHERE id = ' . intval($conversationId) . ' AND status = \'active\' AND (user_one_id = ' . intval($user['id']) . ' OR user_two_id = ' . intval($user['id']) . ') LIMIT 1');
if (!$conversation) {
    json_response(array('ok' => false, 'error' => 'Conversa nao encontrada.'));
}

$partner = conversation_partner($conversation, $user['id']);
if (!$partner || users_are_blocked($user['id'], $partner['id'])) {
    json_response(array('ok' => false, 'error' => 'Conversa bloqueada.'));
}

ensure_message_edit_columns();
$now = now_sql();
db_query("INSERT INTO messages (conversation_id, sender_id, body, is_read, is_edited, is_deleted, is_reported, status, created_at, updated_at) VALUES (" . intval($conversationId) . ', ' . intval($user['id']) . ', ' . db_escape($body) . ", 0, 0, 0, 0, 'active', " . db_escape($now) . ', ' . db_escape($now) . ')');
db_query('UPDATE conversations SET updated_at = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($conversationId));
json_response(array('ok' => true, 'message_id' => intval(db_insert_id())));
