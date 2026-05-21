<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function flash_set($key, $message)
{
    $_SESSION['flash'][$key] = $message;
}

function flash_get($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return '';
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        if (function_exists('random_bytes')) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $_SESSION['csrf_token'] = sha1(uniqid((string)mt_rand(), true));
        }
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_check()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Metodo invalido para esta operacao.');
    }
    $posted = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (
        !$posted ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $posted)
    ) {
        die('Token de seguranca invalido. Recarregue a pagina.');
    }
}

function age_from_birthdate($birthDate)
{
    if (!$birthDate || $birthDate === '0000-00-00') { return ''; }
    $birth = strtotime($birthDate);
    if (!$birth) { return ''; }
    $age = date('Y') - date('Y', $birth);
    if (date('md') < date('md', $birth)) { $age--; }
    return $age;
}

function user_avatar($user)
{
    if (isset($user['avatar']) && $user['avatar'] !== '') {
        return PUBLIC_UPLOAD_PATH . '/profile_photos/' . h($user['avatar']);
    }
    return 'assets/img/avatar-placeholder.svg';
}

function love_profile_photo($user, $slot)
{
    if (isset($user['avatar']) && $user['avatar'] !== '') {
        return PUBLIC_UPLOAD_PATH . '/profile_photos/' . h($user['avatar']);
    }

    $photos = array(
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1527980965255-d3b416303d12?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=900&q=80',
        'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=900&q=80'
    );
    $seed = isset($user['id']) ? intval($user['id']) : intval($slot);
    return $photos[$seed % count($photos)];
}

function love_is_online($user)
{
    if (!isset($user['last_seen']) || !$user['last_seen']) {
        return false;
    }
    $seen = strtotime($user['last_seen']);
    return $seen && (time() - $seen) <= 900;
}

function love_age_text($user)
{
    if (!isset($user['birth_date'])) {
        return '';
    }
    return age_from_birthdate($user['birth_date']);
}

function love_first_name($name)
{
    $parts = explode(' ', trim((string)$name));
    return isset($parts[0]) && $parts[0] !== '' ? $parts[0] : $name;
}

function love_interest_tags($seed)
{
    $groups = array(
        array('Viagens', 'Cafe', 'Musica', 'Praia', 'Livros'),
        array('Gastronomia', 'Pets', 'Cinema', 'Danca', 'Natureza'),
        array('Arte', 'Trilhas', 'Yoga', 'Fotografia', 'Sushi'),
        array('Shows', 'Vinho', 'Games', 'Series', 'Cozinhar')
    );
    return $groups[intval($seed) % count($groups)];
}

function love_zodiac_sign($birthDate)
{
    if (!$birthDate || $birthDate === '0000-00-00') {
        return '';
    }
    $time = strtotime($birthDate);
    if (!$time) {
        return '';
    }
    $m = intval(date('n', $time));
    $d = intval(date('j', $time));
    if (($m === 1 && $d >= 20) || ($m === 2 && $d <= 18)) { return 'Aquario'; }
    if (($m === 2 && $d >= 19) || ($m === 3 && $d <= 20)) { return 'Peixes'; }
    if (($m === 3 && $d >= 21) || ($m === 4 && $d <= 19)) { return 'Aries'; }
    if (($m === 4 && $d >= 20) || ($m === 5 && $d <= 20)) { return 'Touro'; }
    if (($m === 5 && $d >= 21) || ($m === 6 && $d <= 20)) { return 'Gemeos'; }
    if (($m === 6 && $d >= 21) || ($m === 7 && $d <= 22)) { return 'Cancer'; }
    if (($m === 7 && $d >= 23) || ($m === 8 && $d <= 22)) { return 'Leao'; }
    if (($m === 8 && $d >= 23) || ($m === 9 && $d <= 22)) { return 'Virgem'; }
    if (($m === 9 && $d >= 23) || ($m === 10 && $d <= 22)) { return 'Libra'; }
    if (($m === 10 && $d >= 23) || ($m === 11 && $d <= 21)) { return 'Escorpiao'; }
    if (($m === 11 && $d >= 22) || ($m === 12 && $d <= 21)) { return 'Sagitario'; }
    return 'Capricornio';
}

function asset_url($path)
{
    $full = dirname(dirname(__FILE__)) . '/' . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $version = @filemtime($full);
    if (!$version) {
        $version = time();
    }
    return $path . '?v=' . intval($version);
}

function now_sql()
{
    return date('Y-m-d H:i:s');
}

function today_sql()
{
    return date('Y-m-d');
}

