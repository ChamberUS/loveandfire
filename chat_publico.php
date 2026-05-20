<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';

function chat_publico_limpar_inativos()
{
    db_query("UPDATE chat_visitantes SET status = 0 WHERE status = 1 AND last_activity < DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
}

function chat_publico_texto($value, $max)
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

function chat_publico_slug($value)
{
    return preg_replace('/[^a-z0-9-]/', '', strtolower((string)$value));
}

function chat_publico_icone($icone)
{
    $map = array(
        'fire' => 'fa-fire',
        'comment' => 'fa-comment',
        'rainbow' => 'fa-rainbow',
        'coffee' => 'fa-coffee'
    );
    return isset($map[$icone]) ? $map[$icone] : 'fa-comments';
}

function chat_publico_hint($slug)
{
    $map = array(
        'geral-love' => 'romantica',
        'amizades' => 'conversa leve',
        'lgbtqia' => 'acolhedora',
        'cafe-e-papo' => 'movimentada'
    );
    return isset($map[$slug]) ? $map[$slug] : 'online';
}

function chat_publico_limpar_sessao()
{
    unset($_SESSION['chat_visitante_id']);
    unset($_SESSION['chat_visitante_token']);
    unset($_SESSION['chat_sala_id']);
    unset($_SESSION['chat_nome_temp']);
}

function chat_publico_entrar($sala, $nome)
{
    if (!empty($_SESSION['chat_visitante_id']) && !empty($_SESSION['chat_visitante_token'])) {
        db_query("UPDATE chat_visitantes SET status = 0 WHERE id = " . intval($_SESSION['chat_visitante_id']) . " AND token = " . db_escape($_SESSION['chat_visitante_token']) . " LIMIT 1");
    }

    $token = sha1(uniqid(mt_rand(), true) . $nome . time());
    $ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : '';
    db_query("INSERT INTO chat_visitantes (sala_id, nome_temp, token, ip, user_agent, entrou_em, last_activity, status) VALUES (" . intval($sala['id']) . ", " . db_escape($nome) . ", " . db_escape($token) . ", " . db_escape($ip) . ", " . db_escape($ua) . ", NOW(), NOW(), 1)");

    $_SESSION['chat_visitante_id'] = db_insert_id();
    $_SESSION['chat_visitante_token'] = $token;
    $_SESSION['chat_sala_id'] = intval($sala['id']);
    $_SESSION['chat_nome_temp'] = $nome;

    redirect('chat_sala.php?sala=' . $sala['slug']);
}

chat_publico_limpar_inativos();

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = isset($_POST['sala']) ? chat_publico_slug($_POST['sala']) : '';
    $nome = isset($_POST['nome_temp']) ? chat_publico_texto($_POST['nome_temp'], 80) : '';
    if ($nome === '') {
        $erro = 'Informe um nome temporario para entrar.';
    } else {
        $salaPost = db_fetch_one("SELECT * FROM chat_salas WHERE slug = " . db_escape($slug) . " AND status = 1 LIMIT 1");
        if ($salaPost) {
            chat_publico_entrar($salaPost, $nome);
        } else {
            $erro = 'Sala nao encontrada.';
        }
    }
}

$salas = db_fetch_all("SELECT s.*, (SELECT COUNT(*) FROM chat_visitantes v WHERE v.sala_id = s.id AND v.status = 1 AND v.last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)) AS online_total FROM chat_salas s WHERE s.status = 1 ORDER BY s.id ASC");

render_header('Bate-papo publico', 'chat-publico');
?>
<section class="msn-public-wrap">
    <div class="msn-window msn-rooms-window">
        <div class="msn-titlebar">
            <div class="msn-title-left">
                <span class="msn-app-orb"><i class="fas fa-comments"></i></span>
                <div>
                    <strong>Salas publicas</strong>
                    <span>Love & Fire Messenger</span>
                </div>
            </div>
            <div class="msn-window-controls"><span></span><span></span><span></span></div>
        </div>
        <div class="msn-room-hero">
            <span class="msn-online-pill"><span class="msn-status-dot"></span> online agora</span>
            <h1>Entre como convidado</h1>
            <p>Conheca novas conexoes em salas abertas, com leveza, respeito e aquele clima bom de MSN antigo.</p>
            <?php if ($erro !== '') { ?><div class="hc-alert hc-alert-err"><?php echo h($erro); ?></div><?php } ?>
        </div>
        <div class="msn-room-list">
            <?php foreach ($salas as $sala) { ?>
            <article class="msn-room-item">
                <div class="msn-contact-avatar"><i class="fas <?php echo h(chat_publico_icone($sala['icone'])); ?>"></i><span class="msn-status-dot"></span></div>
                <div class="msn-contact-main">
                    <div class="msn-contact-head">
                        <h2><?php echo h($sala['nome']); ?></h2>
                        <span><?php echo h(chat_publico_hint($sala['slug'])); ?></span>
                    </div>
                    <p><strong id="onlineSala<?php echo intval($sala['id']); ?>"><?php echo intval($sala['online_total']); ?></strong> pessoas online - Converse com leveza</p>
                    <form method="post" class="msn-room-form">
                        <input type="hidden" name="sala" value="<?php echo h($sala['slug']); ?>">
                        <input type="text" name="nome_temp" maxlength="80" required placeholder="Seu nome temporario">
                        <button class="msn-chat-button" type="submit">Entrar</button>
                    </form>
                </div>
            </article>
            <?php } ?>
        </div>
        <div class="msn-room-footer">
            <i class="fas fa-heart"></i> Mantenha o respeito e espalhe boas conversas.
        </div>
    </div>
</section>

<script>
(function () {
    function refreshCounts() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'chat_ajax.php?acao=online_count', true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4 || xhr.status !== 200) { return; }
            try {
                var data = JSON.parse(xhr.responseText);
                if (!data.ok || !data.counts) { return; }
                for (var id in data.counts) {
                    if (data.counts.hasOwnProperty(id)) {
                        var el = document.getElementById('onlineSala' + id);
                        if (el) { el.innerHTML = data.counts[id]; }
                    }
                }
            } catch (e) {}
        };
        xhr.send(null);
    }
    setInterval(refreshCounts, 15000);
})();
</script>
<?php render_footer(); ?>