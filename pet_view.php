<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
$user = auth_require();

function lf_format_byx($valor)
{
    return number_format((float)$valor, 8, ',', '.');
}

$petBlockId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($petBlockId <= 0) {
    flash_set('err', 'Pet invalido.');
    redirect('pets.php');
}

$pet = db_fetch_one("SELECT pb.*, po.dono_atual_id, po.ultima_compra_at, po.usuario_original_id, po.valor_atual AS ownership_valor_atual,
u.name, u.city, u.avatar, u.birth_date,
dono.name AS dono_nome
FROM lf_pet_blocks pb
INNER JOIN users u ON u.id = pb.usuario_id
LEFT JOIN lf_pet_ownership po ON po.pet_block_id = pb.id
LEFT JOIN users dono ON dono.id = po.dono_atual_id
WHERE pb.id = " . $petBlockId . " LIMIT 1");

if (!$pet) {
    flash_set('err', 'Pet nao encontrado.');
    redirect('pets.php');
}

$valorAtual = (float)$pet['valor_atual'];
$percentual = 0.25;
if ($valorAtual <= 100) {
    $percentual = 3;
} elseif ($valorAtual <= 1000) {
    $percentual = 2;
} elseif ($valorAtual <= 10000) {
    $percentual = 1.5;
} elseif ($valorAtual <= 100000) {
    $percentual = 1;
} elseif ($valorAtual <= 1000000) {
    $percentual = 0.5;
}
$proximoValor = $valorAtual + ($valorAtual * ($percentual / 100));

$transacoes = db_fetch_all("SELECT t.*, c.name AS comprador_nome, v.name AS vendedor_nome
FROM lf_pet_transacoes t
LEFT JOIN users c ON c.id = t.comprador_id
LEFT JOIN users v ON v.id = t.vendedor_id
WHERE t.pet_block_id = " . intval($pet['id']) . "
ORDER BY t.id DESC
LIMIT 10");

$isOwnProfile = intval($pet['usuario_id']) === intval($user['id']);
$isCurrentOwner = intval($pet['dono_atual_id']) === intval($user['id']);

render_header('Pet #' . intval($pet['id']), 'pets');
?>
<style>
.lfpetv-wrap{max-width:980px;margin:0 auto;background:linear-gradient(160deg,#fff6fa,#fff2f7);padding:18px;border:1px solid #ffd4e2;border-radius:24px;box-shadow:0 18px 38px rgba(242,54,95,.12)}
.lfpetv-grid{display:grid;grid-template-columns:310px 1fr;gap:16px}
.lfpetv-photo{width:100%;height:340px;object-fit:cover;border-radius:18px;border:1px solid #ffd4e2}
.lfpetv-card{background:#fff;border:1px solid #ffd4e2;border-radius:16px;padding:14px}
.lfpetv-title{margin:0 0 8px;color:#be1b53}
.lfpetv-line{display:flex;justify-content:space-between;gap:8px;padding:7px 0;border-bottom:1px dashed #ffe1ec;font-size:14px}
.lfpetv-line:last-child{border-bottom:0}
.lfpetv-buy{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}
.lfpetv-btn{border:0;background:linear-gradient(90deg,#ff3f70,#ff678f);color:#fff;font-weight:700;padding:10px 14px;border-radius:10px;cursor:pointer}
.lfpetv-alert{margin-top:12px;padding:10px;border-radius:10px;background:#fff3f7;border:1px dashed #ffbdd0;color:#8a5362;font-size:13px}
.lfpetv-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #ffd4e2;border-radius:12px;overflow:hidden}
.lfpetv-table th,.lfpetv-table td{padding:9px;font-size:13px;border-bottom:1px solid #ffe1ec;text-align:left}
.lfpetv-table th{background:#fff1f6;color:#a31b4b}
@media (max-width:860px){.lfpetv-grid{grid-template-columns:1fr}}
</style>
<section class="lfpetv-wrap">
    <div class="lfpetv-grid">
        <div>
            <img class="lfpetv-photo" src="<?php echo h(function_exists('love_profile_photo') ? love_profile_photo($pet, intval($pet['usuario_id'])) : user_avatar($pet)); ?>" alt="<?php echo h($pet['name']); ?>">
        </div>
        <div class="lfpetv-card">
            <h1 class="lfpetv-title"><?php echo h($pet['name']); ?></h1>
            <div class="lfpetv-line"><span>Cidade</span><strong><?php echo h($pet['city']); ?></strong></div>
            <div class="lfpetv-line"><span>ID real do usuário</span><strong><?php echo intval($pet['usuario_id']); ?></strong></div>
            <div class="lfpetv-line"><span>ID real do bloco/card</span><strong><?php echo intval($pet['id']); ?></strong></div>
            <div class="lfpetv-line"><span>Valor atual</span><strong><?php echo lf_format_byx($pet['valor_atual']); ?> BYX</strong></div>
            <div class="lfpetv-line"><span>Dono atual</span><strong><?php echo intval($pet['dono_atual_id']) > 0 ? h($pet['dono_nome']) : 'Sem guardião atual'; ?></strong></div>
            <div class="lfpetv-line"><span>Próximo valor de compra</span><strong><?php echo lf_format_byx($proximoValor); ?> BYX</strong></div>

            <div class="lfpetv-buy">
                <?php if ($isOwnProfile): ?>
                    <span class="lfpetv-btn" style="opacity:.7">Você não pode comprar seu próprio perfil</span>
                <?php elseif ($isCurrentOwner): ?>
                    <span class="lfpetv-btn" style="opacity:.7">Você é o dono atual deste Pet</span>
                <?php else: ?>
                    <form action="pet_buy.php" method="post" style="margin:0">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="pet_block_id" value="<?php echo intval($pet['id']); ?>">
                        <button class="lfpetv-btn" type="submit">Comprar este Pet</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="lfpetv-alert">O ID real do perfil nunca muda. Apenas a posse do bloco/card muda dentro do jogo.</div>
        </div>
    </div>

    <h2 style="color:#b41f53;margin:18px 0 8px">Histórico recente</h2>
    <table class="lfpetv-table">
        <thead><tr><th>Data</th><th>Comprador</th><th>Vendedor</th><th>Valor compra</th><th>Novo valor</th></tr></thead>
        <tbody>
        <?php if (!$transacoes): ?>
            <tr><td colspan="5">Sem transações ainda.</td></tr>
        <?php else: ?>
            <?php foreach ($transacoes as $t): ?>
                <tr>
                    <td><?php echo h($t['created_at']); ?></td>
                    <td><?php echo h($t['comprador_nome']); ?></td>
                    <td><?php echo $t['vendedor_nome'] ? h($t['vendedor_nome']) : '-'; ?></td>
                    <td><?php echo lf_format_byx($t['valor_compra']); ?> BYX</td>
                    <td><?php echo lf_format_byx($t['novo_valor']); ?> BYX</td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>
<?php render_footer(); ?>
