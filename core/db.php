<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

$GLOBALS['HC_DB_LINK'] = null;

function db_connect()
{
    if ($GLOBALS['HC_DB_LINK']) {
        return $GLOBALS['HC_DB_LINK'];
    }

    $link = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$link) {
        die('Erro ao conectar no banco de dados. Verifique config/config.php');
    }

    if (!@mysqli_set_charset($link, 'utf8mb4')) {
        @mysqli_set_charset($link, 'utf8');
    }

    @mysqli_query($link, "SET NAMES utf8mb4");
    @mysqli_query($link, "SET collation_connection = 'utf8mb4_unicode_ci'");

    $GLOBALS['HC_DB_LINK'] = $link;
    return $link;
}

function db_escape($value)
{
    db_connect();
    if ($value === null) {
        return 'NULL';
    }
    return "'" . mysqli_real_escape_string(db_connect(), (string)$value) . "'";
}

function db_query($sql)
{
    $link = db_connect();
    $result = mysqli_query($link, $sql);
    if (!$result && APP_DEBUG) {
        die('<pre>Erro SQL: ' . h(mysqli_error($link)) . "\n\n" . h($sql) . '</pre>');
    }
    return $result;
}

function db_fetch_assoc($result)
{
    if (!$result) { return false; }
    return mysqli_fetch_assoc($result);
}

function db_fetch_one($sql)
{
    $res = db_query($sql);
    if (!$res) { return false; }
    return db_fetch_assoc($res);
}

function db_fetch_all($sql)
{
    $res = db_query($sql);
    $rows = array();
    if ($res) {
        while ($row = db_fetch_assoc($res)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function db_insert_id()
{
    return mysqli_insert_id(db_connect());
}

function db_num_rows($result)
{
    if (!$result) { return 0; }
    return mysqli_num_rows($result);
}

function db_error()
{
    return mysqli_error(db_connect());
}
