<?php
/*
|--------------------------------------------------------------------------
| LOVE & FIRE - chat.php
| Versao: 1.2 Renew Direct SQL
|--------------------------------------------------------------------------
| - Design premium mantido
| - AJAX com JSON limpo
| - Envio, edicao e exclusao sem refresh
| - Sem "Resposta invalida do servidor" no envio
| - SQL direto com mysql_query quando disponivel
| - Evita wrappers do projeto que podem imprimir HTML e quebrar JSON
|--------------------------------------------------------------------------
*/

ob_start();

$LF_RENEW_AJAX = false;

if (isset($_GET['renew_ajax']) || isset($_POST['renew_ajax'])) {
    $LF_RENEW_AJAX = true;
}

if (isset($_GET['renew_chat_action']) || isset($_POST['renew_chat_action'])) {
    $LF_RENEW_AJAX = true;
}

if (isset($_GET['lf2_chat_action']) || isset($_POST['lf2_chat_action'])) {
    $LF_RENEW_AJAX = true;
}

if (isset($_GET['hc_chat_action']) || isset($_POST['hc_chat_action'])) {
    $LF_RENEW_AJAX = true;
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $LF_RENEW_AJAX = true;
}

if ($LF_RENEW_AJAX) {
    @ini_set('display_errors', '0');
    @error_reporting(0);
}

$GLOBALS['LF_RENEW_AJAX'] = $LF_RENEW_AJAX;
$GLOBALS['LF_RENEW_JSON_SENT'] = false;
$GLOBALS['LF_RENEW_MESSAGE_COLUMNS'] = array();
$GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = '';

function lf_renew_shutdown_json() {
    if (empty($GLOBALS['LF_RENEW_AJAX'])) {
        return;
    }

    if (!empty($GLOBALS['LF_RENEW_JSON_SENT'])) {
        return;
    }

    $error = error_get_last();

    if (!$error || !isset($error['type'])) {
        return;
    }

    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR);

    if (!in_array($error['type'], $fatalTypes)) {
        return;
    }

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode(array(
        'ok' => false,
        'error' => 'Erro interno no servidor do chat.'
    ));
}

register_shutdown_function('lf_renew_shutdown_json');

require_once dirname(__FILE__) . '/core/bootstrap.php';

function lf_renew_json($data) {
    $GLOBALS['LF_RENEW_JSON_SENT'] = true;

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    echo json_encode($data);
    exit;
}

function lf_renew_query_direct($sql) {
    $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = '';

    if (function_exists('mysql_query')) {
        $res = @mysql_query($sql);

        if (!$res && function_exists('mysql_error')) {
            $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = mysql_error();
        }

        return $res;
    }

    if (function_exists('db_query')) {
        $res = @db_query($sql);

        if (!$res && function_exists('db_error')) {
            $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = db_error();
        }

        return $res;
    }

    $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = 'Nenhuma funcao SQL disponivel.';
    return false;
}

function lf_renew_fetch_all_direct($sql) {
    $rows = array();

    if (function_exists('mysql_query') && function_exists('mysql_fetch_assoc')) {
        $res = @mysql_query($sql);

        if (!$res) {
            if (function_exists('mysql_error')) {
                $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = mysql_error();
            }

            return array();
        }

        while ($row = mysql_fetch_assoc($res)) {
            $rows[] = $row;
        }

        return $rows;
    }

    if (function_exists('db_fetch_all')) {
        $r = @db_fetch_all($sql);
        return $r ? $r : array();
    }

    return array();
}

function lf_renew_fetch_one_direct($sql) {
    if (function_exists('mysql_query') && function_exists('mysql_fetch_assoc')) {
        $res = @mysql_query($sql);

        if (!$res) {
            if (function_exists('mysql_error')) {
                $GLOBALS['LF_RENEW_LAST_SQL_ERROR'] = mysql_error();
            }

            return false;
        }

        $row = mysql_fetch_assoc($res);
        return $row ? $row : false;
    }

    if (function_exists('db_fetch_one')) {
        return @db_fetch_one($sql);
    }

    return false;
}

function lf_renew_escape($value) {
    if (!is_string($value)) {
        $value = strval($value);
    }

    $value = str_replace("\x00", '', $value);

    if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($value);
    }

    if (function_exists('db_escape')) {
        return db_escape($value);
    }

    return addslashes($value);
}

function lf_renew_h($value) {
    if (!is_string($value)) {
        $value = strval($value);
    }

    if (function_exists('h')) {
        return h($value);
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function lf_renew_action() {
    if (isset($_POST['renew_chat_action'])) {
        return trim($_POST['renew_chat_action']);
    }

    if (isset($_GET['renew_chat_action'])) {
        return trim($_GET['renew_chat_action']);
    }

    if (isset($_POST['lf2_chat_action'])) {
        return trim($_POST['lf2_chat_action']);
    }

    if (isset($_GET['lf2_chat_action'])) {
        return trim($_GET['lf2_chat_action']);
    }

    if (isset($_POST['hc_chat_action'])) {
        return trim($_POST['hc_chat_action']);
    }

    if (isset($_GET['hc_chat_action'])) {
        return trim($_GET['hc_chat_action']);
    }

    return '';
}

function lf_renew_int($key, $default) {
    if (isset($_POST[$key])) {
        return intval($_POST[$key]);
    }

    if (isset($_GET[$key])) {
        return intval($_GET[$key]);
    }

    return intval($default);
}

function lf_renew_limit_text($text, $limit) {
    $text = trim(strval($text));

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $limit) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }

        return $text;
    }

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit);
    }

    return $text;
}

function lf_renew_excerpt($text, $limit) {
    $text = trim(strval($text));

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $limit) {
            return mb_substr($text, 0, $limit, 'UTF-8') . '...';
        }

        return $text;
    }

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }

    return $text;
}

function lf_renew_load_message_columns() {
    $GLOBALS['LF_RENEW_MESSAGE_COLUMNS'] = array();

    $rows = lf_renew_fetch_all_direct('SHOW COLUMNS FROM messages');

    foreach ($rows as $row) {
        if (isset($row['Field'])) {
            $GLOBALS['LF_RENEW_MESSAGE_COLUMNS'][strtolower($row['Field'])] = $row;
        }
    }
}

function lf_renew_has_col($name) {
    return isset($GLOBALS['LF_RENEW_MESSAGE_COLUMNS'][strtolower($name)]);
}

