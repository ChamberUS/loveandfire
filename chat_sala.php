<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';

function chat_sala_slug($value)
{
    return preg_replace('/[^a-z0-9-]/', '', strtolower((string)$value));
}

function chat_sala_limpar_inativos()
{
    db_query("UPDATE chat_visitantes SET status = 0 WHERE status = 1 AND last_activity < DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
}

function chat_sala_icone($icone)
{
    $map = array(
        'fire' => 'fa-fire',
        'comment' => 'fa-comment',
        'rainbow' => 'fa-rainbow',
        'coffee' => 'fa-coffee'
    );
    return isset($map[$icone]) ? $map[$icone] : 'fa-comments';
}

function chat_sala_limpar_sessao()
{
    unset($_SESSION['chat_visitante_id']);
    unset($_SESSION['chat_visitante_token']);
    unset($_SESSION['chat_sala_id']);
    unset($_SESSION['chat_nome_temp']);
}

chat_sala_limpar_inativos();

$slug = isset($_GET['sala']) ? chat_sala_slug($_GET['sala']) : '';
$sala = db_fetch_one("SELECT * FROM chat_salas WHERE slug = " . db_escape($slug) . " AND status = 1 LIMIT 1");
if (!$sala) {
    flash_set('err', 'Sala nao encontrada.');
    redirect('chat_publico.php');
}

$visitante = false;
if (!empty($_SESSION['chat_visitante_id']) && !empty($_SESSION['chat_visitante_token']) && !empty($_SESSION['chat_sala_id'])) {
    $visitante = db_fetch_one("SELECT * FROM chat_visitantes WHERE id = " . intval($_SESSION['chat_visitante_id']) . " AND sala_id = " . intval($sala['id']) . " AND token = " . db_escape($_SESSION['chat_visitante_token']) . " AND status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE) LIMIT 1");
}

if (!$visitante) {
    chat_sala_limpar_sessao();
    flash_set('err', 'Entre com um nome temporario para acessar a sala.');
    redirect('chat_publico.php');
}

db_query("UPDATE chat_visitantes SET last_activity = NOW() WHERE id = " . intval($visitante['id']) . " AND token = " . db_escape($visitante['token']) . " LIMIT 1");
$online = db_fetch_one("SELECT COUNT(*) AS total FROM chat_visitantes WHERE sala_id = " . intval($sala['id']) . " AND status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)");
$participantes = db_fetch_all("SELECT nome_temp FROM chat_visitantes WHERE sala_id = " . intval($sala['id']) . " AND status = 1 AND last_activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE) ORDER BY last_activity DESC LIMIT 18");

render_header($sala['nome'], 'chat-publico');
?>
<section class="msn-chat-shell" data-sala="<?php echo h($sala['slug']); ?>">
    <div class="msn-window msn-conversation-window">
        <div class="msn-titlebar">
            <div class="msn-title-left">
                <span class="msn-app-orb"><i class="fas <?php echo h(chat_sala_icone($sala['icone'])); ?>"></i></span>
                <div>
                    <strong><?php echo h($sala['nome']); ?></strong>
                    <span><span class="msn-status-dot"></span> online agora - Love & Fire Messenger</span>
                </div>
            </div>
            <div class="msn-window-controls"><span></span><span></span><span></span></div>
        </div>

        <div class="msn-chat-layout">
            <aside class="msn-contact-panel">
                <div class="msn-profile-card">
                    <div class="msn-contact-avatar large"><i class="fas fa-user"></i><span class="msn-status-dot"></span></div>
                    <strong><?php echo h($visitante['nome_temp']); ?></strong>
                    <p>Voce entrou como convidado</p>
                </div>
                <div class="msn-online-card">
                    <h2>Na sala</h2>
                    <p><strong id="chatOnlineCount"><?php echo intval($online['total']); ?></strong> online</p>
                    <div class="msn-participant-list">
                        <?php foreach ($participantes as $p) { ?>
                            <span><i class="fas fa-circle"></i><?php echo h($p['nome_temp']); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <div class="msn-room-note">
                    <strong>Converse com leveza</strong>
                    <p>Ao sair da sala, seu nome temporario sera removido da lista online.</p>
                </div>
            </aside>

            <section class="msn-chat-body">
                <div class="msn-chat-topic">
                    <div>
                        <span class="msn-online-pill"><span class="msn-status-dot"></span> sala publica</span>
                        <h1><?php echo h($sala['nome']); ?></h1>
                    </div>
                    <button type="button" class="msn-exit-button" id="chatSairBtn"><i class="fas fa-door-open"></i> Sair da sala</button>
                </div>

                <div id="chatMensagens" class="msn-message-area" aria-live="polite">
                    <div class="msn-empty-note">Carregando mensagens...</div>
                </div>

                <div class="msn-typing-row"><span></span> digitando uma boa conversa...</div>
                <form id="chatEnviarForm" class="msn-compose">
                    <div class="msn-emoticons" aria-hidden="true"><span>&#9829;</span><span>:-)</span><span>&#9830;</span><span>...</span></div>
                    <input type="text" name="mensagem" maxlength="500" autocomplete="off" placeholder="Digite uma mensagem..." required>
                    <button class="msn-chat-button" type="submit"><i class="fas fa-paper-plane"></i> Enviar</button>
                </form>
            </section>
        </div>
    </div>
</section>

<script>
(function () {
    var box = document.getElementById('chatMensagens');
    var form = document.getElementById('chatEnviarForm');
    var sair = document.getElementById('chatSairBtn');
    var online = document.getElementById('chatOnlineCount');
    var ultimoId = 0;
    var carregou = false;

    function ajax(method, url, data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        if (method === 'POST') {
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        }
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) { return; }
            if (xhr.status !== 200) {
                callback(false);
                return;
            }
            try {
                callback(JSON.parse(xhr.responseText));
            } catch (e) {
                callback(false);
            }
        };
        xhr.send(data || null);
    }

    function enc(v) {
        return encodeURIComponent(v);
    }

    function addMessage(msg) {
        if (!carregou) {
            box.innerHTML = '';
            carregou = true;
        }
        var item = document.createElement('div');
        item.className = msg.mine ? 'msn-message me' : 'msn-message other';
        item.innerHTML = '<span class="msn-message-name">' + msg.nome_temp + '</span><span class="msn-message-text">' + msg.mensagem + '</span><small>' + msg.horario + '</small>';
        box.appendChild(item);
        ultimoId = Math.max(ultimoId, parseInt(msg.id, 10));
        box.scrollTop = box.scrollHeight;
    }

    function listar() {
        ajax('GET', 'chat_ajax.php?acao=listar_mensagens&ultimo_id=' + ultimoId, null, function (data) {
            if (!data || !data.ok) {
                if (data && data.erro) { window.location.href = 'chat_publico.php'; }
                return;
            }
            if (data.mensagens.length === 0 && !carregou) {
                box.innerHTML = '<div class="msn-empty-note">Nenhuma mensagem ainda. Seja a primeira pessoa a puxar papo.</div>';
                carregou = true;
            }
            for (var i = 0; i < data.mensagens.length; i++) {
                addMessage(data.mensagens[i]);
            }
        });
    }

    function heartbeat() {
        ajax('GET', 'chat_ajax.php?acao=heartbeat', null, function (data) {
            if (!data || !data.ok) {
                window.location.href = 'chat_publico.php';
            }
        });
    }

    function atualizarOnline() {
        var sala = document.querySelector('.msn-chat-shell').getAttribute('data-sala');
        ajax('GET', 'chat_ajax.php?acao=online_count&sala=' + enc(sala), null, function (data) {
            if (data && data.ok && online) {
                online.innerHTML = data.online;
            }
        });
    }

    form.onsubmit = function (event) {
        event.preventDefault();
        var input = form.elements.mensagem;
        var texto = input.value.replace(/^\s+|\s+$/g, '');
        if (!texto) { return false; }
        ajax('POST', 'chat_ajax.php?acao=enviar_mensagem', 'mensagem=' + enc(texto), function (data) {
            if (data && data.ok) {
                input.value = '';
                listar();
                atualizarOnline();
            }
        });
        return false;
    };

    sair.onclick = function () {
        ajax('POST', 'chat_ajax.php?acao=sair', '', function () {
            window.location.href = 'chat_publico.php';
        });
    };

    window.onbeforeunload = function () {
        if (navigator.sendBeacon) {
            navigator.sendBeacon('chat_ajax.php?acao=sair', '');
        }
    };

    listar();
    setInterval(listar, 2500);
    setInterval(heartbeat, 20000);
    setInterval(atualizarOnline, 10000);
})();
</script>
<?php render_footer(); ?>