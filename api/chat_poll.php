<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
$conversationId = request_int('conversation_id', 0);
$lastId = request_int('last_id', 0);

$conversation = db_fetch_one('SELECT * FROM conversations WHERE id = ' . intval($conversationId) . ' AND status = \'active\' AND (user_one_id = ' . intval($user['id']) . ' OR user_two_id = ' . intval($user['id']) . ') LIMIT 1');
if (!$conversation) {
    json_response(array('ok' => false, 'error' => 'Conversa nao encontrada.'));
}

ensure_message_edit_columns();

$where = 'conversation_id = ' . intval($conversationId);
if ($lastId > 0) {
    $where .= ' AND id > ' . intval($lastId);
}
$messages = db_fetch_all('SELECT m.*, u.name, u.avatar, u.id AS user_id FROM messages m INNER JOIN users u ON u.id = m.sender_id WHERE ' . $where . ' ORDER BY m.id ASC LIMIT 100');

$out = array();
foreach ($messages as $m) {
    $sender = array(
        'id' => $m['user_id'],
        'name' => $m['name'],
        'avatar' => $m['avatar']
    );
    $isDeleted = intval($m['is_deleted']) === 1;
    $isEdited = !$isDeleted && (intval($m['is_edited']) === 1 || !empty($m['edited_at']));
    $out[] = array(
        'id' => intval($m['id']),
        'sender_id' => intval($m['sender_id']),
        'name' => $m['name'],
        'avatar_url' => love_profile_photo($sender, intval($m['sender_id'])),
        'body' => $isDeleted ? 'Mensagem excluida.' : $m['body'],
        'mine' => intval($m['sender_id']) === intval($user['id']) ? 1 : 0,
        'created_at' => $m['created_at'],
        'edited_at' => $m['edited_at'],
        'deleted_at' => $m['deleted_at'],
        'is_deleted' => $isDeleted ? 1 : 0,
        'is_edited' => $isEdited ? 1 : 0,
        'is_reported' => intval($m['is_reported']) === 1 ? 1 : 0,
        'status' => $m['status'],
        'can_edit' => hc_message_can_edit($m, $user) ? 1 : 0,
        'can_delete' => hc_message_can_delete($m, $user) ? 1 : 0,
        'can_report' => (!$isDeleted && intval($m['sender_id']) !== intval($user['id'])) ? 1 : 0,
        'can_block' => intval($m['sender_id']) !== intval($user['id']) ? 1 : 0
    );
}

db_query('UPDATE messages SET is_read = 1 WHERE conversation_id = ' . intval($conversationId) . ' AND sender_id <> ' . intval($user['id']));
json_response(array('ok' => true, 'messages' => $out));