function lf_renew_prepare_messages_table() {
    if (function_exists('ensure_message_edit_columns')) {
        @ensure_message_edit_columns();
    }

    lf_renew_load_message_columns();

    if (!lf_renew_has_col('is_deleted')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (!lf_renew_has_col('updated_at')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN updated_at DATETIME NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('edited_at')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN edited_at DATETIME NULL DEFAULT NULL');
    }

    lf_renew_load_message_columns();
}

function lf_renew_conversation($conversationId, $userId) {
    return lf_renew_fetch_one_direct(
        'SELECT *
         FROM conversations
         WHERE id = ' . intval($conversationId) . '
         AND status = \'active\'
         AND (user_one_id = ' . intval($userId) . ' OR user_two_id = ' . intval($userId) . ')
         LIMIT 1'
    );
}

function lf_renew_partner($conversation, $userId) {
    if (function_exists('conversation_partner')) {
        return conversation_partner($conversation, $userId);
    }

    $partnerId = 0;

    if (isset($conversation['user_one_id']) && intval($conversation['user_one_id']) === intval($userId)) {
        $partnerId = isset($conversation['user_two_id']) ? intval($conversation['user_two_id']) : 0;
    } else {
        $partnerId = isset($conversation['user_one_id']) ? intval($conversation['user_one_id']) : 0;
    }

    if ($partnerId <= 0) {
        return false;
    }

    return lf_renew_fetch_one_direct('SELECT * FROM users WHERE id = ' . intval($partnerId) . ' LIMIT 1');
}

function lf_renew_first_name($name) {
    $name = trim(strval($name));

    if (function_exists('love_first_name')) {
        return love_first_name($name);
    }

    if ($name === '') {
        return 'Usuario';
    }

    $parts = explode(' ', $name);
    return $parts[0];
}

function lf_renew_photo($profile, $index) {
    if (function_exists('love_profile_photo')) {
        return love_profile_photo($profile, $index);
    }

    if (is_array($profile) && isset($profile['avatar']) && trim($profile['avatar']) !== '') {
        return $profile['avatar'];
    }

    return 'assets/img/avatar-placeholder.svg';
}

function lf_renew_online($profile) {
    if (function_exists('love_is_online')) {
        return love_is_online($profile);
    }

    return false;
}

function lf_renew_csrf() {
    if (function_exists('csrf_token')) {
        return csrf_token();
    }

    return '';
}

function lf_renew_format_message($row) {
    return array(
        'id' => isset($row['id']) ? intval($row['id']) : 0,
        'conversation_id' => isset($row['conversation_id']) ? intval($row['conversation_id']) : 0,
        'sender_id' => isset($row['sender_id']) ? intval($row['sender_id']) : 0,
        'body' => isset($row['body']) ? strval($row['body']) : '',
        'is_deleted' => isset($row['is_deleted']) ? intval($row['is_deleted']) : 0,
        'created_at' => isset($row['created_at']) ? strval($row['created_at']) : '',
        'updated_at' => isset($row['updated_at']) ? strval($row['updated_at']) : '',
        'edited_at' => isset($row['edited_at']) ? strval($row['edited_at']) : ''
    );
}

function lf_renew_insert_message($conversationId, $senderId, $body) {
    $fields = array();
    $values = array();

    if (lf_renew_has_col('conversation_id')) {
        $fields[] = 'conversation_id';
        $values[] = intval($conversationId);
    }

    if (lf_renew_has_col('sender_id')) {
        $fields[] = 'sender_id';
        $values[] = intval($senderId);
    }

    if (lf_renew_has_col('body')) {
        $fields[] = 'body';
        $values[] = '\'' . lf_renew_escape($body) . '\'';
    }

    if (lf_renew_has_col('attachment_path')) {
        $fields[] = 'attachment_path';
        $values[] = 'NULL';
    }

    if (lf_renew_has_col('is_read')) {
        $fields[] = 'is_read';
        $values[] = 0;
    }

    if (lf_renew_has_col('created_at')) {
        $fields[] = 'created_at';
        $values[] = 'NOW()';
    }

    if (lf_renew_has_col('updated_at')) {
        $fields[] = 'updated_at';
        $values[] = 'NOW()';
    }

    if (lf_renew_has_col('edited_at')) {
        $fields[] = 'edited_at';
        $values[] = 'NULL';
    }

    if (lf_renew_has_col('deleted_at')) {
        $fields[] = 'deleted_at';
        $values[] = 'NULL';
    }

    if (lf_renew_has_col('is_deleted')) {
        $fields[] = 'is_deleted';
        $values[] = 0;
    }

    if (lf_renew_has_col('is_edited')) {
        $fields[] = 'is_edited';
        $values[] = 0;
    }

    if (lf_renew_has_col('deleted_by')) {
        $fields[] = 'deleted_by';
        $values[] = 'NULL';
    }

    if (lf_renew_has_col('is_reported')) {
        $fields[] = 'is_reported';
        $values[] = 0;
    }

    if (lf_renew_has_col('status')) {
        $fields[] = 'status';
        $values[] = '\'active\'';
    }

    $sql = 'INSERT INTO messages (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';

    return lf_renew_query_direct($sql);
}

function lf_renew_update_conversation($conversationId) {
    return lf_renew_query_direct(
        'UPDATE conversations
         SET updated_at = NOW()
         WHERE id = ' . intval($conversationId) . '
         LIMIT 1'
    );
}

if (!function_exists('auth_require')) {
    if ($GLOBALS['LF_RENEW_AJAX']) {
        lf_renew_json(array('ok' => false, 'error' => 'Autenticacao indisponivel.'));
    }

    die('Autenticacao indisponivel.');
}

$user = auth_require();

if (!$user || !isset($user['id'])) {
    if ($GLOBALS['LF_RENEW_AJAX']) {
        lf_renew_json(array('ok' => false, 'error' => 'Sessao expirada.'));
    }

    die('Sessao expirada.');
}

@lf_renew_query_direct('SET NAMES utf8');
@lf_renew_query_direct('SET CHARACTER SET utf8');

lf_renew_prepare_messages_table();

$action = lf_renew_action();

if ($action === 'fetch') {
    $conversationIdFetch = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdFetch, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false, 'error' => 'Conversa nao encontrada.'));
    }

    $rows = lf_renew_fetch_all_direct(
        'SELECT *
         FROM messages
         WHERE conversation_id = ' . intval($conversationIdFetch) . '
         ORDER BY id ASC
         LIMIT 500'
    );

    $messages = array();

    foreach ($rows as $row) {
        $messages[] = lf_renew_format_message($row);
    }

    lf_renew_json(array('ok' => true, 'messages' => $messages));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== '') {
    $conversationIdPost = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdPost, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false, 'error' => 'Conversa nao encontrada.'));
    }

    if ($action === 'send') {
        $body = isset($_POST['body']) ? lf_renew_limit_text($_POST['body'], 1000) : '';

        if ($body === '') {
            lf_renew_json(array('ok' => false, 'error' => 'Digite uma mensagem.'));
        }

        $insert = lf_renew_insert_message($conversationIdPost, $user['id'], $body);

        if (!$insert) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'Nao foi possivel enviar a mensagem.',
                'debug' => $GLOBALS['LF_RENEW_LAST_SQL_ERROR']
            ));
        }

        lf_renew_update_conversation($conversationIdPost);

        lf_renew_json(array('ok' => true, 'message' => 'Mensagem enviada.'));
    }

    if ($action === 'edit') {
        $messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
        $body = isset($_POST['body']) ? lf_renew_limit_text($_POST['body'], 1000) : '';

        if ($messageId <= 0) {
            lf_renew_json(array('ok' => false, 'error' => 'Mensagem invalida.'));
        }

        if ($body === '') {
            lf_renew_json(array('ok' => false, 'error' => 'Digite uma mensagem.'));
        }

        $whereDeleted = lf_renew_has_col('is_deleted') ? ' AND is_deleted = 0 ' : '';

        $message = lf_renew_fetch_one_direct(
            'SELECT id
             FROM messages
             WHERE id = ' . intval($messageId) . '
             AND conversation_id = ' . intval($conversationIdPost) . '
             AND sender_id = ' . intval($user['id']) . '
             ' . $whereDeleted . '
             LIMIT 1'
        );

        if (!$message) {
            lf_renew_json(array('ok' => false, 'error' => 'Voce nao pode editar esta mensagem.'));
        }

        $set = array();
        $set[] = 'body = \'' . lf_renew_escape($body) . '\'';

        if (lf_renew_has_col('updated_at')) {
            $set[] = 'updated_at = NOW()';
        }

        if (lf_renew_has_col('edited_at')) {
            $set[] = 'edited_at = NOW()';
        }

        if (lf_renew_has_col('is_edited')) {
            $set[] = 'is_edited = 1';
        }

        $update = lf_renew_query_direct(
            'UPDATE messages
             SET ' . implode(', ', $set) . '
             WHERE id = ' . intval($messageId) . '
             AND conversation_id = ' . intval($conversationIdPost) . '
             AND sender_id = ' . intval($user['id']) . '
             ' . $whereDeleted . '
             LIMIT 1'
        );

        if (!$update) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'Nao foi possivel editar a mensagem.',
                'debug' => $GLOBALS['LF_RENEW_LAST_SQL_ERROR']
            ));
        }

        lf_renew_update_conversation($conversationIdPost);

        lf_renew_json(array('ok' => true, 'message' => 'Mensagem editada.'));
    }

    if ($action === 'delete') {
        $messageId = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;

        if ($messageId <= 0) {
            lf_renew_json(array('ok' => false, 'error' => 'Mensagem invalida.'));
        }

        $whereDeleted = lf_renew_has_col('is_deleted') ? ' AND is_deleted = 0 ' : '';

        $message = lf_renew_fetch_one_direct(
            'SELECT id
             FROM messages
             WHERE id = ' . intval($messageId) . '
             AND conversation_id = ' . intval($conversationIdPost) . '
             AND sender_id = ' . intval($user['id']) . '
             ' . $whereDeleted . '
             LIMIT 1'
        );

        if (!$message) {
            lf_renew_json(array('ok' => false, 'error' => 'Voce nao pode excluir esta mensagem.'));
        }

        $set = array();
        $set[] = 'body = \'\'';

        if (lf_renew_has_col('is_deleted')) {
            $set[] = 'is_deleted = 1';
        }

        if (lf_renew_has_col('updated_at')) {
            $set[] = 'updated_at = NOW()';
        }

        if (lf_renew_has_col('edited_at')) {
            $set[] = 'edited_at = NOW()';
        }

        if (lf_renew_has_col('deleted_at')) {
            $set[] = 'deleted_at = NOW()';
        }

        if (lf_renew_has_col('deleted_by')) {
            $set[] = 'deleted_by = ' . intval($user['id']);
        }

        $delete = lf_renew_query_direct(
            'UPDATE messages
             SET ' . implode(', ', $set) . '
             WHERE id = ' . intval($messageId) . '
             AND conversation_id = ' . intval($conversationIdPost) . '
             AND sender_id = ' . intval($user['id']) . '
             ' . $whereDeleted . '
             LIMIT 1'
        );

        if (!$delete) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'Nao foi possivel excluir a mensagem.',
                'debug' => $GLOBALS['LF_RENEW_LAST_SQL_ERROR']
            ));
        }

        lf_renew_update_conversation($conversationIdPost);

        lf_renew_json(array('ok' => true, 'message' => 'Mensagem excluida.'));
    }

    lf_renew_json(array('ok' => false, 'error' => 'Acao invalida.'));
}

