<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';

function chat_limpar_inativos()
{
    db_query("UPDATE chat_visitantes SET status = 0 WHERE status = 1 AND last_activity < DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
}

function chat_limpar_sessao()
{
    unset($_SESSION['chat_visitante_id']);
    unset($_SESSION['chat_visitante_token']);
    unset($_SESSION['chat_sala_id']);
    unset($_SESSION['chat_nome_temp']);
}

function chat_texto($value, $max)
{
    $value = trim(strip_tags((string)$value));
    $value = preg_replace('/[[:cntrl:]]/', '', $value);
    if (function_exists('mb_substr')) {
        $value = mb_substr($value, 0, $max, 'UTF-8');
    } else {
        $value = substr($value, 0, $max);
    }
    return trim($value);
}

function chat_slug($value)
{
    return preg_replace('/[^a-z0-9-]/', '', strtolower((string)$value));
}

function chat_sala_por_slug($slug)
{
    return db_fetch_one("SELECT * FROM chat_salas WHERE slug = " . db_escape($slug) . " AND status = 1 LIMIT 1");
}

function chat_visitante_atual()
{
    if (empty($_SESSION['chat_visitante_id']) || empty($_SESSION['chat_visitante_token']) || empty($_SESSION['chat_sala_id'])) {
        return false;
    }
    $id = intval($_SESSION['chat_visitante_id']);
    $salaId = intval($_SESSION['chat_sala_id']);
    $token = $_SESSION['chat_visitante_token'];
    return db_fetch_one("SELECT * FROM chat_visitantes WHERE id = " . $id . " AND sala_id = " . $salaId . " AND token = " . db_escape($token) . " AND status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE) LIMIT 1");
}

function chat_icone_classe($icone)
{
    $map = array(
        'fire' => 'fa-fire',
        'comment' => 'fa-comment',
        'rainbow' => 'fa-rainbow',
        'coffee' => 'fa-coffee'
    );
    return isset($map[$icone]) ? $map[$icone] : 'fa-comments';
}

function chat_criar_visitante($sala, $nome)
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
    $token = sha1(uniqid(mt_rand(), true) . $nome . time());

    if (!empty($_SESSION['chat_visitante_id']) && !empty($_SESSION['chat_visitante_token'])) {
        db_query("UPDATE chat_visitantes SET status = 0 WHERE id = " . intval($_SESSION['chat_visitante_id']) . " AND token = " . db_escape($_SESSION['chat_visitante_token']) . " LIMIT 1");
    }

    db_query("INSERT INTO chat_visitantes (sala_id, nome_temp, token, ip, user_agent, entrou_em, last_activity, status) VALUES (" . intval($sala['id']) . ", " . db_escape($nome) . ", " . db_escape($token) . ", " . db_escape($ip) . ", " . db_escape($ua) . ", NOW(), NOW(), 1)");
    $visitanteId = db_insert_id();

    $_SESSION['chat_visitante_id'] = $visitanteId;
    $_SESSION['chat_visitante_token'] = $token;
    $_SESSION['chat_sala_id'] = intval($sala['id']);
    $_SESSION['chat_nome_temp'] = $nome;

    return $visitanteId;
}

chat_limpar_inativos();

$acao = isset($_REQUEST['acao']) ? $_REQUEST['acao'] : '';

if ($acao === 'entrar') {
    $slug = isset($_POST['sala']) ? chat_slug($_POST['sala']) : '';
    $nome = isset($_POST['nome_temp']) ? chat_texto($_POST['nome_temp'], 80) : '';
    if ($nome === '') {
        json_response(array('ok' => false, 'erro' => 'Informe um nome temporario.'));
    }
    $sala = chat_sala_por_slug($slug);
    if (!$sala) {
        json_response(array('ok' => false, 'erro' => 'Sala nao encontrada.'));
    }
    chat_criar_visitante($sala, $nome);
    json_response(array('ok' => true, 'redirect' => 'chat_sala.php?sala=' . $sala['slug']));
}