function json_response($data)
{
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode($data);
    exit;
}

function request_int($key, $default)
{
    if (isset($_REQUEST[$key])) {
        return intval($_REQUEST[$key]);
    }
    return $default;
}

function post_text($key, $max)
{
    $value = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max, 'UTF-8');
    }
    return substr($value, 0, $max);
}

function ensure_message_edit_columns()
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $columns = array(
        'updated_at' => "ALTER TABLE messages ADD COLUMN updated_at DATETIME DEFAULT NULL",
        'is_edited' => "ALTER TABLE messages ADD COLUMN is_edited TINYINT(1) NOT NULL DEFAULT 0",
        'edited_at' => "ALTER TABLE messages ADD COLUMN edited_at DATETIME DEFAULT NULL",
        'deleted_at' => "ALTER TABLE messages ADD COLUMN deleted_at DATETIME DEFAULT NULL",
        'deleted_by' => "ALTER TABLE messages ADD COLUMN deleted_by INT UNSIGNED DEFAULT NULL",
        'is_deleted' => "ALTER TABLE messages ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0",
        'is_reported' => "ALTER TABLE messages ADD COLUMN is_reported TINYINT(1) NOT NULL DEFAULT 0",
        'status' => "ALTER TABLE messages ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'active'"
    );

    foreach ($columns as $name => $sql) {
        $exists = db_fetch_one("SHOW COLUMNS FROM messages LIKE " . db_escape($name));
        if (!$exists) {
            db_query($sql);
        }
    }

    $reportColumns = array(
        'message_id' => "ALTER TABLE reports ADD COLUMN message_id INT UNSIGNED DEFAULT NULL",
        'conversation_id' => "ALTER TABLE reports ADD COLUMN conversation_id INT UNSIGNED DEFAULT NULL",
        'snapshot_body' => "ALTER TABLE reports ADD COLUMN snapshot_body TEXT",
        'reviewed_by' => "ALTER TABLE reports ADD COLUMN reviewed_by INT UNSIGNED DEFAULT NULL",
        'reviewed_at' => "ALTER TABLE reports ADD COLUMN reviewed_at DATETIME DEFAULT NULL"
    );
    foreach ($reportColumns as $name => $sql) {
        $exists = db_fetch_one("SHOW COLUMNS FROM reports LIKE " . db_escape($name));
        if (!$exists) {
            db_query($sql);
        }
    }

    $roleExists = db_fetch_one("SHOW COLUMNS FROM users LIKE 'role'");
    if (!$roleExists) {
        db_query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
    }
}

function hc_is_moderator($user)
{
    if (!$user) {
        return false;
    }
    if (!isset($user['role'])) {
        $fresh = db_fetch_one('SELECT role FROM users WHERE id = ' . intval($user['id']) . ' LIMIT 1');
        if ($fresh && isset($fresh['role'])) {
            $user['role'] = $fresh['role'];
        }
    }
    if (!isset($user['role'])) {
        return false;
    }
    return $user['role'] === 'admin' || $user['role'] === 'moderator';
}

function hc_message_can_edit($message, $user)
{
    return $message && $user && intval($message['sender_id']) === intval($user['id']) && intval($message['is_deleted']) !== 1;
}

function hc_message_can_delete($message, $user)
{
    return $message && $user && intval($message['is_deleted']) !== 1 && (intval($message['sender_id']) === intval($user['id']) || hc_is_moderator($user));
}

function hc_uploaded_profile_photo_name($user)
{
    return isset($user['avatar']) ? basename($user['avatar']) : '';
}

function hc_delete_profile_photo($filename)
{
    $filename = basename((string)$filename);
    if ($filename === '' || $filename === '.gitkeep') {
        return;
    }
    $path = UPLOAD_DIR . '/profile_photos/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}

function hc_profile_photo_upload()
{
    if (empty($_FILES['avatar_file']) || !isset($_FILES['avatar_file']['error'])) {
        return '';
    }

    $file = $_FILES['avatar_file'];
    if (intval($file['error']) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if (intval($file['error']) !== UPLOAD_ERR_OK) {
        flash_set('err', 'Nao foi possivel enviar a foto. Tente novamente.');
        return '';
    }
    if (intval($file['size']) > 4 * 1024 * 1024) {
        flash_set('err', 'A foto de perfil precisa ter ate 4 MB.');
        return '';
    }

    $info = @getimagesize($file['tmp_name']);
    if (!$info || empty($info['mime'])) {
        flash_set('err', 'Envie uma imagem valida para a foto de perfil.');
        return '';
    }

    $extensions = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    );
    if (!isset($extensions[$info['mime']])) {
        flash_set('err', 'Use JPG, PNG, GIF ou WEBP para a foto de perfil.');
        return '';
    }

    $dir = UPLOAD_DIR . '/profile_photos';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    $name = 'profile_' . sha1(uniqid(mt_rand(), true)) . '.' . $extensions[$info['mime']];
    $target = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        flash_set('err', 'Nao foi possivel salvar a foto de perfil.');
        return '';
    }

    @chmod($target, 0644);
    return $name;
}

