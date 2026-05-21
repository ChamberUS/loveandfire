<?php
/*
 * Love & Fire PHP 8+ - Configuracao principal
 */

if (!defined('HC_APP')) {
    define('HC_APP', true);
}

define('APP_NAME', 'Love & Fire');
define('APP_URL', 'http://lovenfire.com');

define('DB_HOST', 'lovenfiredb.mysql.dbaas.com.br');
define('DB_USER', 'lovenfiredb');
define('DB_PASS', 'Aifx4hdMFFUaq@');
define('DB_NAME', 'lovenfiredb');

define('UPLOAD_DIR', dirname(dirname(__FILE__)) . '/uploads');
define('PUBLIC_UPLOAD_PATH', 'uploads');

define('FREE_DM_DAILY_LIMIT', 5);
define('SESSION_NAME', 'LOVENFIRESESSID');
define('APP_DEBUG', false);

/*
 * Projeto atualizado para PHP 8+ com mysqli.
 */
?>
