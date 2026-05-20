<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

$GLOBALS['HC_DB_LINK'] = null;

function db_connect()
{
    if ($GLOBALS['HC_DB_LINK']) {
        return $GLOBALS['HC_DB_LINK'];
    }

    $link = @mysql_connect(DB_HOST, DB_USER, DB_PASS);
    if (!$link) {
        die('Erro ao conectar no banco de dados. Verifique config/config.php');
    }

    if (!@mysql_select_db(DB_NAME, $link)) {
        die('Banco de dados nao encontrado: ' . h(DB_NAME));
    }

    @mysql_query("SET NAMES 'utf8'", $link);
    @mysql_query("SET CHARACTER SET utf8", $link);
    @mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'", $link);

    $GLOBALS['HC_DB_LINK'] = $link;
    return $link;
}

function db_escape($value)
{
    db_connect();
    if ($value === null) {
        return 'NULL';
    }
    return "'" . mysql_real_escape_string($value) . "'";
}

function db_query($sql)
{
    $link = db_connect();
    $result = mysql_query($sql, $link);
    if (!$result && APP_DEBUG) {
        die('<pre>Erro SQL: ' . h(mysql_error($link)) . "\n\n" . h($sql) . '</pre>');
    }
    return $result;
}

function db_fetch_assoc($result)
{
    if (!$result) { return false; }
    return mysql_fetch_assoc($result);
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
    return mysql_insert_id(db_connect());
}

function db_num_rows($result)
{
    if (!$result) { return 0; }
    return mysql_num_rows($result);
}
