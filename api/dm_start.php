<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
$targetId = request_int('target_id', 0);
$postId = request_int('post_id', 0);

$result = get_or_create_free_dm(intval($user['id']), $targetId, $postId);
if ($result[0]) {
    redirect('../chat.php?conversation_id=' . intval($result[1]));
}
flash_set('err', $result[2]);
redirect('../dashboard.php');
