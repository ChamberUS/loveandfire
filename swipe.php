<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();

if (isset($_GET['target_id'])) {
    $targetId = intval($_GET['target_id']);
    if ($targetId > 0 && $targetId !== intval($user['id'])) {
        db_query('DELETE FROM swipes WHERE swiper_id = ' . intval($user['id']) . ' AND target_id = ' . intval($targetId));
        db_query("INSERT INTO swipes (swiper_id, target_id, action, created_at) VALUES (" . intval($user['id']) . ", " . intval($targetId) . ", 'like', " . db_escape(now_sql()) . ")");
        $reverse = db_fetch_one("SELECT id FROM swipes WHERE swiper_id = " . intval($targetId) . " AND target_id = " . intval($user['id']) . " AND action = 'like' LIMIT 1");
        if ($reverse && !find_active_match($user['id'], $targetId)) {
            $one = min(intval($user['id']), $targetId);
            $two = max(intval($user['id']), $targetId);
            db_query("INSERT INTO matches (user_one_id, user_two_id, status, created_at) VALUES (" . $one . ", " . $two . ", 'active', " . db_escape(now_sql()) . ")");
            $match = db_fetch_one('SELECT * FROM matches WHERE id = ' . intval(db_insert_id()) . ' LIMIT 1');
            get_or_create_match_conversation($match);
            flash_set('ok', 'Deu match! O chat foi liberado.');
        } else {
            flash_set('ok', 'Curtida registrada.');
        }
    }
    redirect('swipe.php');
}

$target = db_fetch_one('SELECT * FROM users WHERE id <> ' . intval($user['id']) . ' AND status = \'active\' AND id NOT IN (SELECT target_id FROM swipes WHERE swiper_id = ' . intval($user['id']) . ') ORDER BY RAND() LIMIT 1');
if (!$target) {
    $target = array(
        'id' => 0,
        'name' => 'Mariana',
        'birth_date' => '1999-09-12',
        'city' => 'Sao Paulo',
        'country' => 'SP',
        'bio' => 'Apaixonada por viagens, cafe e boas conversas que viram historias.',
        'avatar' => '',
        'is_verified' => 1,
        'last_seen' => now_sql(),
        'is_mock' => 1
    );
}
render_header('Matches', 'swipe');
?>
<section class="hc-match-stage">
<?php if (!$target) { ?>
    <div class="hc-empty">Sem novos perfis por enquanto. Volte depois ou explore a aba Descobrir.</div>
<?php } else { ?>
    <?php $age = love_age_text($target); $tags = love_interest_tags(intval($target['id'])); ?>
    <article class="hc-match-card">
        <div class="hc-match-photo">
            <img src="<?php echo h(love_profile_photo($target, 0)); ?>" alt="<?php echo h($target['name']); ?>">
            <span class="hc-photo-spark one">&#10022;</span>
            <span class="hc-photo-spark two">&#10022;</span>
            <span class="hc-match-float">&#128293;&#10084;</span>
        </div>
        <div class="hc-match-sheet">
            <h1><?php echo h(love_first_name($target['name'])); ?><span>&#9825;</span></h1>
            <p class="hc-match-location"><?php if ($age) { ?><?php echo h($age); ?> anos &bull; <?php } ?>&#128205; <?php echo h($target['city']); ?><?php if ($target['country']) { ?>, <?php echo h($target['country']); ?><?php } ?></p>
            <form action="api/swipe_action.php" method="post" class="hc-swipe-actions">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="target_id" value="<?php echo intval($target['id']); ?>">
                <button class="hc-round hc-no" type="submit" name="action" value="pass" aria-label="Pular">&#10005;</button>
                <button class="hc-round hc-yes" type="submit" name="action" value="like" aria-label="Curtir">&#128293;&#10084;</button>
            </form>
            <p class="hc-match-bio">&#10024; <?php echo h($target['bio'] ? $target['bio'] : 'Apaixonada por boas conversas, encontros leves e historias que ficam.'); ?></p>
            <div class="hc-interest-block">
                <strong>Interesses</strong>
                <div class="hc-interest-tags">
                    <?php foreach ($tags as $tag) { ?><span><?php echo h($tag); ?></span><?php } ?>
                </div>
            </div>
        </div>
    </article>
<?php } ?>
</section>
<?php render_footer(); ?>
