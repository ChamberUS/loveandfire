<?php
if (!defined('HC_APP')) { die('Acesso negado'); }

function hc_password_salt()
{
    return substr(sha1(uniqid(mt_rand(), true)), 0, 32);
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

    $salt = hc_password_salt();
    $hash = hc_password_hash($password, $salt);

    db_query("INSERT INTO users
        (name, email, password_hash, password_salt, birth_date, gender, city, country, dm_mode, status, created_at, updated_at)
        VALUES
        (" . db_escape($name) . ", " . db_escape($email) . ", " . db_escape($hash) . ", " . db_escape($salt) . ", " . db_escape($birthDate) . ", " . db_escape($gender) . ", " . db_escape($city) . ", " . db_escape($country) . ", 'open', 'active', " . db_escape(now_sql()) . ", " . db_escape(now_sql()) . ")");

    $id = db_insert_id();
    db_query("INSERT INTO profile_preferences (user_id, looking_for, min_age, max_age, city, verified_only, created_at, updated_at)
        VALUES (" . intval($id) . ", 'all', 18, 80, " . db_escape($city) . ", 0, " . db_escape(now_sql()) . ", " . db_escape(now_sql()) . ")");

    $_SESSION['user_id'] = $id;
    return array(true, 'Conta criada com sucesso.');
}

function auth_login($email, $password)
{
    $email = strtolower(trim($email));
    $user = db_fetch_one('SELECT * FROM users WHERE email = ' . db_escape($email) . ' AND status = \'active\' LIMIT 1');
    if (!$user) {
        return array(false, 'E-mail ou senha invalidos.');
    }

    $hash = hc_password_hash($password, $user['password_salt']);
    if ($hash !== $user['password_hash']) {
        return array(false, 'E-mail ou senha invalidos.');
    }

    $_SESSION['user_id'] = intval($user['id']);
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
