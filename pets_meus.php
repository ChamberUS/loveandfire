<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();

function lf_format_byx($valor)
{
    return number_format((float)$valor, 8, ',', '.');
}

$rows = db_fetch_all("SELECT pb.id AS pet_block_id, pb.valor_atual, po.ultima_compra_at,
o.id AS original_id, o.name AS original_nome, o.city AS original_city, o.avatar AS original_avatar
FROM lf_pet_ownership po
INNER JOIN lf_pet_blocks pb ON pb.id = po.pet_block_id
INNER JOIN users o ON o.id = po.usuario_original_id
WHERE po.dono_atual_id = " . intval($user['id']) . " AND po.status = 'active' AND pb.status = 'active'
ORDER BY pb.valor_atual DESC, po.ultima_compra_at DESC");

render_header('Meus Pets', 'pets');
?>
<style>
.lfpetm-wrap{max-width:1020px;margin:0 auto;padding:16px;background:linear-gradient(160deg,#fff8fb,#fff1f7);border:1px solid #ffd6e3;border-radius:20px}
.lfpetm-title{margin:0 0 12px;color:#bf1e54}
.lfpetm-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #ffd6e3}
.lfpetm-table th,.lfpetm-table td{padding:10px;border-bottom:1px solid #ffe3ec;text-align:left;font-size:14px}
.lfpetm-table th{background:#fff1f6;color:#a4194a}
.lfpetm-link{color:#d32860;text-decoration:none;font-weight:700}
</style>
<section class="lfpetm-wrap">
    <h1 class="lfpetm-title">Meus Pets em posse atual</h1>
    <table class="lfpetm-table">
        <thead><tr><th>Pet/Card</th><th>Dono original</th><th>Cidade</th><th>Valor atual</th><th>Última compra</th><th>Ação</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="6">Você ainda não possui pets.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>#<?php echo intval($r['pet_block_id']); ?></td>
                    <td><?php echo h($r['original_nome']); ?> (ID <?php echo intval($r['original_id']); ?>)</td>
                    <td><?php echo h($r['original_city']); ?></td>
                    <td><?php echo lf_format_byx($r['valor_atual']); ?> BYX</td>
                    <td><?php echo $r['ultima_compra_at'] ? h($r['ultima_compra_at']) : '-'; ?></td>
                    <td><a class="lfpetm-link" href="pet_view.php?id=<?php echo intval($r['pet_block_id']); ?>">Ver Pet</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php render_footer(); ?>
