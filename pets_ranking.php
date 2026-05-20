<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();

function lf_format_byx($valor)
{
    return number_format((float)$valor, 8, ',', '.');
}

$rankCaros = db_fetch_all("SELECT pb.id AS pet_block_id, pb.valor_atual, u.id AS usuario_id, u.name, u.city
FROM lf_pet_blocks pb
INNER JOIN users u ON u.id = pb.usuario_id
WHERE pb.mundo = 'pets' AND pb.status = 'active' AND u.status = 'active'
ORDER BY pb.valor_atual DESC
LIMIT 20");

$rankJogadores = db_fetch_all("SELECT u.id, u.name, COUNT(po.id) AS total_pets
FROM lf_pet_ownership po
INNER JOIN users u ON u.id = po.dono_atual_id
WHERE po.dono_atual_id IS NOT NULL AND po.status = 'active'
GROUP BY u.id, u.name
ORDER BY total_pets DESC, u.name ASC
LIMIT 20");

$rankComprados = db_fetch_all("SELECT u.id, u.name, COUNT(t.id) AS total_compras_recebidas
FROM lf_pet_transacoes t
INNER JOIN users u ON u.id = t.usuario_original_id
GROUP BY u.id, u.name
ORDER BY total_compras_recebidas DESC, u.name ASC
LIMIT 20");

render_header('Ranking LovenPets', 'pets');
?>
<style>
.lfpr-wrap{max-width:1120px;margin:0 auto;padding:18px;background:radial-gradient(circle at top right,#ffe8f0,#fff6fa 45%,#fff1f7);border:1px solid #ffd4e2;border-radius:24px;box-shadow:0 20px 45px rgba(230,56,103,.13)}
.lfpr-title{margin:0 0 6px;color:#bc1d52}
.lfpr-sub{margin:0 0 14px;color:#8a5a69}
.lfpr-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:14px}
.lfpr-card{background:#fff;border:1px solid #ffdbe7;border-radius:16px;padding:12px}
.lfpr-card h2{margin:0 0 8px;color:#b21a4d;font-size:18px}
.lfpr-list{margin:0;padding:0;list-style:none}
.lfpr-list li{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px dashed #ffe4ed;font-size:14px}
.lfpr-list li:last-child{border-bottom:0}
</style>
<section class="lfpr-wrap">
    <h1 class="lfpr-title">Ranking LovenPets</h1>
    <p class="lfpr-sub">Cards sociais mais caros, jogadores com mais posse atual e perfis mais comprados.</p>

    <div class="lfpr-grid">
        <article class="lfpr-card">
            <h2>Pets mais caros</h2>
            <ol class="lfpr-list">
                <?php foreach ($rankCaros as $r): ?>
                    <li><span>#<?php echo intval($r['pet_block_id']); ?> · <?php echo h($r['name']); ?></span><strong><?php echo lf_format_byx($r['valor_atual']); ?> BYX</strong></li>
                <?php endforeach; ?>
            </ol>
        </article>

        <article class="lfpr-card">
            <h2>Jogadores com mais Pets</h2>
            <ol class="lfpr-list">
                <?php foreach ($rankJogadores as $r): ?>
                    <li><span><?php echo h($r['name']); ?> (ID <?php echo intval($r['id']); ?>)</span><strong><?php echo intval($r['total_pets']); ?> pets</strong></li>
                <?php endforeach; ?>
            </ol>
        </article>

        <article class="lfpr-card">
            <h2>Perfis mais comprados</h2>
            <ol class="lfpr-list">
                <?php foreach ($rankComprados as $r): ?>
                    <li><span><?php echo h($r['name']); ?> (ID <?php echo intval($r['id']); ?>)</span><strong><?php echo intval($r['total_compras_recebidas']); ?> compras</strong></li>
                <?php endforeach; ?>
            </ol>
        </article>
    </div>
</section>
<?php render_footer(); ?>
