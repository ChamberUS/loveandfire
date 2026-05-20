<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
require_once dirname(__FILE__) . '/byx/byx_wallet.php';
require_once dirname(__FILE__) . '/byx/byx_game.php';
$user = auth_require();

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DA ECONOMIA LOVENFIRE
|--------------------------------------------------------------------------
| Ajuste aqui quando quiser mudar a economia:
| 1 Diamante = 0.01000000 BYX
| 1 BYX      = R$ 1,00
*/
$lfDiamondByx = defined('BYX_DIAMOND_RATE') ? (float)BYX_DIAMOND_RATE : 0.01000000;
$lfByxBrl     = defined('BYX_BRL_RATE') ? (float)BYX_BRL_RATE : 1.00;

function lf_format_byx($valor)
{
    return number_format((float)$valor, 8, ',', '.');
}

function lf_format_brl($valor)
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function lf_format_diamantes($valor)
{
    return number_format((float)$valor, 0, ',', '.');
}

function lf_crypto_amount_html($valor)
{
    return '<span class="lfcrypto-value">' . lf_format_byx($valor) . ' BYX</span><small class="lfcrypto-note">valor não reembolsável</small>';
}

function lf_safe_table_name($table)
{
    return preg_match('/^[a-zA-Z0-9_]+$/', $table) ? $table : '';
}

function lf_table_exists($table)
{
    $table = lf_safe_table_name($table);
    if ($table == '') {
        return false;
    }

    $row = db_fetch_one("SHOW TABLES LIKE " . db_escape($table));
    return $row ? true : false;
}

function lf_column_exists($table, $column)
{
    $table = lf_safe_table_name($table);
    if ($table == '') {
        return false;
    }

    if (!lf_table_exists($table)) {
        return false;
    }

    $row = db_fetch_one("SHOW COLUMNS FROM `" . $table . "` LIKE " . db_escape($column));
    return $row ? true : false;
}

function lf_get_value_from_array($row, $fields, $default)
{
    foreach ($fields as $field) {
        if (isset($row[$field]) && $row[$field] !== '') {
            return (float)$row[$field];
        }
    }

    return (float)$default;
}

function lf_pick_users_optout_column()
{
    $candidates = array('pets_optout', 'allow_pets', 'pet_enabled');
    foreach ($candidates as $col) {
        if (lf_column_exists('users', $col)) {
            return $col;
        }
    }
    return '';
}

function lf_is_user_optout($row, $optoutColumn)
{
    if ($optoutColumn === '') {
        return false;
    }

    $value = isset($row[$optoutColumn]) ? intval($row[$optoutColumn]) : 0;
    if ($optoutColumn === 'allow_pets' || $optoutColumn === 'pet_enabled') {
        return $value ? false : true;
    }
    return $value ? true : false;
}

function lf_count_table_rows($table, $whereSql)
{
    if (!lf_table_exists($table)) {
        return 0;
    }
    $row = db_fetch_one('SELECT COUNT(*) AS total FROM `' . $table . '` WHERE ' . $whereSql);
    return $row && isset($row['total']) ? intval($row['total']) : 0;
}