if ($GLOBALS['LF_RENEW_AJAX']) {
    lf_renew_json(array('ok' => false, 'error' => 'Acao AJAX invalida.'));
}

$conversationId = lf_renew_int('conversation_id', 0);

$conversations = lf_renew_fetch_all_direct(
    'SELECT c.*
     FROM conversations c
     WHERE c.status = \'active\'
     AND (c.user_one_id = ' . intval($user['id']) . ' OR c.user_two_id = ' . intval($user['id']) . ')
     ORDER BY c.updated_at DESC, c.id DESC
     LIMIT 50'
);

if (!$conversationId && count($conversations) > 0) {
    $conversationId = intval($conversations[0]['id']);
}

$activeConversation = false;
$partner = false;

if ($conversationId) {
    $activeConversation = lf_renew_conversation($conversationId, $user['id']);

    if ($activeConversation) {
        $partner = lf_renew_partner($activeConversation, $user['id']);
    }
}

if (function_exists('render_header')) {
    render_header('Mensagens', 'chat');
}
?>

<style>
#lf2ChatApp,
#lf2ChatApp * {
    box-sizing: border-box;
}

#lf2ChatApp {
    --lf2-pink: #f2365f;
    --lf2-text: #4d4449;
    --lf2-muted: #94888d;
    --lf2-soft: #f7f8fa;
    --lf2-line: rgba(220, 218, 220, 0.95);
    --lf2-white: #ffffff;
    --lf2-shadow: 0 28px 70px rgba(98, 84, 90, 0.13);
    --lf2-shadow-soft: 0 12px 30px rgba(98, 84, 90, 0.07);
    width: 100%;
}

