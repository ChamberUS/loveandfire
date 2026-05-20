<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
$postId = request_int('post_id', 0);
if ($postId > 0) {
    $exists = db_fetch_one('SELECT id FROM post_reactions WHERE post_id = ' . intval($postId) . ' AND user_id = ' . intval($user['id']) . ' LIMIT 1');
    if (!$exists) {
        db_query("INSERT INTO post_reactions (post_id, user_id, reaction, created_at) VALUES (" . intval($postId) . ", " . intval($user['id']) . ", 'like', " . db_escape(now_sql()) . ")");
    }
}
redirect('../feed.php');
