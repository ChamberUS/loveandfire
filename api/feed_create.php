<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../feed.php'); }
csrf_check();
$body = post_text('body', 1000);
if ($body === '') {
    flash_set('err', 'Escreva algo antes de publicar.');
    redirect('../feed.php');
}
db_query('INSERT INTO posts (user_id, body, created_at, updated_at) VALUES (' . intval($user['id']) . ', ' . db_escape($body) . ', ' . db_escape(now_sql()) . ', ' . db_escape(now_sql()) . ')');
flash_set('ok', 'Publicado no feed.');
redirect('../feed.php');