#lf2ChatApp .lf2-shell {
    display: flex;
    min-height: 690px;
    border-radius: 34px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(255,255,255,0.96), rgba(248,248,249,0.98));
    border: 1px solid var(--lf2-line);
    box-shadow:
        0 34px 80px rgba(127, 102, 110, 0.12),
        0 10px 26px rgba(127, 102, 110, 0.05),
        inset 0 1px 0 rgba(255,255,255,0.95);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
}

#lf2ChatApp .lf2-sidebar {
    width: 345px;
    flex: 0 0 345px;
    padding: 20px 18px;
    background:
        radial-gradient(circle at 20% 0%, rgba(255,255,255,0.98), rgba(255,255,255,0) 36%),
        linear-gradient(180deg, rgba(255,255,255,0.90), rgba(248,248,249,0.98));
    border-right: 1px solid var(--lf2-line);
}

#lf2ChatApp .lf2-sidebar-head {
    padding: 4px 8px 18px;
}

#lf2ChatApp .lf2-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 16px;
    border-radius: 999px;
    background: rgba(255,255,255,0.95);
    border: 1px solid rgba(255, 65, 103, 0.18);
    color: var(--lf2-pink);
    font-size: 13px;
    font-weight: 900;
    box-shadow: var(--lf2-shadow-soft);
}

#lf2ChatApp .lf2-sidebar-head h1 {
    margin: 18px 0 0;
    font-size: 30px;
    line-height: 1.05;
    color: var(--lf2-pink);
    font-weight: 900;
    letter-spacing: -0.8px;
}

#lf2ChatApp .lf2-empty {
    margin: 12px 6px 18px;
    padding: 16px 18px;
    border-radius: 22px;
    background: rgba(255,255,255,0.76);
    border: 1px dashed rgba(210, 199, 203, 0.95);
    color: #92848a;
    font-size: 13px;
    font-weight: 800;
}

#lf2ChatApp .lf2-empty-main {
    max-width: 520px;
    margin: 70px auto;
    text-align: center;
    line-height: 1.55;
}

#lf2ChatApp .lf2-conversation {
    position: relative;
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 10px 0;
    padding: 15px 14px;
    border-radius: 26px;
    text-decoration: none !important;
    background: rgba(255,255,255,0.64);
    border: 1px solid rgba(229, 222, 224, 0.94);
    box-shadow: 0 14px 28px rgba(127, 102, 110, 0.045);
    transition: all .24s ease;
}

#lf2ChatApp .lf2-conversation:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.95);
    box-shadow: 0 20px 36px rgba(127, 102, 110, 0.085);
}

#lf2ChatApp .lf2-conversation.active {
    background: linear-gradient(135deg, rgba(255,255,255,0.99), rgba(247,247,248,0.98));
    border-color: rgba(224, 211, 214, 0.98);
    box-shadow:
        0 20px 38px rgba(127, 102, 110, 0.10),
        inset 4px 0 0 rgba(242, 54, 95, 0.82);
}

#lf2ChatApp .lf2-avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    flex: 0 0 auto;
    border: 2px solid rgba(255,255,255,0.98);
    box-shadow: 0 9px 20px rgba(0,0,0,0.10);
}

#lf2ChatApp .lf2-conversation-info {
    min-width: 0;
    flex: 1;
}

#lf2ChatApp .lf2-conversation strong {
    display: inline-block;
    margin-right: 7px;
    color: #5d454d;
    font-size: 15px;
    font-weight: 900;
    vertical-align: middle;
}

#lf2ChatApp .lf2-type {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 10.5px;
    font-weight: 900;
    vertical-align: middle;
}

#lf2ChatApp .lf2-type.match {
    color: var(--lf2-pink);
    background: rgba(242, 54, 95, 0.10);
}

#lf2ChatApp .lf2-type.free,
#lf2ChatApp .lf2-type.dm,
#lf2ChatApp .lf2-type.livre {
    color: #657b8a;
    background: rgba(101, 123, 138, 0.12);
}

#lf2ChatApp .lf2-conversation p {
    margin: 8px 0 0;
    font-size: 13px;
    color: #9a8b91;
    line-height: 1.45;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#lf2ChatApp .lf2-window {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    position: relative;
    background:
        radial-gradient(circle at 80% 10%, rgba(255,255,255,0.98), rgba(255,255,255,0) 32%),
        linear-gradient(180deg, rgba(252,252,253,0.98), rgba(247,247,248,0.98));
}

#lf2ChatApp .lf2-title {
    min-height: 112px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--lf2-line);
    background: rgba(255,255,255,0.82);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

#lf2ChatApp .lf2-person {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
}

#lf2ChatApp .lf2-photo {
    position: relative;
    display: inline-flex;
    flex: 0 0 auto;
}

#lf2ChatApp .lf2-photo.online:after {
    content: "";
    position: absolute;
    right: 2px;
    bottom: 4px;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #38c86b;
    border: 3px solid #fff;
    box-shadow: 0 0 0 4px rgba(56, 200, 107, 0.12);
}

#lf2ChatApp .lf2-avatar-lg {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.98);
    box-shadow: 0 14px 30px rgba(0,0,0,0.11);
}

#lf2ChatApp .lf2-person h2 {
    margin: 0 0 6px;
    font-size: 25px;
    line-height: 1.05;
    color: #564046;
    font-weight: 900;
    letter-spacing: -0.55px;
}

#lf2ChatApp .lf2-person p {
    margin: 0;
    color: #96888e;
    font-size: 14px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 7px;
}

#lf2ChatApp .lf2-live-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #38c86b;
    display: inline-block;
    box-shadow: 0 0 0 4px rgba(56,200,107,0.13);
}

#lf2ChatApp .lf2-muted-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #b8bdc3;
    display: inline-block;
    box-shadow: 0 0 0 4px rgba(184,189,195,0.13);
}

#lf2ChatApp .lf2-verified {
    color: var(--lf2-pink);
    font-size: 16px;
    vertical-align: middle;
}

#lf2ChatApp .lf2-brand {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

#lf2ChatApp .lf2-logo-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 104px;
    height: 34px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(224, 220, 222, 0.95);
    color: #74676d;
    font-size: 12px;
    font-weight: 900;
    box-shadow: var(--lf2-shadow-soft);
}

