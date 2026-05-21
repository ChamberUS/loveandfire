<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

function hc_password_salt()
{
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes(16));
    }
    return substr(sha1(uniqid((string)mt_rand(), true)), 0, 32);
}

function hc_password_hash($password, $salt)
{
    $hash = $password;
    $i = 0;
    while ($i < 1000) {
        $hash = hash('sha256', $salt . $hash . $salt);
        $i++;
    }
    return $hash;
}

function hc_password_hash_modern($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function hc_password_verify_legacy($password, $user)
{
    if (empty($user['password_salt']) || empty($user['password_hash'])) {
        return false;
    }
    $legacyHash = hc_password_hash($password, $user['password_salt']);
    return hash_equals($user['password_hash'], $legacyHash);
}

function hc_password_needs_rehash($hash)
{
    if (!$hash) {
        return true;
    }
    $info = password_get_info($hash);
    if (!isset($info['algo']) || !$info['algo']) {
        return true;
    }
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}

function auth_register($name, $email, $password, $birthDate, $gender, $city, $country)
{
    $email = strtolower(trim($email));
    $exists = db_fetch_one('SELECT id FROM users WHERE email = ' . db_escape($email) . ' LIMIT 1');
    if ($exists) {
        return array(false, 'Este e-mail ja esta cadastrado.');
    }

    if (strlen($password) < 6) {
        return array(false, 'A senha precisa ter pelo menos 6 caracteres.');
    }

    $salt = '';
    $hash = hc_password_hash_modern($password);
    if ($hash === false) {
        return array(false, 'Nao foi possivel criar sua conta agora. Tente novamente.');
    }

    db_query("INSERT INTO users
        (name, email, password_hash, password_salt, birth_date, gender, city, country, dm_mode, status, created_at, updated_at)
        VALUES
        (" . db_escape($name) . ", " . db_escape($email) . ", " . db_escape($hash) . ", " . db_escape($salt) . ", " . db_escape($birthDate) . ", " . db_escape($gender) . ", " . db_escape($city) . ", " . db_escape($country) . ", 'open', 'active', " . db_escape(now_sql()) . ", " . db_escape(now_sql()) . ")");

    $id = db_insert_id();
    db_query("INSERT INTO profile_preferences (user_id, looking_for, min_age, max_age, city, verified_only, created_at, updated_at)
        VALUES (" . intval($id) . ", 'all', 18, 80, " . db_escape($city) . ", 0, " . db_escape(now_sql()) . ", " . db_escape(now_sql()) . ")");

    $_SESSION['user_id'] = $id;
    if (function_exists('session_regenerate_id')) {
        @session_regenerate_id(true);
    }
    return array(true, 'Conta criada com sucesso.');
}

function auth_login($email, $password)
{
    $email = strtolower(trim($email));
    $user = db_fetch_one('SELECT * FROM users WHERE email = ' . db_escape($email) . ' AND status = \'active\' LIMIT 1');
    if (!$user) {
        return array(false, 'E-mail ou senha invalidos.');
    }

    $valid = false;
    if (!empty($user['password_hash'])) {
        $info = password_get_info($user['password_hash']);
        if (isset($info['algo']) && $info['algo']) {
            $valid = password_verify($password, $user['password_hash']);
        } else {
            $valid = hc_password_verify_legacy($password, $user);
        }
    }

    if (!$valid) {
        return array(false, 'E-mail ou senha invalidos.');
    }

    if (hc_password_needs_rehash($user['password_hash'])) {
        $newHash = hc_password_hash_modern($password);
        if ($newHash !== false) {
            db_query('UPDATE users SET password_hash = ' . db_escape($newHash) . ", password_salt = '' WHERE id = " . intval($user['id']));
        }
    }

    $_SESSION['user_id'] = intval($user['id']);
    if (function_exists('session_regenerate_id')) {
        @session_regenerate_id(true);
    }
    db_query('UPDATE users SET last_seen = ' . db_escape(now_sql()) . ' WHERE id = ' . intval($user['id']));
    return array(true, 'Login realizado com sucesso.');
}

function auth_logout()
{
    unset($_SESSION['user_id']);
    session_destroy();
}

function auth_current_user()
{
    if (empty($_SESSION['user_id'])) {
        return false;
    }
    $id = intval($_SESSION['user_id']);
    return db_fetch_one('SELECT * FROM users WHERE id = ' . $id . ' AND status = \'active\' LIMIT 1');
}

function auth_require()
{
    $user = auth_current_user();
    if (!$user) {
        flash_set('err', 'Entre na sua conta para continuar.');
        redirect('login.php');
    }
    return $user;
}
