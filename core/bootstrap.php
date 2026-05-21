<?php
require_once dirname(dirname(__FILE__)) . '/config/config.php';

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

if (SESSION_NAME) {
    session_name(SESSION_NAME);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );

    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ));
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