#lf2ChatApp .lf2-logo-badge:before {
    content: "Love & Fire";
}

#lf2ChatApp .lf2-controls {
    display: inline-flex;
    align-items: center;
    gap: 18px;
}

#lf2ChatApp .lf2-controls a {
    color: #78676e;
    font-size: 14px;
    font-weight: 900;
    text-decoration: none;
    transition: all .2s ease;
}

#lf2ChatApp .lf2-controls a:hover {
    color: var(--lf2-pink);
    text-decoration: none;
}

#lf2ChatApp .lf2-header-dots {
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.90);
    color: #78676e;
    font-size: 23px;
    line-height: 1;
    cursor: pointer;
    box-shadow: 0 12px 24px rgba(127, 102, 110, 0.08);
    transition: all .2s ease;
}

#lf2ChatApp .lf2-messages {
    position: relative;
    flex: 1;
    min-height: 440px;
    padding: 32px 38px;
    overflow-y: auto;
    background:
        radial-gradient(circle at top center, rgba(255,255,255,0.98), rgba(255,255,255,0) 34%),
        linear-gradient(180deg, rgba(250,250,251,0.99), rgba(246,247,248,0.99));
    scroll-behavior: smooth;
}

#lf2ChatApp .lf2-date {
    display: flex;
    justify-content: center;
    margin: 4px 0 28px;
}

#lf2ChatApp .lf2-date span {
    padding: 9px 18px;
    border-radius: 999px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(229, 222, 224, 0.95);
    color: #9b8d93;
    font-size: 12px;
    font-weight: 900;
}

#lf2ChatApp .lf2-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin: 15px 0;
}

#lf2ChatApp .lf2-msg-row.mine {
    justify-content: flex-end;
}

#lf2ChatApp .lf2-msg-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex: 0 0 auto;
    border: 2px solid rgba(255,255,255,0.98);
    box-shadow: 0 9px 18px rgba(0,0,0,0.085);
}

#lf2ChatApp .lf2-bubble {
    position: relative;
    display: inline-block !important;
    flex: 0 1 auto;
    min-width: 96px;
    max-width: 66%;
    width: auto !important;
    padding: 15px 48px 12px 17px;
    border-radius: 24px 24px 24px 9px;
    background: linear-gradient(180deg, #ffffff 0%, #f4f6f8 100%) !important;
    border: 1px solid rgba(210, 216, 223, 0.95) !important;
    box-shadow:
        0 16px 34px rgba(83, 90, 102, 0.08),
        inset 0 1px 0 rgba(255,255,255,0.98) !important;
    color: #45484d !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
    white-space: normal !important;
    hyphens: none !important;
}

#lf2ChatApp .lf2-msg-row.mine .lf2-bubble {
    border-radius: 24px 24px 9px 24px;
    background: linear-gradient(180deg, #f9fafb 0%, #edf0f3 100%) !important;
}

#lf2ChatApp .lf2-msg-text {
    display: block;
    font-size: 15px;
    line-height: 1.48;
    color: #45484d !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
    white-space: pre-wrap !important;
    hyphens: none !important;
}

#lf2ChatApp .lf2-msg-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 8px;
    font-size: 11px;
    color: #8e949c !important;
    font-weight: 800;
    white-space: nowrap !important;
}

#lf2ChatApp .lf2-edited {
    color: #a9afb6;
}

#lf2ChatApp .lf2-deleted .lf2-msg-text {
    color: #8a9097 !important;
    font-style: italic;
}

#lf2ChatApp .lf2-menu-btn {
    position: absolute;
    top: 8px;
    right: 10px;
    width: 31px;
    height: 24px;
    border: 0;
    border-radius: 999px;
    background: rgba(255,255,255,0.96) !important;
    color: #7b8188 !important;
    font-size: 18px;
    font-weight: 900;
    line-height: 10px;
    cursor: pointer;
    box-shadow: 0 9px 18px rgba(111, 96, 101, 0.10);
}

#lf2ChatApp .lf2-actions {
    position: absolute;
    top: 39px;
    right: 8px;
    width: 212px;
    display: none;
    background: rgba(255,255,255,0.985);
    border: 1px solid rgba(224, 220, 222, 0.96);
    border-radius: 17px;
    box-shadow: 0 24px 46px rgba(111, 96, 101, 0.18);
    z-index: 30;
    overflow: hidden;
}

#lf2ChatApp .lf2-bubble.menu-open .lf2-actions {
    display: block;
}

#lf2ChatApp .lf2-action {
    width: 100%;
    border: 0;
    background: transparent;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 15px;
    text-align: left;
    color: #5e5559;
    font-weight: 900;
    font-size: 13px;
    cursor: pointer;
}

#lf2ChatApp .lf2-action.delete {
    color: #e64b67;
}

#lf2ChatApp .lf2-form {
    min-height: 96px;
    padding: 18px 26px;
    border-top: 1px solid var(--lf2-line);
    background: rgba(255,255,255,0.84);
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

#lf2ChatApp .lf2-tool {
    width: 42px;
    height: 42px;
    border: 0;
    border-radius: 50%;
    background: linear-gradient(180deg, #ffffff, #f5f6f8);
    color: #776c71;
    font-size: 16px;
    cursor: pointer;
}

#lf2ChatApp .lf2-input-wrap {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

#lf2ChatApp .lf2-edit-state {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 7px 14px;
    border-radius: 999px;
    background: rgba(255,248,241,0.96);
    border: 1px solid rgba(232, 176, 125, 0.26);
    color: #9b5d1c;
    font-size: 12px;
    font-weight: 900;
}

#lf2ChatApp .lf2-edit-state[hidden] {
    display: none !important;
}

#lf2ChatApp .lf2-edit-state button {
    border: 0;
    background: transparent;
    color: var(--lf2-pink);
    font-weight: 900;
    cursor: pointer;
}

#lf2ChatApp .lf2-input-wrap input {
    width: 100%;
    height: 56px;
    border-radius: 999px;
    border: 1px solid rgba(222, 218, 220, 0.98);
    background: linear-gradient(180deg, #ffffff, #fbfbfc);
    padding: 0 21px;
    outline: none;
    color: #4e474b;
    font-size: 14px;
}

#lf2ChatApp .lf2-send {
    width: 56px;
    height: 56px;
    flex: 0 0 56px;
    border: 0;
    border-radius: 50%;
    background: linear-gradient(180deg, #151515, #000000);
    color: #ffffff;
    font-size: 25px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 20px 34px rgba(0,0,0,0.22);
}

