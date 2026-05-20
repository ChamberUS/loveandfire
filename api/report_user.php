<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
$targetId = request_int('target_id', 0);
$postId = request_int('post_id', 0);
if ($targetId > 0 && $targetId !== intval($user['id'])) {
    db_query("INSERT INTO reports (reporter_id, reported_id, post_id, reason, status, created_at) VALUES (" . intval($user['id']) . ", " . intval($targetId) . ", " . ($postId ? intval($postId) : 'NULL') . ", 'denuncia_rapida', 'open', " . db_escape(now_sql()) . ")");
    flash_set('ok', 'Denuncia registrada para analise.');
}
if ($postId) {
    redirect('../feed.php');
}
redirect('../chat.php');
