<?php
require_once dirname(dirname(__FILE__)) . '/config/config.php';

if (SESSION_NAME) {
    session_name(SESSION_NAME);
}

if (session_id() === '') {
    session_start();
}

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/auth.php';
require_once dirname(__FILE__) . '/rules.php';

db_connect();