#lf2ChatApp .lf2-send:disabled {
    opacity: .58;
    cursor: not-allowed;
}

#lf2ChatApp .lf2-alert {
    position: absolute;
    right: 24px;
    bottom: 106px;
    z-index: 50;
    padding: 13px 16px;
    border-radius: 17px;
    background: rgba(255,255,255,0.99);
    color: #e64b67;
    font-weight: 900;
    font-size: 13px;
    border: 1px solid rgba(230, 75, 103, 0.16);
    box-shadow: 0 22px 46px rgba(111, 96, 101, 0.18);
    display: none;
    max-width: 520px;
}

@media (max-width: 1180px) {
    #lf2ChatApp .lf2-shell {
        flex-direction: column;
        min-height: auto;
    }

    #lf2ChatApp .lf2-sidebar {
        width: 100%;
        flex-basis: auto;
        border-right: 0;
        border-bottom: 1px solid var(--lf2-line);
    }
}

@media (max-width: 900px) {
    #lf2ChatApp .lf2-title {
        padding: 18px 16px;
        flex-direction: column;
        align-items: flex-start;
    }

    #lf2ChatApp .lf2-messages {
        padding: 20px 14px;
    }

    #lf2ChatApp .lf2-bubble {
        min-width: 92px;
        max-width: calc(100% - 58px);
    }

    #lf2ChatApp .lf2-tool {
        display: none;
    }
}
</style>

<section id="lf2ChatApp">
    <div class="lf2-shell">
        <aside class="lf2-sidebar">
            <div class="lf2-sidebar-head">
                <span class="lf2-pill">Mensagens</span>
                <h1>Conversas</h1>
            </div>

            <?php if (count($conversations) === 0) { ?>
                <div class="lf2-empty">Nenhuma conversa ainda.</div>
            <?php } ?>

            <?php $i = 0; ?>
            <?php foreach ($conversations as $conv) { ?>
                <?php
                $p = lf_renew_partner($conv, $user['id']);

                $last = lf_renew_fetch_one_direct(
                    'SELECT body, is_deleted, created_at
                     FROM messages
                     WHERE conversation_id = ' . intval($conv['id']) . '
                     ORDER BY id DESC
                     LIMIT 1'
                );

                $preview = 'Conversa iniciada';

                if ($last) {
                    if (isset($last['is_deleted']) && intval($last['is_deleted']) === 1) {
                        $preview = 'Mensagem excluida.';
                    } else {
                        $preview = isset($last['body']) ? lf_renew_excerpt($last['body'], 42) : 'Conversa iniciada';
                    }
                }

                $conversationType = isset($conv['type']) ? $conv['type'] : 'dm';
                $conversationTypeLabel = $conversationType === 'match' ? 'Match' : 'DM livre';
                ?>

                <a class="lf2-conversation <?php if ($conversationId == intval($conv['id'])) echo 'active'; ?>" href="chat.php?conversation_id=<?php echo intval($conv['id']); ?>">
                    <img class="lf2-avatar" src="<?php echo $p ? lf_renew_h(lf_renew_photo($p, $i)) : 'assets/img/avatar-placeholder.svg'; ?>" alt="<?php echo $p && isset($p['name']) ? lf_renew_h($p['name']) : 'Usuario'; ?>">

                    <div class="lf2-conversation-info">
                        <strong><?php echo $p && isset($p['name']) ? lf_renew_h(lf_renew_first_name($p['name'])) : 'Usuario'; ?></strong>
                        <span class="lf2-type <?php echo lf_renew_h($conversationType); ?>">
                            <?php echo lf_renew_h($conversationTypeLabel); ?>
                        </span>
                        <p><?php echo lf_renew_h($preview); ?></p>
                    </div>
                </a>

                <?php $i++; ?>
            <?php } ?>
        </aside>

        <section class="lf2-window">
            <div id="lf2ChatAlert" class="lf2-alert"></div>

            <?php if (!$activeConversation || !$partner) { ?>
                <div class="lf2-title">
                    <div class="lf2-person">
                        <div class="lf2-photo">
                            <img class="lf2-avatar-lg" src="assets/img/avatar-placeholder.svg" alt="Mensagens">
                        </div>

                        <div>
                            <h2>Mensagens</h2>
                            <p><span class="lf2-muted-dot"></span>Selecione uma conversa</p>
                        </div>
                    </div>

                    <div class="lf2-brand">
                        <span class="lf2-logo-badge"></span>
                    </div>
                </div>

                <div class="lf2-messages">
                    <div class="lf2-empty lf2-empty-main">
                        Selecione uma conversa ao lado para abrir o chat.
                    </div>
                </div>

                <form class="lf2-form" onsubmit="return false;">
                    <button class="lf2-tool" type="button" disabled>&#128247;</button>
                    <button class="lf2-tool" type="button" disabled>&#127908;</button>
                    <button class="lf2-tool" type="button" disabled>&#9786;</button>

                    <div class="lf2-input-wrap">
                        <input type="text" placeholder="Selecione uma conversa..." disabled>
                    </div>

                    <button class="lf2-send" type="submit" disabled>&#8593;</button>
                </form>
            <?php } else { ?>
                <?php
                $partnerAvatar = lf_renew_photo($partner, 1);
                $userAvatar = lf_renew_photo($user, 0);
                $partnerName = isset($partner['name']) ? $partner['name'] : 'Usuario';
                $partnerOnline = lf_renew_online($partner);
                ?>

                <div class="lf2-title">
                    <div class="lf2-person">
                        <div class="lf2-photo <?php echo $partnerOnline ? 'online' : ''; ?>">
                            <img class="lf2-avatar-lg" src="<?php echo lf_renew_h($partnerAvatar); ?>" alt="<?php echo lf_renew_h($partnerName); ?>">
                        </div>

                        <div>
                            <h2>
                                <?php echo lf_renew_h(lf_renew_first_name($partnerName)); ?>
                                <?php if (!empty($partner['is_verified'])) { ?>
                                    <span class="lf2-verified">&#10003;</span>
                                <?php } ?>
                            </h2>

                            <p>
                                <?php if ($partnerOnline) { ?>
                                    <span class="lf2-live-dot"></span>Online agora
                                <?php } else { ?>
                                    <span class="lf2-muted-dot"></span>Visto recentemente
                                <?php } ?>
                            </p>
                        </div>
                    </div>

                    <div class="lf2-brand">
                        <span class="lf2-logo-badge"></span>

                        <div class="lf2-controls">
                            <a href="api/block_user.php?target_id=<?php echo intval($partner['id']); ?>">Bloquear</a>
                            <a href="api/report_user.php?target_id=<?php echo intval($partner['id']); ?>">Denunciar</a>
                            <button class="lf2-header-dots" type="button">⋮</button>
                        </div>
                    </div>
                </div>

                <div
                    id="lf2Messages"
                    class="lf2-messages"
                    data-conversation-id="<?php echo intval($activeConversation['id']); ?>"
                    data-current-user-id="<?php echo intval($user['id']); ?>"
                    data-current-avatar="<?php echo lf_renew_h($userAvatar); ?>"
                    data-partner-avatar="<?php echo lf_renew_h($partnerAvatar); ?>"
                ></div>

                <form id="lf2MessageForm" class="lf2-form" data-conversation-id="<?php echo intval($activeConversation['id']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo lf_renew_h(lf_renew_csrf()); ?>">
                    <input type="hidden" name="conversation_id" value="<?php echo intval($activeConversation['id']); ?>">
                    <input type="hidden" name="message_id" value="">

                    <button class="lf2-tool" type="button">&#128247;</button>
                    <button class="lf2-tool" type="button">&#127908;</button>
                    <button class="lf2-tool" type="button">&#9786;</button>

                    <div class="lf2-input-wrap">
                        <div id="lf2EditState" class="lf2-edit-state" hidden>
                            <span>Editando mensagem</span>
                            <button id="lf2CancelEdit" type="button">Cancelar</button>
                        </div>

                        <input type="text" name="body" maxlength="1000" placeholder="Digite uma mensagem..." autocomplete="off">
                    </div>

                    <button id="lf2SendBtn" class="lf2-send" type="submit">&#8593;</button>
                </form>
            <?php } ?>
        </section>
    </div>