if ($acao === 'listar_mensagens') {
    $visitante = chat_visitante_atual();
    if (!$visitante) {
        chat_limpar_sessao();
        json_response(array('ok' => false, 'erro' => 'Sessao do chat expirada.'));
    }
    $ultimoId = isset($_GET['ultimo_id']) ? intval($_GET['ultimo_id']) : 0;
    db_query("UPDATE chat_visitantes SET last_activity = NOW() WHERE id = " . intval($visitante['id']) . " AND token = " . db_escape($visitante['token']) . " LIMIT 1");
    $rows = db_fetch_all("SELECT id, visitante_id, nome_temp, mensagem, criado_em FROM chat_mensagens WHERE sala_id = " . intval($visitante['sala_id']) . " AND status = 1 AND id > " . $ultimoId . " ORDER BY id ASC LIMIT 80");
    $mensagens = array();
    foreach ($rows as $row) {
        $mensagens[] = array(
            'id' => intval($row['id']),
            'visitante_id' => intval($row['visitante_id']),
            'nome_temp' => h($row['nome_temp']),
            'mensagem' => h($row['mensagem']),
            'horario' => date('H:i', strtotime($row['criado_em'])),
            'mine' => intval($row['visitante_id']) === intval($visitante['id'])
        );
    }
    json_response(array('ok' => true, 'mensagens' => $mensagens));
}

if ($acao === 'enviar_mensagem') {
    $visitante = chat_visitante_atual();
    if (!$visitante) {
        chat_limpar_sessao();
        json_response(array('ok' => false, 'erro' => 'Sessao do chat expirada.'));
    }
    $mensagem = isset($_POST['mensagem']) ? chat_texto($_POST['mensagem'], 500) : '';
    if ($mensagem === '') {
        json_response(array('ok' => false, 'erro' => 'Digite uma mensagem.'));
    }
    db_query("INSERT INTO chat_mensagens (sala_id, visitante_id, nome_temp, mensagem, criado_em, status) VALUES (" . intval($visitante['sala_id']) . ", " . intval($visitante['id']) . ", " . db_escape($visitante['nome_temp']) . ", " . db_escape($mensagem) . ", NOW(), 1)");
    db_query("UPDATE chat_visitantes SET last_activity = NOW() WHERE id = " . intval($visitante['id']) . " AND token = " . db_escape($visitante['token']) . " LIMIT 1");
    json_response(array('ok' => true));
}

if ($acao === 'heartbeat') {
    $visitante = chat_visitante_atual();
    if (!$visitante) {
        chat_limpar_sessao();
        json_response(array('ok' => false, 'erro' => 'Sessao do chat expirada.'));
    }
    db_query("UPDATE chat_visitantes SET last_activity = NOW() WHERE id = " . intval($visitante['id']) . " AND token = " . db_escape($visitante['token']) . " LIMIT 1");
    json_response(array('ok' => true));
}

if ($acao === 'sair') {
    if (!empty($_SESSION['chat_visitante_id']) && !empty($_SESSION['chat_visitante_token'])) {
        db_query("UPDATE chat_visitantes SET status = 0 WHERE id = " . intval($_SESSION['chat_visitante_id']) . " AND token = " . db_escape($_SESSION['chat_visitante_token']) . " LIMIT 1");
    }
    chat_limpar_sessao();
    json_response(array('ok' => true, 'redirect' => 'chat_publico.php'));
}

if ($acao === 'online_count') {
    $slug = isset($_GET['sala']) ? chat_slug($_GET['sala']) : '';
    if ($slug !== '') {
        $sala = chat_sala_por_slug($slug);
        if (!$sala) {
            json_response(array('ok' => false, 'online' => 0));
        }
        $row = db_fetch_one("SELECT COUNT(*) AS total FROM chat_visitantes WHERE sala_id = " . intval($sala['id']) . " AND status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
        json_response(array('ok' => true, 'online' => intval($row['total'])));
    }
    $rows = db_fetch_all("SELECT sala_id, COUNT(*) AS total FROM chat_visitantes WHERE status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE) GROUP BY sala_id");
    $counts = array();
    foreach ($rows as $row) {
        $counts[intval($row['sala_id'])] = intval($row['total']);
    }
    json_response(array('ok' => true, 'counts' => $counts));
}

json_response(array('ok' => false, 'erro' => 'Acao invalida.'));
?>