function render_header($title, $active)
{
    $user = auth_current_user();
    $pageClass = 'hc-page-' . preg_replace('/[^a-z0-9_-]/i', '', $active);
    echo '<!doctype html><html lang="pt-br"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . h($title) . ' - ' . h(APP_NAME) . '</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Pacifico&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
    echo '<link rel="stylesheet" href="' . h(asset_url('assets/css/app.css')) . '">';
    echo '</head><body class="' . ($user ? 'hc-app-shell ' : 'hc-public-shell ') . h($pageClass) . '">';
    echo '<header class="hc-topbar">';
    echo '<a class="hc-logo brand-wrap" href="index.php" aria-label="' . h(APP_NAME) . '"><img class="hc-logo-img" src="' . h(asset_url('assets/img/love-fire-logo.png')) . '" alt="' . h(APP_NAME) . '"><span class="powered-buynnex"><span class="powered-buynnex-mark"></span>Powered by Buynnex</span><span class="hc-logo-name">' . h(APP_NAME) . '</span></a>';
    echo '<div class="hc-top-actions">';
    if ($user) {
        echo '<a href="chat_publico.php" class="btn-chat-online"><span class="msn-status-dot"></span><i class="fas fa-comments"></i><span class="hc-nav-label">Bate-papo online</span></a>';
        echo '<span class="hc-user-chip"><img class="hc-user-chip-photo" src="' . h(user_avatar($user)) . '" alt="' . h($user['name']) . '"><span class="hc-live-dot"></span>' . h(love_first_name($user['name'])) . '</span>';
        if (isset($user['role']) && ($user['role'] === 'admin' || $user['role'] === 'moderator')) {
            echo '<a class="hc-logout" href="moderation_messages.php">Moderacao</a>';
        }
        echo '<a class="hc-logout" href="logout.php">Sair</a>';
    } else {
        echo '<nav class="hc-nav hc-public-nav">';
        echo '<a href="chat_publico.php" class="btn-chat-online"><span class="msn-status-dot"></span><i class="fas fa-comments"></i><span class="hc-nav-label">Bate-papo online</span></a>';
        nav_link('login.php', 'Entrar', $active === 'login', '');
        nav_link('register.php', 'Criar conta', $active === 'register', '');
        echo '</nav>';
    }
    echo '</div>';
    if ($user) {
        echo '<nav class="hc-nav hc-bottom-nav" aria-label="Navegacao principal">';
        nav_link('dashboard.php', 'Descobrir', $active === 'dashboard', '&#128293;');
        nav_link('feed.php', 'Curtidas', $active === 'feed', '&#9825;');
        nav_link('swipe.php', 'Matches', $active === 'swipe', '&#10084;');
        nav_link('chat.php', 'Mensagens', $active === 'chat', '&#128172;');
        nav_link('byx_wallet.php', 'Carteira BYX', $active === 'byx_wallet', '&#128179;');
        nav_link('profile_edit.php', 'Perfil', $active === 'profile', '&#9786;');
        echo '</nav>';
    }
    echo '</header><main class="hc-main">';
    $ok = flash_get('ok');
    $err = flash_get('err');
    if ($ok) { echo '<div class="hc-alert hc-alert-ok">' . h($ok) . '</div>'; }
    if ($err) { echo '<div class="hc-alert hc-alert-err">' . h($err) . '</div>'; }
}

function nav_link($href, $label, $active, $icon)
{
    echo '<a class="' . ($active ? 'active' : '') . '" href="' . h($href) . '">';
    if ($icon !== '') {
        echo '<span class="hc-nav-icon">' . $icon . '</span>';
    }
    echo '<span class="hc-nav-label">' . h($label) . '</span></a>';
}

function render_footer()
{
    echo '</main><footer class="lf-footer"><div class="lf-footer-inner"><span>&copy; ' . date('Y') . ' Love &amp; Fire</span><span class="footer-powered"><span class="footer-powered-mark"></span>Powered by Buynnex</span></div></footer><script src="assets/js/app.js"></script></body></html>';
}
