<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();
ensure_message_edit_columns();

if (!hc_is_moderator($user)) {
    flash_set('err', 'Acesso restrito a moderadores.');
    redirect('dashboard.php');
}

$reports = db_fetch_all("SELECT r.*, m.body, m.is_deleted, m.is_edited, m.is_reported, m.status AS message_status, m.created_at AS message_created_at,
        reporter.name AS reporter_name, reported.name AS reported_name, reported.status AS reported_status
    FROM reports r
    LEFT JOIN messages m ON m.id = r.message_id
    LEFT JOIN users reporter ON reporter.id = r.reporter_id
    LEFT JOIN users reported ON reported.id = r.reported_id
    WHERE r.message_id IS NOT NULL
    ORDER BY r.created_at DESC
    LIMIT 100");

render_header('Moderacao de mensagens', 'profile');
?>
<section class="hc-auth-card hc-wide hc-moderation-panel">
    <h1>Moderacao de mensagens</h1>
    <p>Analise denuncias, apague mensagens abusivas e bloqueie perfis infratores.</p>

    <?php if (count($reports) === 0) { ?>
        <div class="hc-empty-mini">Nenhuma mensagem denunciada no momento.</div>
    <?php } ?>

    <?php foreach ($reports as $r) { ?>
        <?php
            $messageText = isset($r['snapshot_body']) && $r['snapshot_body'] !== '' ? $r['snapshot_body'] : $r['body'];
            if (intval($r['is_deleted']) === 1) {
                $messageText = 'Mensagem apagada.';
            }
        ?>
        <article class="hc-report-card">
            <div class="hc-report-head">
                <div>
                    <strong><?php echo h($r['reported_name']); ?></strong>
                    <span>denunciado por <?php echo h($r['reporter_name']); ?></span>
                </div>
                <em><?php echo h($r['status']); ?></em>
            </div>

            <p class="hc-report-message"><?php echo h($messageText); ?></p>
            <div class="hc-report-meta">
                <span>Motivo: <?php echo h($r['reason']); ?></span>
                <span>Denuncia: <?php echo h($r['created_at']); ?></span>
                <span>Status da mensagem: <?php echo h($r['message_status']); ?></span>
            </div>

            <div class="hc-report-actions">
                <?php if (intval($r['is_deleted']) !== 1 && intval($r['message_id']) > 0) { ?>
                    <form method="post" action="api/moderation_message_delete.php" onsubmit="return confirm('Apagar esta mensagem denunciada?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="message_id" value="<?php echo intval($r['message_id']); ?>">
                        <button class="hc-btn hc-btn-danger hc-btn-small" type="submit">Apagar mensagem</button>
                    </form>
                <?php } ?>
                <?php if ($r['reported_status'] !== 'banned') { ?>
                    <form method="post" action="api/moderation_block_user.php" onsubmit="return confirm('Bloquear este usuario?');">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="target_id" value="<?php echo intval($r['reported_id']); ?>">
                        <button class="hc-btn hc-btn-ghost hc-btn-small" type="submit">Bloquear usuario</button>
                    </form>
                <?php } ?>
            </div>
        </article>
    <?php } ?>
</section>
<?php render_footer(); ?>
