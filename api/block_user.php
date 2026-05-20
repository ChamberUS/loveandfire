<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
$targetId = request_int('target_id', 0);
if ($targetId > 0 && $targetId !== intval($user['id'])) {
    $exists = db_fetch_one('SELECT id FROM blocks WHERE blocker_id = ' . intval($user['id']) . ' AND blocked_id = ' . intval($targetId) . ' LIMIT 1');
    if (!$exists) {
        db_query('INSERT INTO blocks (blocker_id, blocked_id, created_at) VALUES (' . intval($user['id']) . ', ' . intval($targetId) . ', ' . db_escape(now_sql()) . ')');
    }
    db_query("UPDATE conversations SET status = 'blocked' WHERE (user_one_id = " . intval($user['id']) . " AND user_two_id = " . intval($targetId) . ") OR (user_one_id = " . intval($targetId) . " AND user_two_id = " . intval($user['id']) . ")");
    flash_set('ok', 'Usuario bloqueado.');
}
redirect('../chat.php');
