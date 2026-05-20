<?php
require_once dirname(dirname(__FILE__)) . '/core/bootstrap.php';
$user = auth_require();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('../swipe.php'); }
csrf_check();
$targetId = intval($_POST['target_id']);
$action = isset($_POST['action']) ? $_POST['action'] : 'pass';
if ($action !== 'like') { $action = 'pass'; }

if ($targetId > 0 && $targetId !== intval($user['id'])) {
    db_query('DELETE FROM swipes WHERE swiper_id = ' . intval($user['id']) . ' AND target_id = ' . intval($targetId));
    db_query('INSERT INTO swipes (swiper_id, target_id, action, created_at) VALUES (' . intval($user['id']) . ', ' . intval($targetId) . ', ' . db_escape($action) . ', ' . db_escape(now_sql()) . ')');

    if ($action === 'like') {
        $reverse = db_fetch_one("SELECT id FROM swipes WHERE swiper_id = " . intval($targetId) . " AND target_id = " . intval($user['id']) . " AND action = 'like' LIMIT 1");
        if ($reverse && !find_active_match($user['id'], $targetId)) {
            $one = min(intval($user['id']), $targetId);
            $two = max(intval($user['id']), $targetId);
            db_query("INSERT INTO matches (user_one_id, user_two_id, status, created_at) VALUES (" . $one . ", " . $two . ", 'active', " . db_escape(now_sql()) . ")");
            $match = db_fetch_one('SELECT * FROM matches WHERE id = ' . intval(db_insert_id()) . ' LIMIT 1');
            get_or_create_match_conversation($match);
            flash_set('ok', 'Deu match! O chat foi liberado.');
        } else {
            flash_set('ok', 'Curtida registrada.');
        }
    } else {
        flash_set('ok', 'Perfil passado.');
    }
}
redirect('../swipe.php');