function lf_get_user_wallet($user)
{
    $saldoByx  = 0.00000000;
    $diamantes = 0;
    $saldoApiOk = false;

    $apiSaldo = byx_game_get_balance(intval($user['id']));
    if ($apiSaldo['ok']) {
        $saldoApiOk = true;
        if (isset($apiSaldo['data']['saldo_byx'])) {
            $saldoByx = (float)$apiSaldo['data']['saldo_byx'];
        } elseif (isset($apiSaldo['data']['balance_byx'])) {
            $saldoByx = (float)$apiSaldo['data']['balance_byx'];
        } elseif (isset($apiSaldo['data']['amount_byx'])) {
            $saldoByx = (float)$apiSaldo['data']['amount_byx'];
        }
    }

    $w = byx_wallet_get(intval($user['id']), false);
    if ($w) {
        if (isset($w['diamantes'])) {
            $diamantes = (float)$w['diamantes'];
        }
        if ($saldoByx <= 0 && isset($w['saldo_byx'])) {
            $saldoByx = (float)$w['saldo_byx'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TENTATIVA 1: Tabela lf_wallets
    |--------------------------------------------------------------------------
    | Caso você já tenha uma tabela de carteira, o sistema tenta buscar nela.
    | Campos aceitos:
    | - saldo_byx, byx, saldo, saldo_disponivel
    | - diamantes, diamonds, saldo_diamantes
    */
    if (lf_table_exists('lf_wallets') && lf_column_exists('lf_wallets', 'usuario_id')) {
        $wallet = db_fetch_one("SELECT * FROM lf_wallets WHERE usuario_id = " . intval($user['id']) . " LIMIT 1");

        if ($wallet) {
            $saldoByx = lf_get_value_from_array(
                $wallet,
                array('saldo_byx', 'byx', 'saldo', 'saldo_disponivel'),
                0.00000000
            );

            $diamantes = lf_get_value_from_array(
                $wallet,
                array('diamantes', 'diamonds', 'saldo_diamantes'),
                0
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TENTATIVA 2: Próprio array do usuário logado
    |--------------------------------------------------------------------------
    | Caso o saldo esteja vindo direto da tabela users pelo auth_require().
    */
    if ($saldoByx <= 0) {
        $saldoByx = lf_get_value_from_array(
            $user,
            array('saldo_byx', 'byx', 'saldo', 'saldo_disponivel'),
            $saldoByx
        );
    }

    if ($diamantes <= 0) {
        $diamantes = lf_get_value_from_array(
            $user,
            array('diamantes', 'diamonds', 'saldo_diamantes'),
            $diamantes
        );
    }

    return array(
        'saldo_byx'  => $saldoByx,
        'diamantes' => $diamantes,
        'saldo_api_ok' => $saldoApiOk
    );
}

function lf_pet_ensure_for_user($usuarioId)
{
    $usuarioId = intval($usuarioId);
    $block = db_fetch_one("SELECT * FROM lf_pet_blocks WHERE usuario_id = " . $usuarioId . " AND mundo = 'pets' LIMIT 1");
    $now = now_sql();

    if (!$block) {
        $codigo = 'LF-PET-' . $usuarioId . '-PETS';
        db_query('INSERT INTO lf_pet_blocks (usuario_id, mundo, codigo_publico, valor_inicial, valor_atual, status, created_at, updated_at) VALUES (' . $usuarioId . ', \'pets\', ' . db_escape($codigo) . ', 10.00000000, 10.00000000, \'active\', ' . db_escape($now) . ', ' . db_escape($now) . ')');
        $blockId = db_insert_id();
        $block = db_fetch_one('SELECT * FROM lf_pet_blocks WHERE id = ' . intval($blockId) . ' LIMIT 1');
    }

    $ownership = db_fetch_one('SELECT * FROM lf_pet_ownership WHERE pet_block_id = ' . intval($block['id']) . ' LIMIT 1');
    if (!$ownership) {
        db_query('INSERT INTO lf_pet_ownership (pet_block_id, usuario_original_id, dono_atual_id, valor_atual, ultima_compra_at, status, created_at, updated_at) VALUES (' . intval($block['id']) . ', ' . $usuarioId . ', NULL, ' . db_escape($block['valor_atual']) . ', NULL, \'active\', ' . db_escape($now) . ', \'active\', ' . db_escape($now) . ')');
    }
}

$wallet = lf_get_user_wallet($user);

$saldoAtualByx      = (float)$wallet['saldo_byx'];
$diamantesAtual     = (float)$wallet['diamantes'];
$saldoApiOk         = !empty($wallet['saldo_api_ok']);
$saldoAtualReais    = $saldoAtualByx * $lfByxBrl;
$diamondBrl         = $lfDiamondByx * $lfByxBrl;
$walletMsg = isset($_GET['wallet_msg']) ? trim($_GET['wallet_msg']) : '';
$walletMsgText = '';
$walletMsgClass = '';
if ($walletMsg === 'converted') {
    $walletMsgText = 'Diamantes convertidos em BYX com sucesso. Valor nao reembolsavel.';
    $walletMsgClass = 'ok';
} elseif ($walletMsg === 'insufficient_diamonds') {
    $walletMsgText = 'Voce nao possui diamantes suficientes para essa conversao.';
    $walletMsgClass = 'err';
} elseif ($walletMsg === 'order_created') {
    $walletMsgText = 'Pedido de diamantes criado com sucesso. Aguarde a confirmacao do pagamento.';
    $walletMsgClass = 'ok';
} elseif ($walletMsg === 'invalid_package') {
    $walletMsgText = 'Pacote de diamantes invalido.';
    $walletMsgClass = 'err';
} elseif ($walletMsg === 'error') {
    $walletMsgText = 'Nao foi possivel concluir a operacao. Tente novamente.';
    $walletMsgClass = 'err';
}

$pacotesDiamantes = array(
    array(
        'titulo' => 'Pacote Inicial',
        'qtd' => 100,
        'bonus' => 0
    ),
    array(
        'titulo' => 'Pacote Bronze',
        'qtd' => 500,
        'bonus' => 50
    ),
    array(
        'titulo' => 'Pacote Prata',
        'qtd' => 1000,
        'bonus' => 200
    ),
    array(
        'titulo' => 'Pacote Fogo',
        'qtd' => 5000,
        'bonus' => 1500
    )
);

$usersOptoutColumn = lf_pick_users_optout_column();
$usersSelectOptout = $usersOptoutColumn !== '' ? ', `' . $usersOptoutColumn . '`' : '';
$allUsers = db_fetch_all("SELECT id, name, city, avatar, status, last_seen" . $usersSelectOptout . " FROM users WHERE status = 'active' AND id <> " . intval($user['id']) . " ORDER BY id DESC");

foreach ($allUsers as $u) {
    if (!lf_is_user_optout($u, $usersOptoutColumn)) {
        lf_pet_ensure_for_user($u['id']);
    }
}

$petFilter = isset($_GET['f']) ? trim($_GET['f']) : 'valiosos';
$petOrder = 'pb.valor_atual DESC, pb.id DESC';
if ($petFilter === 'novos') {
    $petOrder = 'pb.id DESC';
} elseif ($petFilter === 'populares') {
    $petOrder = 'pb.valor_atual DESC, pb.id DESC';
} elseif ($petFilter === 'online') {
    $petOrder = 'u.last_seen DESC, pb.id DESC';
}

$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
if ($page < 1) {
    $page = 1;
}
$perPage = 12;
$offset = ($page - 1) * $perPage;

$optoutSqlSelect = $usersOptoutColumn !== '' ? ', u.`' . $usersOptoutColumn . '` AS users_optout' : ', 0 AS users_optout';
$optoutSqlWhere = '';
if ($usersOptoutColumn !== '') {
    if ($usersOptoutColumn === 'allow_pets' || $usersOptoutColumn === 'pet_enabled') {
        $optoutSqlWhere = ' AND u.`' . $usersOptoutColumn . '` = 1 ';
    } else {
        $optoutSqlWhere = ' AND u.`' . $usersOptoutColumn . '` = 0 ';
    }
}

$totalPetsRow = db_fetch_one("SELECT COUNT(*) AS total
FROM lf_pet_blocks pb
INNER JOIN users u ON u.id = pb.usuario_id
WHERE pb.mundo = 'pets' AND pb.status = 'active' AND u.status = 'active' AND u.id <> " . intval($user['id']) . $optoutSqlWhere);
$totalPets = $totalPetsRow && isset($totalPetsRow['total']) ? intval($totalPetsRow['total']) : 0;
$totalPages = $totalPets > 0 ? intval(ceil($totalPets / $perPage)) : 1;
if ($page > $totalPages) {
    $page = $totalPages;
    if ($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * $perPage;
}

$pets = db_fetch_all("SELECT pb.id AS pet_block_id, pb.usuario_id, pb.valor_atual, pb.codigo_publico, po.dono_atual_id, po.ultima_compra_at, u.name, u.city, u.avatar, u.last_seen" . $optoutSqlSelect . ", dono.name AS dono_nome, dono.avatar AS dono_avatar, dono.last_seen AS dono_last_seen
FROM lf_pet_blocks pb
INNER JOIN users u ON u.id = pb.usuario_id
LEFT JOIN lf_pet_ownership po ON po.pet_block_id = pb.id
LEFT JOIN users dono ON dono.id = po.dono_atual_id
WHERE pb.mundo = 'pets' AND pb.status = 'active' AND u.status = 'active' AND u.id <> " . intval($user['id']) . $optoutSqlWhere . "
ORDER BY " . $petOrder . "
LIMIT " . intval($offset) . ", " . intval($perPage));

$countMinhaPosse = lf_count_table_rows('lf_pet_ownership', 'dono_atual_id = ' . intval($user['id']));
$countMinhasDisputas = lf_count_table_rows('lf_pet_transacoes', 'comprador_id = ' . intval($user['id']) . ' OR vendedor_id = ' . intval($user['id']));
$countMeuRanking = 0;
$rankingRows = db_fetch_all("SELECT po.dono_atual_id, COUNT(*) AS total FROM lf_pet_ownership po INNER JOIN lf_pet_blocks pb ON pb.id = po.pet_block_id WHERE pb.status = 'active' GROUP BY po.dono_atual_id ORDER BY total DESC");
if ($rankingRows) {
    $rankPos = 1;
    foreach ($rankingRows as $rr) {
        if (intval($rr['dono_atual_id']) === intval($user['id'])) {
            $countMeuRanking = $rankPos;
            break;
        }
        $rankPos++;
    }
}

$feedItems = array();
if (lf_table_exists('lf_pet_transacoes')) {
    $feedItems = db_fetch_all("SELECT t.id, t.created_at, t.pet_block_id, t.comprador_id, t.vendedor_id, t.novo_valor, cu.name AS comprador_nome, vu.name AS vendedor_nome, pb.usuario_id AS perfil_id, pu.name AS perfil_nome
    FROM lf_pet_transacoes t
    LEFT JOIN users cu ON cu.id = t.comprador_id
    LEFT JOIN users vu ON vu.id = t.vendedor_id
    LEFT JOIN lf_pet_blocks pb ON pb.id = t.pet_block_id
    LEFT JOIN users pu ON pu.id = pb.usuario_id
    ORDER BY t.id DESC
    LIMIT 8");
}

render_header('Jogar Pets', 'pets');
?>
<style>
.lfpet-wrap{
    max-width:1180px;
    margin:0 auto;
    padding:16px;
    background:linear-gradient(145deg,#fff7fb,#fff0f4);
    border:1px solid #ffd7e4;
    border-radius:22px;
    box-shadow:0 16px 35px rgba(242,54,95,.12);
}

.lfpet-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
    margin-bottom:12px;
}
.lfpet-topmenu{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:8px;
}
.lfpet-chip{
    background:#fff;
    border:1px solid #ffc8db;
    color:#b2184f;
    border-radius:999px;
    padding:6px 10px;
    font-size:12px;
    font-weight:700;
}

.lfpet-title{
    margin:0;
    font-size:28px;
    color:#d5275f;
}

.lfpet-sub{
    margin:0;
    color:#7f5362;
}

.lfpet-grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(255px,1fr));
    gap:14px;
}
.lfsocial-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:14px;
    margin-bottom:14px;
}
.lfsocial-card{
    background:#fff;
    border:1px solid #ffd7e4;
    border-radius:16px;
    padding:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}
.lfsocial-stats{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:8px;
}
.lfsocial-stat{
    background:#fff7fb;
    border:1px solid #ffe0eb;
    border-radius:12px;
    padding:10px;
}
.lfsocial-stat b{
    display:block;
    color:#b2184f;
    font-size:18px;
}
.lfsocial-stat span{
    font-size:12px;
    color:#7f5362;
}
.lfsocial-feed-item{
    padding:8px 0;
    border-bottom:1px dashed #ffd9e8;
    font-size:13px;
    color:#6e4352;
}
.lfsocial-feed-item:last-child{
    border-bottom:0;
}
.lfpet-filter{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin:8px 0 14px;
}
.lfpet-filter a{
    text-decoration:none;
}

.lfpet-card{
    background:#fff;
    border:1px solid #ffd7e4;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}

.lfpet-photo{
    width:100%;
    height:210px;
    object-fit:cover;
    display:block;
}

.lfpet-body{
    padding:12px;
}

.lfpet-name{
    margin:0 0 4px;
    font-size:20px;
    color:#b2184f;
}

.lfpet-city{
    margin:0 0 10px;
    color:#7f5362;
    font-size:14px;
}

.lfpet-row{
    display:flex;
    justify-content:space-between;
    gap:8px;
    font-size:13px;
    margin-bottom:8px;
    align-items:flex-start;
}
.lfpet-guardian{
    display:flex;
    align-items:center;
    gap:10px;
    margin:8px 0;
    padding:8px;
    border:1px solid #ffe2ed;
    border-radius:12px;
    background:#fff9fc;
}
.lfpet-guardian img{
    width:38px;
    height:38px;
    border-radius:50%;
    object-fit:cover;
}
.lfpet-meta{
    font-size:12px;
    color:#7f5362;
}

.lfpet-row b{
    color:#4b2d37;
    text-align:right;
}

.lfpet-badge{
    display:inline-block;
    padding:5px 10px;
    border-radius:999px;
    background:#ffe5ee;
    color:#b1174c;
    font-weight:700;
    font-size:12px;
    margin-bottom:8px;
}

.lfpet-actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:12px;
}

.lfpet-btn{
    display:inline-block;
    padding:8px 12px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    font-size:13px;
    cursor:pointer;
    transition:.2s ease;
}

.lfpet-btn:hover{
    transform:translateY(-1px);
    text-decoration:none;
}

.lfpet-btn-view{
    background:#fff;
    border:1px solid #f5a5bd;
    color:#d02862;
}

.lfpet-btn-buy{
    background:linear-gradient(90deg,#ff3f70,#ff6387);
    color:#fff;
    border:0;
}
.lfpet-btn-soft{
    background:#fff;
    border:1px dashed #f0a5bf;
    color:#b2184f;
}

.lfpet-btn-diamond{
    background:linear-gradient(90deg,#7c3cff,#ff3f93);
    color:#fff;
    border:0;
    box-shadow:0 8px 18px rgba(124,60,255,.22);
}

.lfpet-note{
    font-size:13px;
    color:#855767;
    background:#fff3f7;
    border:1px dashed #ffc5d7;
    padding:10px;
    border-radius:12px;
    margin-top:14px;
}

.lfwallet-alert{
    border-radius:12px;
    padding:10px 14px;
    margin:0 0 14px;
    font-weight:600;
}
.lfwallet-alert.ok{
    background:#e9fff1;
    border:1px solid #a9efc4;
    color:#1c6b39;
}
.lfwallet-alert.err{
    background:#fff1f4;
    border:1px solid #ffc8d5;
    color:#982345;
}

.lfwallet-panel{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:12px;
    margin:14px 0 18px;
}

.lfwallet-card{
    background:rgba(255,255,255,.88);
    border:1px solid #ffd6e5;
    border-radius:18px;
    padding:14px;
    box-shadow:0 12px 26px rgba(212,39,95,.08);
    position:relative;
    overflow:hidden;
}

.lfwallet-card:before{
    content:"";
    position:absolute;
    width:110px;
    height:110px;
    border-radius:50%;
    background:radial-gradient(circle,rgba(255,63,112,.18),rgba(255,63,112,0));
    right:-35px;
    top:-35px;
}

.lfwallet-label{
    display:block;
    font-size:12px;
    color:#8a5266;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.04em;
    margin-bottom:6px;
    position:relative;
}

.lfwallet-value{
    font-size:22px;
    font-weight:900;
    color:#b2184f;
    position:relative;
    line-height:1.1;
}

.lfwallet-real{
    font-size:13px;
    color:#62404d;
    margin-top:6px;
    position:relative;
}

.lfwallet-mini{
    font-size:12px;
    color:#8a5266;
    margin-top:6px;
    position:relative;
}

.lfcrypto-value{
    display:block;
    color:#b2184f;
    font-weight:900;
}

.lfcrypto-note{
    display:block;
    margin-top:3px;
    font-size:11px;
    color:#9c526b;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.03em;
}

.lfmodal-diamond .modal-content{
    border:0;
    border-radius:22px;
    overflow:hidden;
    background:linear-gradient(145deg,#fff,#fff3f8);
    box-shadow:0 24px 70px rgba(120,30,80,.25);
}

.lfmodal-diamond .modal-header{
    background:linear-gradient(90deg,#7c3cff,#ff3f93);
    color:#fff;
    border:0;
}

.lfmodal-diamond .modal-title{
    font-weight:900;
}

.lfmodal-diamond .close{
    color:#fff;
    opacity:1;
    text-shadow:none;
}

.lfdiamond-conversion{
    background:#fff;
    border:1px solid #ffd4e3;
    border-radius:16px;
    padding:12px;
    margin-bottom:14px;
}

.lfdiamond-conversion h4{
    margin:0 0 8px;
    font-size:16px;
    color:#b2184f;
    font-weight:900;
}

.lfdiamond-conversion p{
    margin:4px 0;
    color:#654252;
    font-size:13px;
}

.lfdiamond-packages{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
}

.lfdiamond-package{
    background:#fff;
    border:1px solid #ffd4e3;
    border-radius:16px;
    padding:12px;
    box-shadow:0 8px 18px rgba(0,0,0,.04);
}

.lfdiamond-package h5{
    margin:0 0 8px;
    color:#b2184f;
    font-size:17px;
    font-weight:900;
}

.lfdiamond-main{
    font-size:24px;
    font-weight:900;
    color:#7c3cff;
    margin-bottom:5px;
}

.lfdiamond-bonus{
    color:#15945f;
    font-size:13px;
    font-weight:800;
    margin-bottom:8px;
}

.lfdiamond-info{
    font-size:13px;
    color:#60404d;
    margin-bottom:6px;
}

.lfconvert-box{
    margin-top:14px;
    background:#fff8fb;
    border:1px dashed #ffc3d8;
    border-radius:16px;
    padding:12px;
}

.lfconvert-box h4{
    margin:0 0 8px;
    color:#b2184f;
    font-size:16px;
    font-weight:900;
}

.lfconvert-form{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    align-items:center;
}

.lfconvert-form input{
    flex:1;
    min-width:180px;
    border:1px solid #f4aac1;
    border-radius:10px;
    padding:9px 10px;
    outline:none;
}

.lfconvert-form input:focus{
    border-color:#d5275f;
    box-shadow:0 0 0 3px rgba(213,39,95,.12);
}

@media(max-width:900px){
    .lfwallet-panel{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
    .lfsocial-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:575px){
    .lfpet-wrap{
        padding:12px;
        border-radius:16px;
    }

    .lfpet-title{
        font-size:23px;
    }

    .lfwallet-panel,
    .lfdiamond-packages{
        grid-template-columns:1fr;
    }

    .lfwallet-value{
        font-size:20px;
    }
}
</style>

<section class="lfpet-wrap">
    <?php if ($walletMsgText !== ''): ?>
        <div class="lfwallet-alert <?php echo h($walletMsgClass); ?>"><?php echo h($walletMsgText); ?></div>
    <?php endif; ?>
    <div class="lfpet-top">
        <div>
            <h1 class="lfpet-title">LovenPets · Camada Social Gamificada</h1>
            <p class="lfpet-sub">Perfis reais do LovenFire com card social, interação e posse simbólica dentro do jogo.</p>
            <div class="lfpet-topmenu">
                <span class="lfpet-chip">Rede social primeiro</span>
                <span class="lfpet-chip">Perfis reais</span>
                <span class="lfpet-chip">Economia BYX</span>
                <span class="lfpet-chip">Diamante = combustível</span>
            </div>
        </div>

        <div class="lfpet-actions">
            <button type="button" class="lfpet-btn lfpet-btn-diamond" data-toggle="modal" data-target="#modalComprarDiamantes">
                Comprar Diamantes
            </button>
            <a class="lfpet-btn lfpet-btn-view" href="pets_ranking.php">Ver ranking</a>
        </div>
    </div>

    <div class="lfsocial-grid">
        <div class="lfsocial-card">
            <h3 style="margin-top:0;color:#b2184f;">Seu painel social</h3>
            <p class="lfwallet-mini" style="margin-top:0;">No LovenPets, cada card representa um perfil social real do LovenFire. A disputa e a guarda do card sao simbolicas.</p>
            <div class="lfsocial-stats">
                <div class="lfsocial-stat"><b><?php echo intval($countMinhaPosse); ?></b><span>Cards sob sua guarda</span></div>
                <div class="lfsocial-stat"><b><?php echo intval($countMinhasDisputas); ?></b><span>Disputas realizadas</span></div>
                <div class="lfsocial-stat"><b><?php echo $countMeuRanking > 0 ? '#' . intval($countMeuRanking) : '-'; ?></b><span>Ranking social</span></div>
                <div class="lfsocial-stat"><b>Ativo</b><span>Status no LovenPets</span></div>
                <div class="lfsocial-stat"><b>BYX</b><span>Valor simbolico nao reembolsavel</span></div>
                <div class="lfsocial-stat"><b>Social Lover</b><span>Conquista base</span></div>
            </div>
        </div>
        <div class="lfsocial-card">
            <h3 style="margin-top:0;color:#b2184f;">Mini feed</h3>
            <?php if ($feedItems): ?>
                <?php foreach ($feedItems as $fi): ?>
                    <div class="lfsocial-feed-item">
                        <strong><?php echo h($fi['comprador_nome'] ? $fi['comprador_nome'] : 'Jogador'); ?></strong>
                        disputou o card social de
                        <strong><?php echo h($fi['perfil_nome'] ? $fi['perfil_nome'] : 'perfil'); ?></strong>.
                        <div class="lfpet-meta">Valor do card: <?php echo lf_format_byx($fi['novo_valor']); ?> BYX · valor não reembolsável</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="lfsocial-feed-item">As proximas interacoes sociais e disputas aparecerao aqui.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="lfsocial-card" style="margin-bottom:14px;">
        <h3 style="margin-top:0;color:#b2184f;">Conquistas sociais</h3>
        <div class="lfpet-actions" style="margin-top:6px;">
            <span class="lfpet-chip">Perfil Popular</span>
            <span class="lfpet-chip">Guardião Ativo</span>
            <span class="lfpet-chip">Colecionador</span>
            <span class="lfpet-chip">Magnata BYX</span>
            <span class="lfpet-chip">Social Lover</span>
            <span class="lfpet-chip">Card em Alta</span>
        </div>
    </div>

    <div class="lfwallet-panel">
        <div class="lfwallet-card">
            <span class="lfwallet-label">Saldo atual BYX</span>
            <div class="lfwallet-value">
                <?php echo lf_crypto_amount_html($saldoAtualByx); ?>
            </div>
            <?php if (!$saldoApiOk): ?>
                <div class="lfwallet-mini" style="color:#982345;">Saldo BYX indisponível no momento.</div>
            <?php endif; ?>
            <div class="lfwallet-real">Conversão atual: <?php echo lf_format_brl($saldoAtualReais); ?></div>
        </div>

        <div class="lfwallet-card">
            <span class="lfwallet-label">Diamantes atuais</span>
            <div class="lfwallet-value">
                <?php echo lf_format_diamantes($diamantesAtual); ?> 💎
            </div>
            <div class="lfwallet-mini">Pode converter diamantes em BYX dentro do ecossistema.</div>
        </div>

        <div class="lfwallet-card">
            <span class="lfwallet-label">Conversão do diamante</span>
            <div class="lfwallet-value">
                1 💎
            </div>
            <div class="lfwallet-real">
                Equivale a <?php echo lf_crypto_amount_html($lfDiamondByx); ?>
            </div>
        </div>

        <div class="lfwallet-card">
            <span class="lfwallet-label">Valor em reais</span>
            <div class="lfwallet-value">
                <?php echo lf_format_brl($diamondBrl); ?>
            </div>
            <div class="lfwallet-mini">Valor estimado pela taxa atual de conversão.</div>
        </div>
    </div>

    <div class="lfpet-filter">
        <a class="lfpet-btn <?php echo $petFilter === 'populares' ? 'lfpet-btn-diamond' : 'lfpet-btn-view'; ?>" href="pets.php?f=populares">Mais populares</a>
        <a class="lfpet-btn <?php echo $petFilter === 'valiosos' ? 'lfpet-btn-diamond' : 'lfpet-btn-view'; ?>" href="pets.php?f=valiosos">Mais valiosos</a>
        <a class="lfpet-btn <?php echo $petFilter === 'novos' ? 'lfpet-btn-diamond' : 'lfpet-btn-view'; ?>" href="pets.php?f=novos">Novos</a>
        <a class="lfpet-btn <?php echo $petFilter === 'online' ? 'lfpet-btn-diamond' : 'lfpet-btn-view'; ?>" href="pets.php?f=online">Online agora</a>
    </div>

    <div class="lfpet-grid">
        <?php foreach ($pets as $idx => $pet): ?>
            <?php
                $isOwner = intval($pet['dono_atual_id']) === intval($user['id']);
                $avatarFn = function_exists('love_profile_photo') ? love_profile_photo($pet, $idx + 1) : user_avatar($pet);
                $donoAtual = intval($pet['dono_atual_id']) > 0 ? $pet['dono_nome'] : 'Sem guardião atual';
                $donoAvatar = (!empty($pet['dono_avatar']) ? (PUBLIC_UPLOAD_PATH . '/profile_photos/' . $pet['dono_avatar']) : 'assets/img/avatar-placeholder.svg');
                $isOptout = lf_is_user_optout(array($usersOptoutColumn => $pet['users_optout']), $usersOptoutColumn);
                $valorPetReais = ((float)$pet['valor_atual']) * $lfByxBrl;
                $statusOnline = function_exists('love_is_online') && love_is_online($pet);
            ?>
            <article class="lfpet-card">
                <img class="lfpet-photo" src="<?php echo h($avatarFn); ?>" alt="<?php echo h($pet['name']); ?>">
                <div class="lfpet-body">
                    <?php if ($isOwner): ?><span class="lfpet-badge">Sob sua guarda simbólica</span><?php endif; ?>
                    <?php if ($statusOnline): ?><span class="lfpet-badge" style="background:#e9fff1;color:#1f7a44;">Online agora</span><?php endif; ?>

                    <h2 class="lfpet-name"><?php echo h($pet['name']); ?></h2>
                    <p class="lfpet-city"><?php echo h($pet['city']); ?></p>
                    <p class="lfpet-meta">Este card social representa o perfil real de <?php echo h($pet['name']); ?> dentro do mundo LovenPets.</p>

                    <div class="lfpet-row">
                        <span>Valor simbolico do card</span>
                        <b>
                            <?php echo lf_crypto_amount_html($pet['valor_atual']); ?>
                            <span class="lfwallet-real">≈ <?php echo lf_format_brl($valorPetReais); ?></span>
                        </b>
                    </div>

                    <div class="lfpet-guardian">
                        <img src="<?php echo h($donoAvatar); ?>" alt="Guardiao">
                        <div>
                            <div><strong>Guardião atual:</strong> <?php echo h($donoAtual); ?></div>
                            <div class="lfpet-meta">Última disputa: <?php echo $pet['ultima_compra_at'] ? h($pet['ultima_compra_at']) : 'sem disputas recentes'; ?></div>
                        </div>
                    </div>

                    <div class="lfpet-actions">
                        <a class="lfpet-btn lfpet-btn-view" href="#">Ver Perfil</a>
                        <a class="lfpet-btn lfpet-btn-soft" href="#">Seguir</a>
                        <a class="lfpet-btn lfpet-btn-soft" href="#">Curtir</a>
                        <a class="lfpet-btn lfpet-btn-soft" href="#">Compartilhar</a>
                        <a class="lfpet-btn lfpet-btn-soft" href="#">Favoritar</a>
                        <a class="lfpet-btn lfpet-btn-view" href="pet_view.php?id=<?php echo intval($pet['pet_block_id']); ?>">Histórico</a>

                        <?php if ($isOptout): ?>
                            <span class="lfpet-btn lfpet-btn-view" style="opacity:.75">Este usuário optou por não participar do LovenPets.</span>
                        <?php elseif ($isOwner): ?>
                            <span class="lfpet-btn lfpet-btn-buy" style="opacity:.75">Card sob sua guarda</span>
                        <?php else: ?>
                            <form action="pet_buy.php" method="post" style="margin:0">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="pet_block_id" value="<?php echo intval($pet['pet_block_id']); ?>">
                                <button class="lfpet-btn lfpet-btn-buy" type="submit">Disputar Card</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="lfpet-actions" style="justify-content:center;margin-top:16px;">
        <?php if ($page > 1): ?>
            <a class="lfpet-btn lfpet-btn-view" href="pets.php?f=<?php echo h($petFilter); ?>&p=<?php echo intval($page - 1); ?>">Página anterior</a>
        <?php endif; ?>
        <span class="lfpet-btn lfpet-btn-soft" style="cursor:default;">Página <?php echo intval($page); ?> de <?php echo intval($totalPages); ?></span>
        <?php if ($page < $totalPages): ?>
            <a class="lfpet-btn lfpet-btn-view" href="pets.php?f=<?php echo h($petFilter); ?>&p=<?php echo intval($page + 1); ?>">Próxima página</a>
        <?php endif; ?>
    </div>

    <p class="lfpet-note">No LovenFire, a identidade real da pessoa sempre permanece a mesma. O que muda no LovenPets é apenas a posse simbólica do card social no contexto do jogo.</p>
</section>

<div class="modal fade lfmodal-diamond" id="modalComprarDiamantes" tabindex="-1" role="dialog" aria-labelledby="modalComprarDiamantesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalComprarDiamantesLabel">Comprar Diamantes</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="lfdiamond-conversion">
                    <h4>Conversão atual</h4>
                    <p><strong>1 Diamante</strong> equivale a <strong><?php echo lf_format_byx($lfDiamondByx); ?> BYX</strong>.</p>
                    <p><strong>1 BYX</strong> equivale a <strong><?php echo lf_format_brl($lfByxBrl); ?></strong>.</p>
                    <p><strong>1 Diamante</strong> equivale aproximadamente a <strong><?php echo lf_format_brl($diamondBrl); ?></strong>.</p>
                    <small class="lfcrypto-note">Todo valor convertido em BYX é valor não reembolsável.</small>
                </div>

                <div class="lfdiamond-packages">
                    <?php foreach ($pacotesDiamantes as $pacote): ?>
                        <?php
                            $qtdDiamantes = intval($pacote['qtd']);
                            $bonusDiamantes = intval($pacote['bonus']);
                            $totalDiamantes = $qtdDiamantes + $bonusDiamantes;
                            $totalByx = $totalDiamantes * $lfDiamondByx;
                            $totalReais = $totalByx * $lfByxBrl;
                        ?>

                        <div class="lfdiamond-package">
                            <h5><?php echo h($pacote['titulo']); ?></h5>

                            <div class="lfdiamond-main">
                                <?php echo lf_format_diamantes($totalDiamantes); ?> 💎
                            </div>

                            <?php if ($bonusDiamantes > 0): ?>
                                <div class="lfdiamond-bonus">
                                    Inclui bônus de <?php echo lf_format_diamantes($bonusDiamantes); ?> diamantes
                                </div>
                            <?php else: ?>
                                <div class="lfdiamond-bonus">
                                    Pacote sem bônus
                                </div>
                            <?php endif; ?>

                            <div class="lfdiamond-info">
                                Equivalente:
                                <?php echo lf_crypto_amount_html($totalByx); ?>
                            </div>

                            <div class="lfdiamond-info">
                                Valor da conversão em reais:
                                <strong><?php echo lf_format_brl($totalReais); ?></strong>
                            </div>

                            <form action="diamantes_comprar.php" method="post" style="margin-top:10px">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="diamantes" value="<?php echo intval($qtdDiamantes); ?>">
                                <input type="hidden" name="bonus" value="<?php echo intval($bonusDiamantes); ?>">
                                <input type="hidden" name="total_diamantes" value="<?php echo intval($totalDiamantes); ?>">
                                <input type="hidden" name="byx_estimado" value="<?php echo h(number_format($totalByx, 8, '.', '')); ?>">
                                <input type="hidden" name="valor_reais" value="<?php echo h(number_format($totalReais, 2, '.', '')); ?>">

                                <button type="submit" class="lfpet-btn lfpet-btn-diamond">
                                    Comprar pacote
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="lfconvert-box">
                    <h4>Converter Diamantes em BYX</h4>

                    <p class="lfwallet-mini">
                        Saldo atual: <strong><?php echo lf_format_diamantes($diamantesAtual); ?> diamantes</strong>.
                        Cada diamante convertido gera <?php echo lf_crypto_amount_html($lfDiamondByx); ?>
                    </p>

                    <form action="diamantes_converter.php" method="post" class="lfconvert-form">
                        <?php echo csrf_field(); ?>

                        <input 
                            type="number" 
                            name="diamantes" 
                            min="1" 
                            step="1"
                            <?php if ($diamantesAtual > 0): ?>
                                max="<?php echo intval($diamantesAtual); ?>"
                            <?php endif; ?>
                            placeholder="Quantidade de diamantes para converter"
                            required
                        >

                        <button type="submit" class="lfpet-btn lfpet-btn-buy">
                            Converter em BYX
                        </button>
                    </form>

                    <small class="lfcrypto-note">Conversões em BYX são valores não reembolsáveis.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>
