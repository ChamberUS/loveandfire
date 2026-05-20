<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

function users_are_blocked($a, $b)
{
    $row = db_fetch_one('SELECT id FROM blocks WHERE (blocker_id = ' . intval($a) . ' AND blocked_id = ' . intval($b) . ') OR (blocker_id = ' . intval($b) . ' AND blocked_id = ' . intval($a) . ') LIMIT 1');
    return $row ? true : false;
}

function find_active_match($a, $b)
{
    $a = intval($a);
    $b = intval($b);
    return db_fetch_one('SELECT * FROM matches WHERE status = \'active\' AND ((user_one_id = ' . $a . ' AND user_two_id = ' . $b . ') OR (user_one_id = ' . $b . ' AND user_two_id = ' . $a . ')) LIMIT 1');
}

function find_conversation_between($a, $b)
{
    $a = intval($a);
    $b = intval($b);
    return db_fetch_one('SELECT * FROM conversations WHERE status = \'active\' AND ((user_one_id = ' . $a . ' AND user_two_id = ' . $b . ') OR (user_one_id = ' . $b . ' AND user_two_id = ' . $a . ')) ORDER BY id DESC LIMIT 1');
}

function create_conversation($type, $userOne, $userTwo, $matchId, $postId, $createdBy)
{
    db_query("INSERT INTO conversations (type, user_one_id, user_two_id, match_id, post_id, created_by, status, created_at, updated_at)
        VALUES (" . db_escape($type) . ", " . intval($userOne) . ", " . intval($userTwo) . ", " . ($matchId ? intval($matchId) : 'NULL') . ", " . ($postId ? intval($postId) : 'NULL') . ", " . intval($createdBy) . ", 'active', " . db_escape(now_sql()) . ", " . db_escape(now_sql()) . ")");
    return db_insert_id();
}

function get_or_create_match_conversation($match)
{
    $existing = db_fetch_one('SELECT * FROM conversations WHERE match_id = ' . intval($match['id']) . ' LIMIT 1');
    if ($existing) {
        return intval($existing['id']);
    }
    return create_conversation('match', intval($match['user_one_id']), intval($match['user_two_id']), intval($match['id']), 0, intval($match['user_one_id']));
}

function daily_dm_count($userId)
{
    $row = db_fetch_one('SELECT COUNT(*) AS total FROM dm_limits WHERE user_id = ' . intval($userId) . ' AND day_date = ' . db_escape(today_sql()));
    return $row ? intval($row['total']) : 0;
}

function can_start_free_dm($fromUser, $toUser)
{
    if ($fromUser == $toUser) {
        return array(false, 'Voce nao pode iniciar conversa com voce mesmo.');
    }

    if (users_are_blocked($fromUser, $toUser)) {
        return array(false, 'Conversa bloqueada por privacidade.');
    }

    $target = db_fetch_one('SELECT id, dm_mode FROM users WHERE id = ' . intval($toUser) . ' AND status = \'active\' LIMIT 1');
    if (!$target) {
        return array(false, 'Usuario nao encontrado.');
    }

    $match = find_active_match($fromUser, $toUser);
    if ($match) {
        return array(true, 'match');
    }

    if ($target['dm_mode'] === 'closed') {
        return array(false, 'Este usuario fechou as DMs.');
    }

    if ($target['dm_mode'] === 'matches') {
        return array(false, 'Este usuario aceita mensagens apenas de matches.');
    }

    if (daily_dm_count($fromUser) >= FREE_DM_DAILY_LIMIT) {
        return array(false, 'Limite diario de DMs livres atingido. Tente novamente amanha.');
    }

    return array(true, 'free_dm');
}

function register_dm_limit_use($fromUser, $toUser, $conversationId)
{
    db_query("INSERT INTO dm_limits (user_id, target_id, conversation_id, day_date, created_at)
        VALUES (" . intval($fromUser) . ", " . intval($toUser) . ", " . intval($conversationId) . ", " . db_escape(today_sql()) . ", " . db_escape(now_sql()) . ")");
}

function get_or_create_free_dm($fromUser, $toUser, $postId)
{
    $existing = find_conversation_between($fromUser, $toUser);
    if ($existing) {
        return array(true, intval($existing['id']), 'Conversa aberta.');
    }

    $check = can_start_free_dm($fromUser, $toUser);
    if (!$check[0]) {
        return array(false, 0, $check[1]);
    }

    $type = $check[1] === 'match' ? 'match' : 'free_dm';
    $matchId = 0;
    if ($type === 'match') {
        $match = find_active_match($fromUser, $toUser);
        $matchId = intval($match['id']);
    }

    $conversationId = create_conversation($type, $fromUser, $toUser, $matchId, $postId, $fromUser);
    if ($type === 'free_dm') {
        register_dm_limit_use($fromUser, $toUser, $conversationId);
    }

    return array(true, $conversationId, 'Conversa criada.');
}

function conversation_partner($conversation, $myId)
{
    $partnerId = intval($conversation['user_one_id']) === intval($myId) ? intval($conversation['user_two_id']) : intval($conversation['user_one_id']);
    return db_fetch_one('SELECT * FROM users WHERE id = ' . intval($partnerId) . ' LIMIT 1');
}
