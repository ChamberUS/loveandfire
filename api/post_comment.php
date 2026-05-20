<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../feed.php'); }
csrf_check();
$postId = intval($_POST['post_id']);
$body = post_text('body', 500);
$post = db_fetch_one('SELECT id FROM posts WHERE id = ' . intval($postId) . ' LIMIT 1');
if ($post && $body !== '') {
    db_query('INSERT INTO post_comments (post_id, user_id, body, created_at) VALUES (' . intval($postId) . ', ' . intval($user['id']) . ', ' . db_escape($body) . ', ' . db_escape(now_sql()) . ')');
}
redirect('../feed.php');
