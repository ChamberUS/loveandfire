<?php
require_once dirname(__FILE__) . '/../core/bootstrap.php';
require_once dirname(__FILE__) . '/byx_api.php';

$resp = byx_api_health();

render_header('BYX Health', 'pets');
?>
<div style="max-width:760px;margin:20px auto;background:#fff;border:1px solid #ffd7e4;border-radius:16px;padding:16px;">
    <h2 style="margin-top:0;">Teste de saúde da API BYX</h2>
    <p><strong>Status:</strong> <?php echo $resp['ok'] ? 'ONLINE' : 'INDISPONIVEL'; ?></p>
    <p><strong>HTTP Code:</strong> <?php echo intval($resp['http_code']); ?></p>
    <p><strong>Mensagem:</strong> <?php echo h($resp['error'] !== '' ? $resp['error'] : 'OK'); ?></p>
    <p style="font-size:12px;color:#7f5362;">Token BYX não é exibido nesta tela.</p>
</div>
<?php render_footer(); ?>
