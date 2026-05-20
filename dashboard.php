<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$where = 'id <> ' . intval($user['id']) . ' AND status = \'active\' AND id NOT IN (SELECT blocked_id FROM blocks WHERE blocker_id = ' . intval($user['id']) . ')';

if ($query !== '') {
    $like = '%' . $query . '%';
    $where .= ' AND (name LIKE ' . db_escape($like) . ' OR city LIKE ' . db_escape($like) . ' OR country LIKE ' . db_escape($like) . ' OR bio LIKE ' . db_escape($like) . ')';
}

if ($filter === 'online') {
    $where .= ' AND last_seen >= ' . db_escape(date('Y-m-d H:i:s', time() - 900));
} elseif ($filter === 'near' && isset($user['city']) && $user['city'] !== '') {
    $where .= ' AND city = ' . db_escape($user['city']);
}

$limit = $filter === 'sign' ? 80 : 24;
$users = db_fetch_all('SELECT * FROM users WHERE ' . $where . ' ORDER BY last_seen DESC, created_at DESC LIMIT ' . intval($limit));
if ($filter === 'sign') {
    $mySign = love_zodiac_sign($user['birth_date']);
    $sameSign = array();
    foreach ($users as $candidate) {
        if ($mySign !== '' && love_zodiac_sign($candidate['birth_date']) === $mySign) {
            $sameSign[] = $candidate;
        }
        if (count($sameSign) >= 24) {
            break;
        }
    }
    $users = $sameSign;
}
$usingMockProfiles = false;
if (count($users) === 0 && $query === '' && $filter === '') {
    $usingMockProfiles = true;
    $users = array(
        array('id' => 101, 'name' => 'Juliana', 'birth_date' => '1999-06-12', 'city' => 'Sao Paulo', 'country' => 'SP', 'bio' => 'Viagens, praia e boas conversas.', 'avatar' => '', 'is_verified' => 1, 'last_seen' => now_sql(), 'is_mock' => 1),
        array('id' => 102, 'name' => 'Gabriel', 'birth_date' => '1997-03-09', 'city' => 'Rio de Janeiro', 'country' => 'RJ', 'bio' => 'Musica, cafe e por do sol.', 'avatar' => '', 'is_verified' => 0, 'last_seen' => now_sql(), 'is_mock' => 1),
        array('id' => 103, 'name' => 'Camila', 'birth_date' => '2001-11-03', 'city' => 'Belo Horizonte', 'country' => 'MG', 'bio' => 'Danca, livros e gastronomia.', 'avatar' => '', 'is_verified' => 1, 'last_seen' => now_sql(), 'is_mock' => 1),
        array('id' => 104, 'name' => 'Lucas', 'birth_date' => '1994-01-22', 'city' => 'Curitiba', 'country' => 'PR', 'bio' => 'Trilhas, pets e cinema.', 'avatar' => '', 'is_verified' => 0, 'last_seen' => date('Y-m-d H:i:s', time() - 3600), 'is_mock' => 1),
        array('id' => 105, 'name' => 'Rafael', 'birth_date' => '1996-08-18', 'city' => 'Florianopolis', 'country' => 'SC', 'bio' => 'Praia, fotografia e sushi.', 'avatar' => '', 'is_verified' => 1, 'last_seen' => now_sql(), 'is_mock' => 1),
        array('id' => 106, 'name' => 'Beatriz', 'birth_date' => '2000-04-25', 'city' => 'Salvador', 'country' => 'BA', 'bio' => 'Arte, shows e viagens.', 'avatar' => '', 'is_verified' => 0, 'last_seen' => now_sql(), 'is_mock' => 1)
    );
}
render_header('Explorar', 'dashboard');
?>
<section class="hc-desktop-shell hc-discovery-shell">
    <aside class="hc-room-rail">
        <span class="hc-pill">Salas publicas</span>
        <h2>Entre como convidado</h2>
        <a class="hc-room-card active" href="#"><span>🔥</span><strong>Geral Love</strong><small>132 online</small></a>
        <a class="hc-room-card" href="#"><span>💬</span><strong>Amizades</strong><small>87 online</small></a>
        <a class="hc-room-card" href="#"><span>🌈</span><strong>LGBTQIA+</strong><small>64 online</small></a>
        <a class="hc-room-card" href="#"><span>☕</span><strong>Cafe e Papo</strong><small>41 online</small></a>
        <div class="hc-conversion-card">
            <strong>Modo convidado</strong>
            <p>Visitantes conversam em grupo. Para salvar contato ou abrir DM, o cadastro rapido entra em cena.</p>
        </div>
    </aside>

    <section class="hc-main-panel">
        <section class="hc-discovery-panel">
            <div class="hc-screen-title">
                <span class="hc-pill">Descobrir pessoas</span>
                <h1>Perfis perto de voce</h1>
                <p>Busque por nome, cidade ou interesses e encontre conexoes com clima leve.</p>
            </div>
            <form method="get" class="hc-search-bar">
                <span class="hc-search-icon">&#128269;</span>
                <input type="text" name="q" value="<?php echo h($query); ?>" placeholder="Buscar por nome ou interesse...">
                <?php if ($filter !== '') { ?><input type="hidden" name="filter" value="<?php echo h($filter); ?>"><?php } ?>
            </form>
            <div class="hc-filter-row">
                <a class="hc-filter-chip <?php if ($filter === 'online') echo 'active'; ?>" href="dashboard.php?filter=online<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>"><span></span>Online Agora</a>
                <a class="hc-filter-chip <?php if ($filter === 'near') echo 'active'; ?>" href="dashboard.php?filter=near<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>">&#128205; Perto de Mim</a>
                <a class="hc-filter-chip <?php if ($filter === 'sign') echo 'active'; ?>" href="dashboard.php?filter=sign<?php echo $query !== '' ? '&q=' . urlencode($query) : ''; ?>">&#9734; Mesmo Signo</a>
            </div>
        </section>
        <section class="hc-profile-grid">
        <?php if (count($users) === 0) { ?>
            <div class="hc-empty">Nenhum perfil encontrado ainda. Ajuste os filtros ou crie usuarios de teste pelo cadastro.</div>
        <?php } ?>
        <?php $i = 0; ?>
        <?php foreach ($users as $person) { ?>
            <?php $age = love_age_text($person); $tags = love_interest_tags($i); ?>
            <article class="hc-profile-card">
                <a class="hc-profile-photo" href="<?php echo isset($person['is_mock']) ? 'swipe.php' : 'swipe.php?target_id=' . intval($person['id']); ?>">
                    <img src="<?php echo h(love_profile_photo($person, $i)); ?>" alt="<?php echo h($person['name']); ?>">
                    <span class="hc-photo-shine"></span>
                    <span class="hc-card-online <?php echo love_is_online($person) ? 'is-online' : ''; ?>"></span>
                    <span class="hc-location-pin">&#128205;</span>
                    <span class="hc-card-name"><?php echo h(love_first_name($person['name'])); ?><?php if ($age) { ?>, <?php echo h($age); ?><?php } ?></span>
                </a>
                <div class="hc-profile-meta">
                    <p><?php echo h($person['city']); ?><?php if ($person['country']) { ?>, <?php echo h($person['country']); ?><?php } ?> <?php if ($person['is_verified']) { ?><span class="hc-verified">&#10003;</span><?php } ?></p>
                    <div class="hc-mini-tags">
                        <?php for ($t = 0; $t < count($tags) && $t < 3; $t++) { ?><span><?php echo h($tags[$t]); ?></span><?php } ?>
                    </div>
                </div>
                <div class="hc-card-row">
                    <a class="hc-btn hc-btn-small hc-btn-ghost" href="<?php echo isset($person['is_mock']) ? 'chat.php' : 'api/dm_start.php?target_id=' . intval($person['id']); ?>">Chat</a>
                    <a class="hc-btn hc-btn-small hc-btn-primary" href="<?php echo isset($person['is_mock']) ? 'swipe.php' : 'swipe.php?target_id=' . intval($person['id']); ?>">Curtir</a>
                </div>
            </article>
            <?php $i++; ?>
        <?php } ?>
        </section>
    </section>

    <aside class="hc-context-rail">
        <div class="hc-human-card">
            <span>100% humano</span>
            <h2>Sem bots se passando por pessoas.</h2>
            <p>Verificacao por selfie, aceite para DMs livres, denuncia rapida e bloqueio em um clique.</p>
        </div>
        <div class="hc-mini-match">
            <img src="<?php echo h(love_profile_photo(array('id' => 101, 'avatar' => ''), 0)); ?>" alt="Mariana">
            <div>
                <strong>Mariana</strong>
                <small>Online agora</small>
            </div>
            <a class="hc-btn hc-btn-small hc-btn-primary" href="chat.php">Abrir chat</a>
        </div>
    </aside>
</section>
<?php render_footer(); ?>
