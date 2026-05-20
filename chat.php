<?php
/*
|--------------------------------------------------------------------------
| LOVE & FIRE - chat.php
| Renew Chat 1.3
|--------------------------------------------------------------------------
| - Desktop script / Mobile script com autodetect ao abrir o site
| - AJAX Direct SQL, baseado na versão que funcionou
| - Envio de texto em tempo real
| - Envio de foto em tempo real usando attachment_path
| - Typing message apenas para o outro usuário
| - Wallet BIX reservado / Flechas do Cupido reservado
| - Design premium 2026 inspirado no mockup aprovado
| - Classes isoladas lf2-
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

function lf_renew_is_utf8($value) {
    if (!is_string($value)) {
        return true;
    }

    if (function_exists('mb_check_encoding')) {
        return mb_check_encoding($value, 'UTF-8');
    }

    return preg_match('//u', $value) === 1;
}

function lf_renew_utf8_string($value) {
    if (!is_string($value)) {
        $value = strval($value);
    }

    $value = str_replace("\x00", '', $value);

    if (lf_renew_is_utf8($value)) {
        return $value;
    }

    if (function_exists('iconv')) {
        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);

        if ($converted !== false && $converted !== '') {
            return $converted;
        }

        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $value);

        if ($converted !== false && $converted !== '') {
            return $converted;
        }
    }

    if (function_exists('utf8_encode')) {
        return utf8_encode($value);
    }

    return preg_replace('/[^\x20-\x7E]/', '', $value);
}

function lf_renew_utf8_safe($value) {
    if (is_array($value)) {
        $clean = array();

        foreach ($value as $key => $item) {
            $clean[$key] = lf_renew_utf8_safe($item);
        }

        return $clean;
    }

    if (is_string($value)) {
        return lf_renew_utf8_string($value);
    }

    return $value;
}

function lf_renew_json($data) {
    $GLOBALS['LF_RENEW_JSON_SENT'] = true;

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    $data = lf_renew_utf8_safe($data);

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    $jsonOptions = 0;

    if (defined('JSON_UNESCAPED_UNICODE')) {
        $jsonOptions = $jsonOptions | JSON_UNESCAPED_UNICODE;
    }

    if (defined('JSON_PARTIAL_OUTPUT_ON_ERROR')) {
        $jsonOptions = $jsonOptions | JSON_PARTIAL_OUTPUT_ON_ERROR;
    }

    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $jsonOptions = $jsonOptions | JSON_INVALID_UTF8_SUBSTITUTE;
    }

    if ($jsonOptions > 0) {
        $json = @json_encode($data, $jsonOptions);
    } else {
        $json = @json_encode($data);
    }

    if ($json === false || $json === null || $json === '') {
        $json = @json_encode(array(
            'ok' => false,
            'error' => 'Erro ao gerar JSON do chat.'
        ));
    }

    echo $json;
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
    $value = lf_renew_utf8_string($value);

    if (function_exists('mysql_real_escape_string')) {
        return mysql_real_escape_string($value);
    }

    if (function_exists('db_escape')) {
        return db_escape($value);
    }

    return addslashes($value);
}

function lf_renew_h($value) {
    $value = lf_renew_utf8_string($value);

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
    $text = trim(lf_renew_utf8_string($text));

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
    $text = trim(lf_renew_utf8_string($text));

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

    if (!lf_renew_has_col('attachment_path')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN attachment_path VARCHAR(255) NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('is_read')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (!lf_renew_has_col('is_deleted')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (!lf_renew_has_col('updated_at')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN updated_at DATETIME NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('edited_at')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN edited_at DATETIME NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('deleted_at')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('is_edited')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN is_edited TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (!lf_renew_has_col('deleted_by')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN deleted_by INT NULL DEFAULT NULL');
    }

    if (!lf_renew_has_col('is_reported')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN is_reported TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (!lf_renew_has_col('status')) {
        @lf_renew_query_direct('ALTER TABLE messages ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT \'active\'');
    }

    lf_renew_load_message_columns();
}

function lf_renew_prepare_typing_table() {
    @lf_renew_query_direct(
        'CREATE TABLE IF NOT EXISTS chat_typing (
            id INT NOT NULL AUTO_INCREMENT,
            conversation_id INT NOT NULL,
            user_id INT NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY conversation_user (conversation_id, user_id),
            KEY conversation_updated (conversation_id, updated_at)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8'
    );
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
    $name = trim(lf_renew_utf8_string($name));

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
        'body' => isset($row['body']) ? lf_renew_utf8_string($row['body']) : '',
        'attachment_path' => isset($row['attachment_path']) ? lf_renew_utf8_string($row['attachment_path']) : '',
        'is_deleted' => isset($row['is_deleted']) ? intval($row['is_deleted']) : 0,
        'created_at' => isset($row['created_at']) ? lf_renew_utf8_string($row['created_at']) : '',
        'updated_at' => isset($row['updated_at']) ? lf_renew_utf8_string($row['updated_at']) : '',
        'edited_at' => isset($row['edited_at']) ? lf_renew_utf8_string($row['edited_at']) : ''
    );
}

function lf_renew_insert_message($conversationId, $senderId, $body, $attachmentPath) {
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

        if ($attachmentPath !== '') {
            $values[] = '\'' . lf_renew_escape($attachmentPath) . '\'';
        } else {
            $values[] = 'NULL';
        }
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

function lf_renew_upload_picture() {
    if (!isset($_FILES['picture']) || !is_array($_FILES['picture'])) {
        return array(
            'ok' => false,
            'error' => 'Nenhuma imagem recebida.'
        );
    }

    if (!isset($_FILES['picture']['error']) || intval($_FILES['picture']['error']) !== 0) {
        return array(
            'ok' => false,
            'error' => 'Erro no upload da imagem.'
        );
    }

    if (!isset($_FILES['picture']['tmp_name']) || !is_uploaded_file($_FILES['picture']['tmp_name'])) {
        return array(
            'ok' => false,
            'error' => 'Arquivo temporario invalido.'
        );
    }

    $maxSize = 6 * 1024 * 1024;

    if (isset($_FILES['picture']['size']) && intval($_FILES['picture']['size']) > $maxSize) {
        return array(
            'ok' => false,
            'error' => 'Imagem muito grande. Envie uma imagem de ate 6MB.'
        );
    }

    $imageInfo = @getimagesize($_FILES['picture']['tmp_name']);

    if (!$imageInfo || !isset($imageInfo[2])) {
        return array(
            'ok' => false,
            'error' => 'Envie apenas imagem valida.'
        );
    }

    $type = intval($imageInfo[2]);
    $ext = '';

    if ($type === IMAGETYPE_JPEG) {
        $ext = 'jpg';
    } elseif ($type === IMAGETYPE_PNG) {
        $ext = 'png';
    } elseif ($type === IMAGETYPE_GIF) {
        $ext = 'gif';
    } elseif (defined('IMAGETYPE_WEBP') && $type === IMAGETYPE_WEBP) {
        $ext = 'webp';
    }

    if ($ext === '') {
        return array(
            'ok' => false,
            'error' => 'Formato nao permitido. Use JPG, PNG, GIF ou WEBP.'
        );
    }

    $baseDir = dirname(__FILE__) . '/uploads/chat';
    $publicDir = 'uploads/chat';

    if (!is_dir($baseDir)) {
        @mkdir($baseDir, 0775, true);
    }

    if (!is_dir($baseDir)) {
        return array(
            'ok' => false,
            'error' => 'Nao foi possivel criar a pasta uploads/chat.'
        );
    }

    $name = 'lf_chat_' . date('Ymd_His') . '_' . md5(uniqid(mt_rand(), true)) . '.' . $ext;
    $dest = $baseDir . '/' . $name;
    $publicPath = $publicDir . '/' . $name;

    if (!@move_uploaded_file($_FILES['picture']['tmp_name'], $dest)) {
        return array(
            'ok' => false,
            'error' => 'Nao foi possivel salvar a imagem.'
        );
    }

    return array(
        'ok' => true,
        'path' => $publicPath
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
lf_renew_prepare_typing_table();

$action = lf_renew_action();

if ($action === 'fetch') {
    $conversationIdFetch = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdFetch, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false, 'error' => 'Conversa nao encontrada.'));
    }

    $statusWhere = '';

    if (lf_renew_has_col('status')) {
        $statusWhere = ' AND status = \'active\' ';
    }

    $rows = lf_renew_fetch_all_direct(
        'SELECT *
         FROM messages
         WHERE conversation_id = ' . intval($conversationIdFetch) . '
         ' . $statusWhere . '
         ORDER BY id ASC
         LIMIT 500'
    );

    $messages = array();

    foreach ($rows as $row) {
        $messages[] = lf_renew_format_message($row);
    }

    lf_renew_json(array('ok' => true, 'messages' => $messages));
}

if ($action === 'typing_ping') {
    $conversationIdTyping = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdTyping, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false));
    }

    lf_renew_query_direct(
        'INSERT INTO chat_typing (conversation_id, user_id, updated_at)
         VALUES (' . intval($conversationIdTyping) . ', ' . intval($user['id']) . ', NOW())
         ON DUPLICATE KEY UPDATE updated_at = NOW()'
    );

    lf_renew_json(array('ok' => true));
}

if ($action === 'typing_stop') {
    $conversationIdTyping = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdTyping, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false));
    }

    lf_renew_query_direct(
        'DELETE FROM chat_typing
         WHERE conversation_id = ' . intval($conversationIdTyping) . '
         AND user_id = ' . intval($user['id']) . '
         LIMIT 1'
    );

    lf_renew_json(array('ok' => true));
}

if ($action === 'typing_status') {
    $conversationIdTyping = lf_renew_int('conversation_id', 0);
    $conversation = lf_renew_conversation($conversationIdTyping, $user['id']);

    if (!$conversation) {
        lf_renew_json(array('ok' => false, 'typing' => false));
    }

    lf_renew_query_direct(
        'DELETE FROM chat_typing
         WHERE updated_at < DATE_SUB(NOW(), INTERVAL 10 SECOND)'
    );

    $typing = lf_renew_fetch_one_direct(
        'SELECT *
         FROM chat_typing
         WHERE conversation_id = ' . intval($conversationIdTyping) . '
         AND user_id <> ' . intval($user['id']) . '
         AND updated_at >= DATE_SUB(NOW(), INTERVAL 5 SECOND)
         LIMIT 1'
    );

    $partnerTyping = lf_renew_partner($conversation, $user['id']);
    $typingName = $partnerTyping && isset($partnerTyping['name']) ? lf_renew_first_name($partnerTyping['name']) : 'Alguem';

    lf_renew_json(array(
        'ok' => true,
        'typing' => $typing ? true : false,
        'name' => $typingName
    ));
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

        $insert = lf_renew_insert_message($conversationIdPost, $user['id'], $body, '');

        if (!$insert) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'Nao foi possivel enviar a mensagem.',
                'debug' => $GLOBALS['LF_RENEW_LAST_SQL_ERROR']
            ));
        }

        lf_renew_query_direct(
            'DELETE FROM chat_typing
             WHERE conversation_id = ' . intval($conversationIdPost) . '
             AND user_id = ' . intval($user['id']) . '
             LIMIT 1'
        );

        lf_renew_update_conversation($conversationIdPost);

        lf_renew_json(array('ok' => true, 'message' => 'Mensagem enviada.'));
    }

    if ($action === 'send_picture') {
        if (!lf_renew_has_col('attachment_path')) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'A coluna attachment_path nao existe na tabela messages.'
            ));
        }

        $upload = lf_renew_upload_picture();

        if (!$upload['ok']) {
            lf_renew_json($upload);
        }

        $caption = isset($_POST['body']) ? lf_renew_limit_text($_POST['body'], 1000) : '';

        $insert = lf_renew_insert_message($conversationIdPost, $user['id'], $caption, $upload['path']);

        if (!$insert) {
            lf_renew_json(array(
                'ok' => false,
                'error' => 'Nao foi possivel enviar a imagem.',
                'debug' => $GLOBALS['LF_RENEW_LAST_SQL_ERROR']
            ));
        }

        lf_renew_update_conversation($conversationIdPost);

        lf_renew_json(array('ok' => true, 'message' => 'Imagem enviada.', 'path' => $upload['path']));
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

        if (lf_renew_has_col('attachment_path')) {
            $set[] = 'attachment_path = NULL';
        }

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

$partnerAvatar = '';
$userAvatar = lf_renew_photo($user, 0);
$partnerName = 'Usuario';
$partnerOnline = false;

if ($activeConversation && $partner) {
    $partnerAvatar = lf_renew_photo($partner, 1);
    $partnerName = isset($partner['name']) ? $partner['name'] : 'Usuario';
    $partnerOnline = lf_renew_online($partner);
}
?>

<style>
#lf2ChatApp,
#lf2ChatApp * {
    box-sizing: border-box;
}

#lf2ChatApp {
    --lf2-pink: #f2365f;
    --lf2-coral: #ff6b7a;
    --lf2-hot: #ff315f;
    --lf2-text: #33242b;
    --lf2-muted: #94888d;
    --lf2-soft: #f7f8fa;
    --lf2-line: rgba(220, 218, 220, 0.95);
    --lf2-white: #ffffff;
    --lf2-shadow: 0 30px 80px rgba(98, 84, 90, 0.13);
    --lf2-shadow-soft: 0 12px 30px rgba(98, 84, 90, 0.07);
    width: 100%;
    padding: 0 8px 22px;
}

#lf2ChatApp .lf2-desktop-view,
#lf2ChatApp .lf2-mobile-view {
    width: 100%;
}

#lf2ChatApp .lf2-mobile-view {
    display: none;
}

#lf2ChatApp .lf2-shell {
    display: flex;
    min-height: 690px;
    border-radius: 34px;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(255,255,255,0.97), rgba(248,248,249,0.99));
    border: 1px solid var(--lf2-line);
    box-shadow:
        0 34px 80px rgba(127, 102, 110, 0.12),
        0 10px 26px rgba(127, 102, 110, 0.05),
        inset 0 1px 0 rgba(255,255,255,0.95);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
}

#lf2ChatApp .lf2-sidebar {
    width: 365px;
    flex: 0 0 365px;
    padding: 24px 22px;
    background:
        radial-gradient(circle at 20% 0%, rgba(255,255,255,0.98), rgba(255,255,255,0) 36%),
        linear-gradient(180deg, rgba(255,255,255,0.93), rgba(248,248,249,0.98));
    border-right: 1px solid var(--lf2-line);
}

#lf2ChatApp .lf2-sidebar-head {
    padding: 0 4px 18px;
}

#lf2ChatApp .lf2-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 17px;
    border-radius: 999px;
    background: rgba(255,255,255,0.95);
    border: 1px solid rgba(255, 65, 103, 0.20);
    color: var(--lf2-hot);
    font-size: 13px;
    font-weight: 900;
    box-shadow: var(--lf2-shadow-soft);
}

#lf2ChatApp .lf2-sidebar-head h1 {
    margin: 18px 0 0;
    font-size: 32px;
    line-height: 1.05;
    color: var(--lf2-pink);
    font-weight: 900;
    letter-spacing: -0.8px;
}

#lf2ChatApp .lf2-search-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 8px 0 18px;
}

#lf2ChatApp .lf2-search {
    height: 50px;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 0 16px;
    border-radius: 999px;
    background: rgba(255,255,255,0.88);
    border: 1px solid rgba(222,218,220,0.92);
    color: #9b9297;
    font-weight: 800;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.98);
}

#lf2ChatApp .lf2-filter-btn,
#lf2ChatApp .lf2-round-btn {
    width: 48px;
    height: 48px;
    border: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.96);
    color: #746970;
    font-size: 18px;
    box-shadow: var(--lf2-shadow-soft);
    cursor: pointer;
}

#lf2ChatApp .lf2-tabs {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 10px 0 18px;
    border-bottom: 1px solid rgba(224,220,222,0.80);
}

#lf2ChatApp .lf2-tab {
    position: relative;
    padding: 12px 6px 14px;
    font-size: 13px;
    font-weight: 900;
    color: #7a6c73;
}

#lf2ChatApp .lf2-tab.active {
    color: var(--lf2-hot);
}

#lf2ChatApp .lf2-tab.active:after {
    content: "";
    position: absolute;
    left: 4px;
    right: 4px;
    bottom: -1px;
    height: 2px;
    border-radius: 2px;
    background: var(--lf2-hot);
}

#lf2ChatApp .lf2-badge {
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--lf2-hot);
    color: #fff;
    font-size: 11px;
    font-weight: 900;
}

#lf2ChatApp .lf2-wallet-card {
    margin: 0 0 18px;
    padding: 15px 16px;
    border-radius: 24px;
    background:
        radial-gradient(circle at 100% 0%, rgba(255,49,95,0.12), rgba(255,255,255,0) 45%),
        linear-gradient(135deg, rgba(255,255,255,0.96), rgba(248,248,249,0.98));
    border: 1px solid rgba(255,49,95,0.13);
    box-shadow: 0 16px 34px rgba(111,96,101,0.07);
}

#lf2ChatApp .lf2-wallet-card strong {
    display: block;
    font-size: 14px;
    color: #4d4047;
    font-weight: 950;
}

#lf2ChatApp .lf2-wallet-card span {
    display: block;
    margin-top: 5px;
    color: #91868d;
    font-size: 12px;
    font-weight: 800;
}

#lf2ChatApp .lf2-wallet-mini {
    margin-top: 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 9px;
}

#lf2ChatApp .lf2-wallet-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,49,95,0.08);
    color: var(--lf2-hot);
    font-size: 12px;
    font-weight: 950;
}

#lf2ChatApp .lf2-conversation {
    position: relative;
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 10px 0;
    padding: 14px;
    min-height: 82px;
    border-radius: 28px;
    text-decoration: none !important;
    background: rgba(255,255,255,0.66);
    border: 1px solid rgba(229,222,224,0.94);
    box-shadow: 0 14px 28px rgba(127,102,110,0.045);
    transition: all .24s ease;
}

#lf2ChatApp .lf2-conversation:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.96);
    box-shadow: 0 20px 36px rgba(127,102,110,0.085);
}

#lf2ChatApp .lf2-conversation.active {
    border-color: rgba(255,49,95,0.52);
    box-shadow:
        0 20px 38px rgba(127,102,110,0.10),
        inset 4px 0 0 rgba(255,49,95,0.82);
}

#lf2ChatApp .lf2-avatar-wrap {
    position: relative;
    flex: 0 0 auto;
}

#lf2ChatApp .lf2-avatar,
#lf2ChatApp .lf2-msg-avatar {
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.98);
    box-shadow: 0 9px 20px rgba(0,0,0,0.10);
}

#lf2ChatApp .lf2-avatar {
    width: 50px;
    height: 50px;
}

#lf2ChatApp .lf2-online-dot {
    position: absolute;
    right: 1px;
    bottom: 3px;
    width: 13px;
    height: 13px;
    border-radius: 50%;
    background: #25c77a;
    border: 3px solid #fff;
}

#lf2ChatApp .lf2-conversation-info {
    min-width: 0;
    flex: 1;
}

#lf2ChatApp .lf2-conversation-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

#lf2ChatApp .lf2-conversation strong {
    color: #4c333c;
    font-size: 15px;
    font-weight: 950;
}

#lf2ChatApp .lf2-time-mini {
    color: #958a90;
    font-size: 11px;
    font-weight: 850;
    white-space: nowrap;
}

#lf2ChatApp .lf2-type {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 6px;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 950;
    color: var(--lf2-hot);
    background: rgba(255,49,95,0.10);
}

#lf2ChatApp .lf2-conversation p {
    margin: 7px 0 0;
    font-size: 13px;
    color: #958a90;
    line-height: 1.35;
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
    min-height: 110px;
    padding: 22px 28px;
    border-bottom: 1px solid var(--lf2-line);
    background: rgba(255,255,255,0.84);
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
    background: #25c77a;
    border: 3px solid #fff;
    box-shadow: 0 0 0 4px rgba(37,199,122,0.13);
}

#lf2ChatApp .lf2-avatar-lg {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.98);
    box-shadow: 0 14px 30px rgba(0,0,0,0.11);
}

#lf2ChatApp .lf2-person h2 {
    margin: 0 0 6px;
    font-size: 27px;
    line-height: 1.05;
    color: #2f252a;
    font-weight: 950;
    letter-spacing: -0.55px;
}

#lf2ChatApp .lf2-person p {
    margin: 0;
    color: #8d8389;
    font-size: 14px;
    font-weight: 850;
    display: flex;
    align-items: center;
    gap: 7px;
}

#lf2ChatApp .lf2-live-dot,
#lf2ChatApp .lf2-muted-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}

#lf2ChatApp .lf2-live-dot {
    background: #25c77a;
    box-shadow: 0 0 0 4px rgba(37,199,122,0.13);
}

#lf2ChatApp .lf2-muted-dot {
    background: #b8bdc3;
    box-shadow: 0 0 0 4px rgba(184,189,195,0.13);
}

#lf2ChatApp .lf2-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

#lf2ChatApp .lf2-logo-badge,
#lf2ChatApp .lf2-wallet-top {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 15px;
    border-radius: 999px;
    background: rgba(255,255,255,0.92);
    border: 1px solid rgba(224,220,222,0.95);
    color: #74676d;
    font-size: 12px;
    font-weight: 950;
    box-shadow: var(--lf2-shadow-soft);
}

#lf2ChatApp .lf2-wallet-top {
    gap: 7px;
    color: var(--lf2-hot);
    background: rgba(255,49,95,0.07);
    border-color: rgba(255,49,95,0.12);
}

#lf2ChatApp .lf2-controls {
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

#lf2ChatApp .lf2-controls a {
    color: #78676e;
    font-size: 13px;
    font-weight: 950;
    text-decoration: none;
}

#lf2ChatApp .lf2-header-dots,
#lf2ChatApp .lf2-call-btn {
    width: 42px;
    height: 42px;
    border: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.92);
    color: #3b3136;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    box-shadow: var(--lf2-shadow-soft);
}

#lf2ChatApp .lf2-messages {
    position: relative;
    flex: 1;
    min-height: 440px;
    padding: 28px 38px 22px;
    overflow-y: auto;
    background:
        radial-gradient(circle at top center, rgba(255,255,255,0.98), rgba(255,255,255,0) 34%),
        linear-gradient(180deg, rgba(250,250,251,0.99), rgba(246,247,248,0.99));
    scroll-behavior: smooth;
}

#lf2ChatApp .lf2-date {
    display: flex;
    align-items: center;
    gap: 16px;
    justify-content: center;
    margin: 8px 0 22px;
    color: #9b9297;
    font-size: 12px;
    font-weight: 900;
}

#lf2ChatApp .lf2-date:before,
#lf2ChatApp .lf2-date:after {
    content: "";
    height: 1px;
    flex: 1;
    max-width: 360px;
    background: rgba(222,218,220,0.82);
}

#lf2ChatApp .lf2-date span {
    padding: 0 4px;
    white-space: nowrap;
}

#lf2ChatApp .lf2-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin: 9px 0;
}

#lf2ChatApp .lf2-msg-row.mine {
    justify-content: flex-end;
}

#lf2ChatApp .lf2-msg-avatar {
    width: 34px;
    height: 34px;
    flex: 0 0 auto;
}

#lf2ChatApp .lf2-bubble {
    position: relative;
    display: inline-block !important;
    flex: 0 1 auto;
    min-width: 72px;
    max-width: 58%;
    width: auto !important;
    padding: 11px 44px 10px 15px;
    border-radius: 20px 20px 20px 9px;
    background: linear-gradient(180deg, #ffffff 0%, #f4f6f8 100%) !important;
    border: 1px solid rgba(210,216,223,0.95) !important;
    box-shadow:
        0 12px 25px rgba(83,90,102,0.07),
        inset 0 1px 0 rgba(255,255,255,0.98) !important;
    color: #45484d !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
    white-space: normal !important;
    hyphens: none !important;
}

#lf2ChatApp .lf2-msg-row.mine .lf2-bubble {
    border-radius: 20px 20px 9px 20px;
    background: linear-gradient(180deg, #f9fafb 0%, #edf0f3 100%) !important;
}

#lf2ChatApp .lf2-msg-text {
    display: block;
    font-size: 14px;
    line-height: 1.38;
    color: #333941 !important;
    white-space: pre-wrap !important;
    word-break: normal !important;
    overflow-wrap: break-word !important;
}

#lf2ChatApp .lf2-msg-meta {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 6px;
    font-size: 10.5px;
    color: #8e949c !important;
    font-weight: 850;
    white-space: nowrap !important;
}

#lf2ChatApp .lf2-edited {
    color: #a9afb6;
}

#lf2ChatApp .lf2-deleted .lf2-msg-text {
    color: #8a9097 !important;
    font-style: italic;
}

#lf2ChatApp .lf2-attachment-img {
    display: block;
    width: 250px;
    max-width: 100%;
    max-height: 320px;
    object-fit: cover;
    border-radius: 18px;
    box-shadow: 0 15px 34px rgba(0,0,0,0.11);
    margin: 2px 0 5px;
}

#lf2ChatApp .lf2-menu-btn {
    position: absolute;
    top: 7px;
    right: 8px;
    width: 28px;
    height: 22px;
    border: 0;
    border-radius: 999px;
    background: rgba(255,255,255,0.98) !important;
    color: #7b8188 !important;
    font-size: 17px;
    font-weight: 950;
    line-height: 10px;
    cursor: pointer;
    box-shadow: 0 7px 15px rgba(111,96,101,0.10);
}

#lf2ChatApp .lf2-actions {
    position: absolute;
    top: 34px;
    right: 7px;
    width: 205px;
    display: none;
    background: rgba(255,255,255,0.99);
    border: 1px solid rgba(224,220,222,0.96);
    border-radius: 17px;
    box-shadow: 0 24px 46px rgba(111,96,101,0.18);
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
    padding: 13px 15px;
    text-align: left;
    color: #5e5559;
    font-weight: 950;
    font-size: 13px;
    cursor: pointer;
}

#lf2ChatApp .lf2-action.delete {
    color: #e64b67;
}

#lf2ChatApp .lf2-typing {
    min-height: 28px;
    padding: 0 38px 8px;
    background: rgba(255,255,255,0.82);
    color: #8a8086;
    font-size: 12px;
    font-weight: 900;
    display: none;
}

#lf2ChatApp .lf2-typing.active {
    display: flex;
    align-items: center;
    gap: 7px;
}

#lf2ChatApp .lf2-typing-dots span {
    width: 5px;
    height: 5px;
    margin-right: 2px;
    display: inline-block;
    border-radius: 50%;
    background: #9ca2aa;
    animation: lf2TypingDot 1s infinite ease-in-out;
}

#lf2ChatApp .lf2-typing-dots span:nth-child(2) {
    animation-delay: .15s;
}

#lf2ChatApp .lf2-typing-dots span:nth-child(3) {
    animation-delay: .30s;
}

@keyframes lf2TypingDot {
    0%, 80%, 100% {
        opacity: .35;
        transform: translateY(0);
    }

    40% {
        opacity: 1;
        transform: translateY(-3px);
    }
}

#lf2ChatApp .lf2-form {
    min-height: 90px;
    padding: 16px 28px 20px;
    border-top: 1px solid var(--lf2-line);
    background: rgba(255,255,255,0.86);
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

#lf2ChatApp .lf2-tool {
    width: 46px;
    height: 46px;
    border: 0;
    border-radius: 50%;
    background: linear-gradient(180deg, #ffffff, #f5f6f8);
    color: #776c71;
    font-size: 18px;
    cursor: pointer;
    box-shadow: 0 13px 24px rgba(111,96,101,0.08);
}

#lf2ChatApp .lf2-tool.lf2-plus {
    color: var(--lf2-hot);
    background: rgba(255,49,95,0.08);
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
    border: 1px solid rgba(232,176,125,0.26);
    color: #9b5d1c;
    font-size: 12px;
    font-weight: 950;
}

#lf2ChatApp .lf2-edit-state[hidden] {
    display: none !important;
}

#lf2ChatApp .lf2-edit-state button {
    border: 0;
    background: transparent;
    color: var(--lf2-hot);
    font-weight: 950;
    cursor: pointer;
}

#lf2ChatApp .lf2-input-line {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 58px;
    padding: 0 7px 0 18px;
    border-radius: 999px;
    border: 1px solid rgba(222,218,220,0.98);
    background: linear-gradient(180deg, #ffffff, #fbfbfc);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.98),
        0 14px 26px rgba(111,96,101,0.045);
}

#lf2ChatApp .lf2-input-line input[type="text"] {
    flex: 1;
    min-width: 0;
    height: 52px;
    border: 0;
    background: transparent;
    outline: none;
    color: #4e474b;
    font-size: 14px;
}

#lf2ChatApp .lf2-send {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    border: 0;
    border-radius: 50%;
    background: linear-gradient(180deg, #151515, #000000);
    color: #ffffff;
    font-size: 24px;
    font-weight: 950;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 18px 32px rgba(0,0,0,0.22);
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
    font-weight: 950;
    font-size: 13px;
    border: 1px solid rgba(230,75,103,0.16);
    box-shadow: 0 22px 46px rgba(111,96,101,0.18);
    display: none;
    max-width: 520px;
}

#lf2ChatApp .lf2-empty {
    color: #8d8389;
}

@media (max-width: 920px) {
    #lf2ChatApp {
        padding: 0 0 16px;
    }

    #lf2ChatApp .lf2-desktop-view {
        display: none;
    }

    #lf2ChatApp .lf2-mobile-view {
        display: block;
    }

    #lf2ChatApp .lf2-mobile-shell {
        min-height: calc(100vh - 20px);
        border-radius: 28px;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,248,249,0.98));
        border: 1px solid rgba(224,220,222,0.92);
        box-shadow: 0 24px 70px rgba(111,96,101,0.12);
        display: flex;
        flex-direction: column;
    }

    #lf2ChatApp .lf2-mobile-header {
        min-height: 82px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid rgba(224,220,222,0.85);
        background: rgba(255,255,255,0.92);
    }

    #lf2ChatApp .lf2-mobile-person {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
        flex: 1;
    }

    #lf2ChatApp .lf2-mobile-person h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 950;
        color: #2f252a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #lf2ChatApp .lf2-mobile-person p {
        margin: 2px 0 0;
        font-size: 12px;
        font-weight: 850;
        color: #8d8389;
    }

    #lf2ChatApp .lf2-avatar-lg {
        width: 46px;
        height: 46px;
    }

    #lf2ChatApp .lf2-mobile-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #lf2ChatApp .lf2-mobile-actions .lf2-call-btn,
    #lf2ChatApp .lf2-mobile-actions .lf2-header-dots,
    #lf2ChatApp .lf2-mobile-back {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }

    #lf2ChatApp .lf2-mobile-wallet {
        padding: 10px 16px;
        background: rgba(255,49,95,0.045);
        border-bottom: 1px solid rgba(255,49,95,0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #7f5965;
        font-size: 12px;
        font-weight: 900;
    }

    #lf2ChatApp .lf2-mobile-wallet strong {
        color: var(--lf2-hot);
    }

    #lf2ChatApp .lf2-messages {
        flex: 1;
        min-height: 420px;
        padding: 18px 14px 12px;
    }

    #lf2ChatApp .lf2-msg-row {
        margin: 8px 0;
        gap: 8px;
    }

    #lf2ChatApp .lf2-msg-avatar {
        width: 30px;
        height: 30px;
    }

    #lf2ChatApp .lf2-bubble {
        max-width: 82%;
        min-width: 58px;
        padding: 10px 39px 9px 13px;
        border-radius: 18px 18px 18px 8px;
    }

    #lf2ChatApp .lf2-msg-row.mine .lf2-bubble {
        border-radius: 18px 18px 8px 18px;
    }

    #lf2ChatApp .lf2-msg-text {
        font-size: 13.5px;
        line-height: 1.36;
    }

    #lf2ChatApp .lf2-attachment-img {
        width: 210px;
        max-height: 280px;
        border-radius: 16px;
    }

    #lf2ChatApp .lf2-date {
        margin: 10px 0 16px;
        gap: 10px;
        font-size: 11px;
    }

    #lf2ChatApp .lf2-typing {
        padding: 0 16px 8px;
        font-size: 12px;
    }

    #lf2ChatApp .lf2-form {
        min-height: 78px;
        padding: 12px 12px 16px;
        gap: 8px;
    }

    #lf2ChatApp .lf2-tool {
        width: 42px;
        height: 42px;
        font-size: 18px;
    }

    #lf2ChatApp .lf2-form .lf2-tool:not(.lf2-plus) {
        display: none;
    }

    #lf2ChatApp .lf2-input-line {
        height: 50px;
        padding-left: 15px;
    }

    #lf2ChatApp .lf2-input-line input[type="text"] {
        height: 46px;
        font-size: 13.5px;
    }

    #lf2ChatApp .lf2-send {
        width: 42px;
        height: 42px;
        flex-basis: 42px;
        font-size: 22px;
    }

    #lf2ChatApp .lf2-mobile-list {
        padding: 16px;
        flex: 1;
        overflow-y: auto;
    }

    #lf2ChatApp .lf2-mobile-list .lf2-conversation {
        min-height: 76px;
        padding: 13px;
    }
}
</style>

<section id="lf2ChatApp">
    <div class="lf2-desktop-view">
        <div class="lf2-shell">
            <aside class="lf2-sidebar">
                <div class="lf2-sidebar-head">
                    <span class="lf2-pill">Mensagens</span>
                    <h1>Conversas</h1>
                </div>

                <div class="lf2-search-row">
                    <div class="lf2-search">
                        <span>🔎</span>
                        <span>Buscar conversas</span>
                    </div>
                    <button class="lf2-filter-btn" type="button">☷</button>
                </div>

                <div class="lf2-tabs">
                    <span class="lf2-tab active">Todas</span>
                    <span class="lf2-tab">Não lidas <b class="lf2-badge">3</b></span>
                    <span class="lf2-tab">Favoritas</span>
                </div>

                <div class="lf2-wallet-card">
                    <strong>🏹 Wallet BIX</strong>
                    <span>Reservado para compra de Flechas do Cupido.</span>
                    <div class="lf2-wallet-mini">
                        <span class="lf2-wallet-chip">🪙 0 BIX</span>
                        <span class="lf2-wallet-chip">💘 Em breve</span>
                    </div>
                </div>

                <?php if (count($conversations) === 0) { ?>
                    <div class="lf2-empty">Nenhuma conversa ainda.</div>
                <?php } ?>

                <?php $i = 0; ?>
                <?php foreach ($conversations as $conv) { ?>
                    <?php
                    $p = lf_renew_partner($conv, $user['id']);

                    $last = lf_renew_fetch_one_direct(
                        'SELECT body, attachment_path, is_deleted, created_at
                         FROM messages
                         WHERE conversation_id = ' . intval($conv['id']) . '
                         ORDER BY id DESC
                         LIMIT 1'
                    );

                    $preview = 'Conversa iniciada';

                    if ($last) {
                        if (isset($last['is_deleted']) && intval($last['is_deleted']) === 1) {
                            $preview = 'Mensagem excluida.';
                        } elseif (isset($last['attachment_path']) && trim($last['attachment_path']) !== '') {
                            $preview = 'Te enviou uma foto';
                        } else {
                            $preview = isset($last['body']) ? lf_renew_excerpt($last['body'], 34) : 'Conversa iniciada';
                        }
                    }

                    $conversationType = isset($conv['type']) ? $conv['type'] : 'dm';
                    $conversationTypeLabel = $conversationType === 'match' ? 'Match' : 'DM livre';
                    $lastTime = '';
                    if ($last && isset($last['created_at'])) {
                        $lastParts = explode(' ', $last['created_at']);
                        if (count($lastParts) > 1) {
                            $lastTime = substr($lastParts[1], 0, 5);
                        }
                    }
                    ?>

                    <a class="lf2-conversation <?php if ($conversationId == intval($conv['id'])) echo 'active'; ?>" href="chat.php?conversation_id=<?php echo intval($conv['id']); ?>">
                        <span class="lf2-avatar-wrap">
                            <img class="lf2-avatar" src="<?php echo $p ? lf_renew_h(lf_renew_photo($p, $i)) : 'assets/img/avatar-placeholder.svg'; ?>" alt="<?php echo $p && isset($p['name']) ? lf_renew_h($p['name']) : 'Usuario'; ?>">
                            <span class="lf2-online-dot"></span>
                        </span>

                        <span class="lf2-conversation-info">
                            <span class="lf2-conversation-top">
                                <span>
                                    <strong><?php echo $p && isset($p['name']) ? lf_renew_h(lf_renew_first_name($p['name'])) : 'Usuario'; ?></strong>
                                    <span class="lf2-type"><?php echo lf_renew_h($conversationTypeLabel); ?></span>
                                </span>
                                <span class="lf2-time-mini"><?php echo lf_renew_h($lastTime); ?></span>
                            </span>
                            <p><?php echo lf_renew_h($preview); ?></p>
                        </span>
                    </a>

                    <?php $i++; ?>
                <?php } ?>
            </aside>

            <section class="lf2-window">
                <div id="lf2ChatAlertDesktop" class="lf2-alert"></div>

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
                            <span class="lf2-wallet-top">🪙 Wallet BIX</span>
                            <span class="lf2-logo-badge">Love & Fire</span>
                        </div>
                    </div>

                    <div class="lf2-messages">
                        <div class="lf2-empty lf2-empty-main">
                            Selecione uma conversa ao lado para abrir o chat.
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="lf2-title">
                        <div class="lf2-person">
                            <div class="lf2-photo <?php echo $partnerOnline ? 'online' : ''; ?>">
                                <img class="lf2-avatar-lg" src="<?php echo lf_renew_h($partnerAvatar); ?>" alt="<?php echo lf_renew_h($partnerName); ?>">
                            </div>

                            <div>
                                <h2><?php echo lf_renew_h(lf_renew_first_name($partnerName)); ?></h2>
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
                            <span class="lf2-wallet-top">🪙 0 BIX · Flechas em breve</span>
                            <span class="lf2-logo-badge">Love & Fire</span>

                            <div class="lf2-controls">
                                <button class="lf2-call-btn" type="button">☎</button>
                                <button class="lf2-call-btn" type="button">▣</button>
                                <a href="api/block_user.php?target_id=<?php echo intval($partner['id']); ?>">Bloquear</a>
                                <a href="api/report_user.php?target_id=<?php echo intval($partner['id']); ?>">Denunciar</a>
                                <button class="lf2-header-dots" type="button">⋮</button>
                            </div>
                        </div>
                    </div>

                    <div
                        id="lf2MessagesDesktop"
                        class="lf2-messages"
                        data-conversation-id="<?php echo intval($activeConversation['id']); ?>"
                        data-current-user-id="<?php echo intval($user['id']); ?>"
                        data-current-avatar="<?php echo lf_renew_h($userAvatar); ?>"
                        data-partner-avatar="<?php echo lf_renew_h($partnerAvatar); ?>"
                    ></div>

                    <div id="lf2TypingDesktop" class="lf2-typing">
                        <span class="lf2-typing-dots"><span></span><span></span><span></span></span>
                        <b></b>
                    </div>

                    <form id="lf2MessageFormDesktop" class="lf2-form" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo lf_renew_h(lf_renew_csrf()); ?>">
                        <input type="hidden" name="conversation_id" value="<?php echo intval($activeConversation['id']); ?>">
                        <input type="hidden" name="message_id" value="">
                        <input id="lf2PictureDesktop" type="file" name="picture" accept="image/*" style="display:none;">

                        <button class="lf2-tool lf2-plus" type="button" data-tool="picture">+</button>
                        <button class="lf2-tool" type="button">😊</button>

                        <div class="lf2-input-wrap">
                            <div id="lf2EditStateDesktop" class="lf2-edit-state" hidden>
                                <span>Editando mensagem</span>
                                <button id="lf2CancelEditDesktop" type="button">Cancelar</button>
                            </div>

                            <div class="lf2-input-line">
                                <input type="text" name="body" maxlength="1000" placeholder="Digite uma mensagem..." autocomplete="off">
                                <button id="lf2SendBtnDesktop" class="lf2-send" type="submit">↑</button>
                            </div>
                        </div>
                    </form>
                <?php } ?>
            </section>
        </div>
    </div>

    <div class="lf2-mobile-view">
        <div class="lf2-mobile-shell">
            <?php if (!$activeConversation || !$partner) { ?>
                <div class="lf2-mobile-header">
                    <div class="lf2-mobile-person">
                        <div>
                            <h2>Conversas</h2>
                            <p>Selecione uma conversa</p>
                        </div>
                    </div>
                    <span class="lf2-wallet-chip">🪙 0 BIX</span>
                </div>

                <div class="lf2-mobile-wallet">
                    <span>🏹 Flechas do Cupido</span>
                    <strong>Em breve</strong>
                </div>

                <div class="lf2-mobile-list">
                    <?php if (count($conversations) === 0) { ?>
                        <div class="lf2-empty">Nenhuma conversa ainda.</div>
                    <?php } ?>

                    <?php $mi = 0; ?>
                    <?php foreach ($conversations as $conv) { ?>
                        <?php
                        $mp = lf_renew_partner($conv, $user['id']);
                        $mtype = isset($conv['type']) ? $conv['type'] : 'dm';
                        $mtypeLabel = $mtype === 'match' ? 'Match' : 'DM livre';

                        $mlast = lf_renew_fetch_one_direct(
                            'SELECT body, attachment_path, is_deleted, created_at
                             FROM messages
                             WHERE conversation_id = ' . intval($conv['id']) . '
                             ORDER BY id DESC
                             LIMIT 1'
                        );

                        $mpreview = 'Conversa iniciada';

                        if ($mlast) {
                            if (isset($mlast['is_deleted']) && intval($mlast['is_deleted']) === 1) {
                                $mpreview = 'Mensagem excluida.';
                            } elseif (isset($mlast['attachment_path']) && trim($mlast['attachment_path']) !== '') {
                                $mpreview = 'Te enviou uma foto';
                            } else {
                                $mpreview = isset($mlast['body']) ? lf_renew_excerpt($mlast['body'], 32) : 'Conversa iniciada';
                            }
                        }
                        ?>

                        <a class="lf2-conversation" href="chat.php?conversation_id=<?php echo intval($conv['id']); ?>">
                            <span class="lf2-avatar-wrap">
                                <img class="lf2-avatar" src="<?php echo $mp ? lf_renew_h(lf_renew_photo($mp, $mi)) : 'assets/img/avatar-placeholder.svg'; ?>" alt="<?php echo $mp && isset($mp['name']) ? lf_renew_h($mp['name']) : 'Usuario'; ?>">
                                <span class="lf2-online-dot"></span>
                            </span>

                            <span class="lf2-conversation-info">
                                <span class="lf2-conversation-top">
                                    <span>
                                        <strong><?php echo $mp && isset($mp['name']) ? lf_renew_h(lf_renew_first_name($mp['name'])) : 'Usuario'; ?></strong>
                                        <span class="lf2-type"><?php echo lf_renew_h($mtypeLabel); ?></span>
                                    </span>
                                </span>
                                <p><?php echo lf_renew_h($mpreview); ?></p>
                            </span>
                        </a>

                        <?php $mi++; ?>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="lf2-mobile-header">
                    <button class="lf2-mobile-back lf2-round-btn" type="button" onclick="window.location.href='chat.php';">‹</button>

                    <div class="lf2-mobile-person">
                        <div class="lf2-photo <?php echo $partnerOnline ? 'online' : ''; ?>">
                            <img class="lf2-avatar-lg" src="<?php echo lf_renew_h($partnerAvatar); ?>" alt="<?php echo lf_renew_h($partnerName); ?>">
                        </div>

                        <div>
                            <h2><?php echo lf_renew_h(lf_renew_first_name($partnerName)); ?></h2>
                            <p><?php echo $partnerOnline ? 'Online agora' : 'Visto recentemente'; ?></p>
                        </div>
                    </div>

                    <div class="lf2-mobile-actions">
                        <button class="lf2-call-btn" type="button">☎</button>
                        <button class="lf2-header-dots" type="button">⋮</button>
                    </div>
                </div>

                <div class="lf2-mobile-wallet">
                    <span>🪙 Wallet BIX: <strong>0 BIX</strong></span>
                    <span>🏹 Flechas em breve</span>
                </div>

                <div
                    id="lf2MessagesMobile"
                    class="lf2-messages"
                    data-conversation-id="<?php echo intval($activeConversation['id']); ?>"
                    data-current-user-id="<?php echo intval($user['id']); ?>"
                    data-current-avatar="<?php echo lf_renew_h($userAvatar); ?>"
                    data-partner-avatar="<?php echo lf_renew_h($partnerAvatar); ?>"
                ></div>

                <div id="lf2TypingMobile" class="lf2-typing">
                    <span class="lf2-typing-dots"><span></span><span></span><span></span></span>
                    <b></b>
                </div>

                <form id="lf2MessageFormMobile" class="lf2-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo lf_renew_h(lf_renew_csrf()); ?>">
                    <input type="hidden" name="conversation_id" value="<?php echo intval($activeConversation['id']); ?>">
                    <input type="hidden" name="message_id" value="">
                    <input id="lf2PictureMobile" type="file" name="picture" accept="image/*" style="display:none;">

                    <button class="lf2-tool lf2-plus" type="button" data-tool="picture">+</button>

                    <div class="lf2-input-wrap">
                        <div id="lf2EditStateMobile" class="lf2-edit-state" hidden>
                            <span>Editando mensagem</span>
                            <button id="lf2CancelEditMobile" type="button">Cancelar</button>
                        </div>

                        <div class="lf2-input-line">
                            <input type="text" name="body" maxlength="1000" placeholder="Digite uma mensagem..." autocomplete="off">
                            <button id="lf2SendBtnMobile" class="lf2-send" type="submit">↑</button>
                        </div>
                    </div>
                </form>
            <?php } ?>
        </div>
    </div>
</section>

<script>
(function () {
    function lf2IsMobile() {
        return window.matchMedia && window.matchMedia('(max-width: 920px)').matches;
    }

    function lf2Trim(text) {
        return String(text || '').replace(/^\s+|\s+$/g, '');
    }

    function lf2Escape(text) {
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

    function lf2Encode(data) {
        var pairs = [];
        var key;

        for (key in data) {
            if (data.hasOwnProperty(key)) {
                pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
            }
        }

        return pairs.join('&');
    }

    function lf2ParseJson(text) {
        var clean = String(text || '').replace(/^\uFEFF/, '');
        var firstBrace;
        var lastBrace;
        var sliced;

        clean = lf2Trim(clean);

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

    function lf2Ajax(method, url, data, callback) {
        var xhr = new XMLHttpRequest();
        var payload = data ? lf2Encode(data) : null;

        xhr.open(method, url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        }

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                callback(lf2ParseJson(xhr.responseText), xhr.responseText);
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

    function lf2AjaxForm(url, formData, callback) {
        var xhr = new XMLHttpRequest();

        xhr.open('POST', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                callback(lf2ParseJson(xhr.responseText), xhr.responseText);
            }
        };

        xhr.onerror = function () {
            callback({
                ok: false,
                error: 'Erro de conexao com o servidor.'
            }, '');
        };

        xhr.send(formData);
    }

    function lf2InitChat(mode) {
        var suffix = mode === 'mobile' ? 'Mobile' : 'Desktop';

        var messagesBox = document.getElementById('lf2Messages' + suffix);
        var form = document.getElementById('lf2MessageForm' + suffix);

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
        var pictureInput = document.getElementById('lf2Picture' + suffix);
        var editState = document.getElementById('lf2EditState' + suffix);
        var cancelEditBtn = document.getElementById('lf2CancelEdit' + suffix);
        var sendBtn = document.getElementById('lf2SendBtn' + suffix);
        var alertBox = document.getElementById('lf2ChatAlert' + suffix);
        var typingBox = document.getElementById('lf2Typing' + suffix);

        var lastHtml = '';
        var loadingMessages = false;
        var firstLoad = true;
        var lastTypingPing = 0;
        var typingTimer = null;

        function showAlert(text) {
            if (!text) {
                return;
            }

            if (!alertBox) {
                alert(text);
                return;
            }

            alertBox.innerHTML = lf2Escape(text);
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
            el.className = lf2Trim(el.className);
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
            var attachment = deleted ? '' : (message.attachment_path || '');
            var deletedClass = deleted ? ' lf2-deleted' : '';
            var editedText = isEdited(message) ? '<span class="lf2-edited">editada</span>' : '';
            var html = '';

            html += '<div class="' + rowClass + '" data-message-id="' + parseInt(message.id, 10) + '">';

            if (!mine) {
                html += '<img class="lf2-msg-avatar" src="' + lf2Escape(avatar) + '" alt="Avatar">';
            }

            html += '<div class="lf2-bubble' + deletedClass + '">';

            if (attachment !== '') {
                html += '<a href="' + lf2Escape(attachment) + '" target="_blank">';
                html += '<img class="lf2-attachment-img" src="' + lf2Escape(attachment) + '" alt="Imagem enviada">';
                html += '</a>';
            }

            if (text !== '' || deleted) {
                html += '<span class="lf2-msg-text">' + lf2Escape(text) + '</span>';
            }

            html += '<small class="lf2-msg-meta">';
            html += '<span>' + lf2Escape(formatTime(message.created_at)) + '</span>';
            html += editedText;

            if (mine && !deleted) {
                html += '<span>✓✓</span>';
            }

            html += '</small>';

            if (mine && !deleted) {
                html += '<button class="lf2-menu-btn" type="button" data-action="toggle-menu">...</button>';
                html += '<div class="lf2-actions">';
                html += '<button class="lf2-action" type="button" data-action="edit" data-id="' + parseInt(message.id, 10) + '" data-body="' + lf2Escape(message.body) + '">';
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
                    html += '<div class="lf2-date"><span>' + lf2Escape(dateLabel) + '</span></div>';
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

            lf2Ajax(
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

            lf2Ajax(
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

        function sendTypingPing() {
            var now = new Date().getTime();

            if (now - lastTypingPing < 1600) {
                return;
            }

            lastTypingPing = now;

            postChat('typing_ping', {}, function () {});
        }

        function sendTypingStop() {
            postChat('typing_stop', {}, function () {});
        }

        function checkTypingStatus() {
            lf2Ajax(
                'GET',
                'chat.php?conversation_id=' + encodeURIComponent(conversationId) + '&renew_ajax=1&renew_chat_action=typing_status&_=' + new Date().getTime(),
                null,
                function (data) {
                    if (!typingBox || !data || !data.ok) {
                        return;
                    }

                    if (data.typing) {
                        typingBox.className = 'lf2-typing active';
                        typingBox.querySelector('b').innerHTML = lf2Escape(data.name || 'Alguem') + ' está digitando...';
                    } else {
                        typingBox.className = 'lf2-typing';
                        typingBox.querySelector('b').innerHTML = '';
                    }
                }
            );
        }

        input.oninput = function () {
            if (lf2Trim(input.value) !== '') {
                sendTypingPing();

                if (typingTimer) {
                    clearTimeout(typingTimer);
                }

                typingTimer = setTimeout(function () {
                    sendTypingStop();
                }, 2600);
            } else {
                sendTypingStop();
            }
        };

        input.onblur = function () {
            sendTypingStop();
        };

        form.onsubmit = function (event) {
            if (event && event.preventDefault) {
                event.preventDefault();
            }

            var body = lf2Trim(input.value);
            var editingId = messageIdInput.value;
            var action = editingId ? 'edit' : 'send';

            if (body === '') {
                showAlert('Digite uma mensagem.');
                input.focus();
                return false;
            }

            sendBtn.disabled = true;
            sendTypingStop();

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

        var pictureButtons = form.querySelectorAll('[data-tool="picture"]');
        var pi;

        for (pi = 0; pi < pictureButtons.length; pi++) {
            pictureButtons[pi].onclick = function () {
                if (pictureInput) {
                    pictureInput.click();
                }
            };
        }

        if (pictureInput) {
            pictureInput.onchange = function () {
                if (!pictureInput.files || !pictureInput.files.length) {
                    return;
                }

                var fd = new FormData();
                fd.append('renew_ajax', '1');
                fd.append('renew_chat_action', 'send_picture');
                fd.append('conversation_id', conversationId);
                fd.append('csrf_token', csrfInput ? csrfInput.value : '');
                fd.append('body', lf2Trim(input.value));
                fd.append('picture', pictureInput.files[0]);

                sendBtn.disabled = true;

                lf2AjaxForm('chat.php?conversation_id=' + encodeURIComponent(conversationId), fd, function (data) {
                    sendBtn.disabled = false;
                    pictureInput.value = '';

                    if (!data || !data.ok) {
                        showAlert((data && data.error ? data.error : 'Nao foi possivel enviar a imagem.') + (data && data.debug ? ' | SQL: ' + data.debug : ''));
                        return;
                    }

                    resetEdit();
                    fetchMessages(true);
                });
            };
        }

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
        checkTypingStatus();

        setInterval(function () {
            fetchMessages(false);
        }, 3200);

        setInterval(function () {
            checkTypingStatus();
        }, 1800);
    }

    if (lf2IsMobile()) {
        lf2InitChat('mobile');
    } else {
        lf2InitChat('desktop');
    }
})();
</script>

<?php
if (function_exists('render_footer')) {
    render_footer();
}
?>