</section>

<script>
(function () {
    var messagesBox = document.getElementById('lf2Messages');
    var form = document.getElementById('lf2MessageForm');

    if (!messagesBox || !form) {
        return;
    }

    var conversationId = messagesBox.getAttribute('data-conversation-id');
    var currentUserId = parseInt(messagesBox.getAttribute('data-current-user-id'), 10);
    var currentAvatar = messagesBox.getAttribute('data-current-avatar');
    var partnerAvatar = messagesBox.getAttribute('data-partner-avatar');

    var input = form.querySelector('input[name="body"]');
    var messageIdInput = form.querySelector('input[name="message_id"]');
    var csrfInput = form.querySelector('input[name="csrf_token"]');
    var editState = document.getElementById('lf2EditState');
    var cancelEditBtn = document.getElementById('lf2CancelEdit');
    var sendBtn = document.getElementById('lf2SendBtn');
    var alertBox = document.getElementById('lf2ChatAlert');

    var lastHtml = '';
    var loadingMessages = false;
    var firstLoad = true;

    function trimText(text) {
        return String(text || '').replace(/^\s+|\s+$/g, '');
    }

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') {
            return '';
        }

        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function encodeForm(data) {
        var pairs = [];
        var key;

        for (key in data) {
            if (data.hasOwnProperty(key)) {
                pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
        }

        return pairs.join('&');
    }

    function parseJsonClean(text) {
        var clean = String(text || '').replace(/^\uFEFF/, '');
        var firstBrace;
        var lastBrace;
        var sliced;

        clean = trimText(clean);

        try {
            return JSON.parse(clean);
        } catch (e1) {
            firstBrace = clean.indexOf('{');
            lastBrace = clean.lastIndexOf('}');

            if (firstBrace >= 0 && lastBrace > firstBrace) {
                sliced = clean.substring(firstBrace, lastBrace + 1);

                try {
                    return JSON.parse(sliced);
                } catch (e2) {}
            }

            return {
                ok: false,
                invalid_json: true,
                error: ''
            };
        }
    }

    function ajax(method, url, data, callback) {
        var xhr = new XMLHttpRequest();
        var payload = data ? encodeForm(data) : null;

        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                callback(parseJsonClean(xhr.responseText), xhr.responseText);
            }
        };

        xhr.onerror = function () {
            callback({
                ok: false,
                error: 'Erro de conexao com o servidor.'
            }, '');
        };

        xhr.send(payload);
    }

    function showAlert(text) {
        if (!text) {
            return;
        }

        if (!alertBox) {
            alert(text);
            return;
        }

        alertBox.innerHTML = escapeHtml(text);
        alertBox.style.display = 'block';

        setTimeout(function () {
            alertBox.style.display = 'none';
        }, 5200);
    }

    function formatTime(value) {
        if (!value) {
            return '';
        }

        var parts = String(value).split(' ');

        if (parts.length >= 2) {
            return parts[1].substring(0, 5);
        }

        return value;
    }

    function formatDateLabel(value) {
        if (!value) {
            return '';
        }

        var datePart = String(value).split(' ')[0];
        var pieces = datePart.split('-');

        if (pieces.length !== 3) {
            return datePart;
        }

        return pieces[2] + '/' + pieces[1] + '/' + pieces[0];
    }

    function isEdited(message) {
        if (!message.edited_at) {
            return false;
        }

        if (parseInt(message.is_deleted, 10) === 1) {
            return false;
        }

        return true;
    }

    function hasClass(el, className) {
        if (!el || !el.className) {
            return false;
        }

        return (' ' + el.className + ' ').indexOf(' ' + className + ' ') > -1;
    }

    function addClass(el, className) {
        if (!hasClass(el, className)) {
            el.className += ' ' + className;
        }
    }

    function removeClass(el, className) {
        if (!el || !el.className) {
            return;
        }

        el.className = (' ' + el.className + ' ').replace(' ' + className + ' ', ' ');
        el.className = trimText(el.className);
    }

    function closeAllMenus() {
        var opened = messagesBox.querySelectorAll('.lf2-bubble.menu-open');
        var i;

        for (i = 0; i < opened.length; i++) {
            removeClass(opened[i], 'menu-open');
        }
    }

    function buildMessageHtml(message) {
        var mine = parseInt(message.sender_id, 10) === currentUserId;
        var deleted = parseInt(message.is_deleted, 10) === 1;
        var avatar = mine ? currentAvatar : partnerAvatar;
        var rowClass = mine ? 'lf2-msg-row mine' : 'lf2-msg-row';
        var text = deleted ? 'Mensagem excluida.' : message.body;
        var deletedClass = deleted ? ' lf2-deleted' : '';
        var editedText = isEdited(message) ? '<span class="lf2-edited">editada</span>' : '';
        var html = '';

        html += '<div class="' + rowClass + '" data-message-id="' + parseInt(message.id, 10) + '">';

        if (!mine) {
            html += '<img class="lf2-msg-avatar" src="' + escapeHtml(avatar) + '" alt="Avatar">';
        }

        html += '<div class="lf2-bubble' + deletedClass + '">';
        html += '<span class="lf2-msg-text">' + escapeHtml(text) + '</span>';
        html += '<small class="lf2-msg-meta">';
        html += '<span>' + escapeHtml(formatTime(message.created_at)) + '</span>';
        html += editedText;
        html += '</small>';

        if (mine && !deleted) {
            html += '<button class="lf2-menu-btn" type="button" data-action="toggle-menu">...</button>';
            html += '<div class="lf2-actions">';
            html += '<button class="lf2-action" type="button" data-action="edit" data-id="' + parseInt(message.id, 10) + '" data-body="' + escapeHtml(message.body) + '">';
            html += '<span>&#9998;</span> Editar mensagem';
            html += '</button>';
            html += '<button class="lf2-action delete" type="button" data-action="delete" data-id="' + parseInt(message.id, 10) + '">';
            html += '<span>&#128465;</span> Excluir mensagem';
            html += '</button>';
            html += '</div>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    }

    function buildMessages(messages) {
        var html = '';
        var lastDate = '';
        var i;
        var msg;
        var dateLabel;

        if (!messages || !messages.length) {
            return '<div class="lf2-empty lf2-empty-main">Nenhuma mensagem ainda. Comece a conversa agora.</div>';
        }

        for (i = 0; i < messages.length; i++) {
            msg = messages[i];
            dateLabel = formatDateLabel(msg.created_at);

            if (dateLabel && dateLabel !== lastDate) {
                html += '<div class="lf2-date"><span>' + escapeHtml(dateLabel) + '</span></div>';
                lastDate = dateLabel;
            }

            html += buildMessageHtml(msg);
        }

        return html;
    }

    function shouldStickToBottom() {
        return messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 130;
    }

    function scrollBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }

    function fetchMessages(forceBottom) {
        if (loadingMessages) {
            return;
        }

        loadingMessages = true;

        ajax(
            'GET',
            'chat.php?conversation_id=' + encodeURIComponent(conversationId) + '&renew_ajax=1&renew_chat_action=fetch&_=' + new Date().getTime(),
            null,
            function (data) {
                var stayBottom;
                var html;

                loadingMessages = false;

                if (!data || !data.ok) {
                    return;
                }

                stayBottom = forceBottom || firstLoad || shouldStickToBottom();
                html = buildMessages(data.messages);

                if (html !== lastHtml) {
                    messagesBox.innerHTML = html;
                    lastHtml = html;

                    if (stayBottom) {
                        scrollBottom();
                    }
                }

                firstLoad = false;
            }
        );
    }

    function postChat(action, extraData, callback) {
        var data = {
            renew_ajax: '1',
            renew_chat_action: action,
            conversation_id: conversationId,
            csrf_token: csrfInput ? csrfInput.value : ''
        };

        var key;

        for (key in extraData) {
            if (extraData.hasOwnProperty(key)) {
                data[key] = extraData[key];
            }
        }

        ajax(
            'POST',
            'chat.php?conversation_id=' + encodeURIComponent(conversationId),
            data,
            callback
        );
    }

    function resetEdit() {
        messageIdInput.value = '';

        if (editState) {
            editState.hidden = true;
        }

        input.value = '';
        input.focus();
    }

    function startEdit(id, body) {
        messageIdInput.value = id;
        input.value = body;

        if (editState) {
            editState.hidden = false;
        }

        input.focus();
    }

    form.onsubmit = function (event) {
        if (event && event.preventDefault) {
            event.preventDefault();
        }

        var body = trimText(input.value);
        var editingId = messageIdInput.value;
        var action = editingId ? 'edit' : 'send';

        if (body === '') {
            showAlert('Digite uma mensagem.');
            input.focus();
            return false;
        }

        sendBtn.disabled = true;

        postChat(action, {
            body: body,
            message_id: editingId
        }, function (data) {
            sendBtn.disabled = false;

            if (!data || !data.ok) {
                if (data && data.invalid_json) {
                    resetEdit();
                    fetchMessages(true);
                    return;
                }

                showAlert((data && data.error ? data.error : 'Nao foi possivel salvar a mensagem.') + (data && data.debug ? ' | SQL: ' + data.debug : ''));
                return;
            }

            resetEdit();
            fetchMessages(true);
        });

        return false;
    };

    messagesBox.onclick = function (event) {
        event = event || window.event;

        var target = event.target || event.srcElement;
        var action;
        var bubble;
        var editId;
        var editBody;
        var deleteId;

        while (target && target !== messagesBox) {
            if (target.getAttribute && target.getAttribute('data-action')) {
                break;
            }

            target = target.parentNode;
        }

        if (!target || target === messagesBox) {
            closeAllMenus();
            return;
        }

        action = target.getAttribute('data-action');

        if (action === 'toggle-menu') {
            bubble = target.parentNode;

            if (hasClass(bubble, 'menu-open')) {
                removeClass(bubble, 'menu-open');
            } else {
                closeAllMenus();
                addClass(bubble, 'menu-open');
            }

            return;
        }

        if (action === 'edit') {
            editId = target.getAttribute('data-id');
            editBody = target.getAttribute('data-body');

            startEdit(editId, editBody);
            closeAllMenus();

            return;
        }

        if (action === 'delete') {
            deleteId = target.getAttribute('data-id');

            if (!confirm('Excluir esta mensagem?')) {
                closeAllMenus();
                return;
            }

            closeAllMenus();

            postChat('delete', {
                message_id: deleteId
            }, function (data) {
                if (!data || !data.ok) {
                    if (data && data.invalid_json) {
                        fetchMessages(true);
                        return;
                    }

                    showAlert((data && data.error ? data.error : 'Nao foi possivel excluir a mensagem.') + (data && data.debug ? ' | SQL: ' + data.debug : ''));
                    return;
                }

                if (messageIdInput.value === deleteId) {
                    resetEdit();
                }

                fetchMessages(true);
            });
        }
    };

    if (cancelEditBtn) {
        cancelEditBtn.onclick = function () {
            resetEdit();
        };
    }

    fetchMessages(true);

    setInterval(function () {
        fetchMessages(false);
    }, 3500);
})();
</script>

<?php
if (function_exists('render_footer')) {
    render_footer();
}
?>