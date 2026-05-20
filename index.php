<?php
/* ============================================================
   LOVE & FIRE - INDEX.PHP
   Versao: 1.1.0
   Modulo: Landing principal / Dois mundos
   Projeto: LovenFire.com
   Padrao: PHP 5.3 + MySQL antigo + core/bootstrap.php
   Observacao: Este arquivo e visual/conceitual. Nao cria tabelas.
============================================================ */

require_once dirname(__FILE__) . '/core/bootstrap.php';

$user = auth_current_user();

render_header('Inicio', 'home');

$primaryHref = $user ? 'dashboard.php' : 'register.php';
$primaryText = $user ? 'Abrir app' : 'Criar conta gratis';

$secondaryHref = $user ? 'chat.php' : 'login.php';
$secondaryText = $user ? 'Minhas mensagens' : 'Entrar';

$petsHref = $user ? 'pets.php' : 'register.php';
$arenaHref = $user ? (is_file(dirname(__FILE__) . '/arena.php') ? 'arena.php' : 'pets_ranking.php') : 'register.php';
$walletHref = $user ? (is_file(dirname(__FILE__) . '/wallet.php') ? 'wallet.php' : '#') : 'register.php';

$profileOne = love_profile_photo(array('id' => 1), 1);
$profileTwo = love_profile_photo(array('id' => 2), 2);
$profileThree = love_profile_photo(array('id' => 3), 3);
$profileFour = love_profile_photo(array('id' => 4), 4);
$profileFive = love_profile_photo(array('id' => 5), 5);
$profileSix = love_profile_photo(array('id' => 6), 6);
?>

<style>
#lfHome2026,
#lfHome2026 * {
    box-sizing: border-box;
}

#lfHome2026 {
    --lf-pink: #f2365f;
    --lf-hot: #ff315f;
    --lf-coral: #ff6b78;
    --lf-soft: #fff7f6;
    --lf-cream: #fffaf8;
    --lf-white: #ffffff;
    --lf-text: #49333b;
    --lf-muted: #8f7d84;
    --lf-muted-2: #aa9ca2;
    --lf-line: rgba(222, 213, 216, 0.92);
    --lf-shadow: 0 32px 80px rgba(129, 89, 98, 0.13);
    --lf-shadow-soft: 0 18px 42px rgba(129, 89, 98, 0.09);
    --lf-radius-xl: 34px;
    --lf-radius-lg: 26px;
    --lf-radius-md: 20px;
    position: relative;
    isolation: isolate;
    overflow: hidden;
    padding: 34px 0 0;
    color: var(--lf-text);
}

#lfHome2026:before {
    content: "";
    position: absolute;
    inset: -160px -120px auto auto;
    width: 540px;
    height: 540px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 49, 95, 0.18), rgba(255, 49, 95, 0) 68%);
    z-index: -1;
    pointer-events: none;
}

#lfHome2026:after {
    content: "";
    position: absolute;
    left: -160px;
    bottom: 12%;
    width: 520px;
    height: 520px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 178, 143, 0.20), rgba(255, 178, 143, 0) 68%);
    z-index: -1;
    pointer-events: none;
}

#lfHome2026 .lf-home-container {
    width: min(1240px, calc(100% - 38px));
    margin: 0 auto;
}

/* ============================================================
   VERSAO 1 - HERO PRINCIPAL LOVE & FIRE
============================================================ */

#lfHome2026 .lf-hero {
    position: relative;
    min-height: 620px;
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
    gap: 56px;
    align-items: center;
    padding: 36px 0 54px;
}

#lfHome2026 .lf-hero-copy {
    position: relative;
    z-index: 2;
}

#lfHome2026 .lf-kicker {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 15px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.86);
    border: 1px solid rgba(255, 49, 95, 0.14);
    box-shadow: 0 12px 26px rgba(129, 89, 98, 0.08);
    color: var(--lf-hot);
    font-size: 13px;
    font-weight: 900;
}

#lfHome2026 .lf-hero h1 {
    margin: 22px 0 14px;
    font-size: clamp(54px, 7vw, 96px);
    line-height: 0.92;
    letter-spacing: -3px;
    color: var(--lf-pink);
    font-weight: 950;
    text-shadow: 0 14px 30px rgba(242, 54, 95, 0.13);
}

#lfHome2026 .lf-hero-lead {
    max-width: 610px;
    margin: 0;
    color: #7e6870;
    font-size: 18px;
    line-height: 1.75;
    font-weight: 650;
}

#lfHome2026 .lf-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin: 30px 0 22px;
}

#lfHome2026 .lf-btn {
    min-height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 0 22px;
    border-radius: 999px;
    text-decoration: none !important;
    font-weight: 950;
    font-size: 14px;
    transition: all .24s ease;
}

#lfHome2026 .lf-btn-primary {
    color: #fff;
    background: linear-gradient(135deg, var(--lf-hot), #ff735f);
    box-shadow: 0 18px 35px rgba(255, 49, 95, 0.25);
}

#lfHome2026 .lf-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 24px 46px rgba(255, 49, 95, 0.32);
}

#lfHome2026 .lf-btn-ghost {
    color: var(--lf-hot);
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(255, 49, 95, 0.12);
    box-shadow: 0 16px 32px rgba(129, 89, 98, 0.08);
}

#lfHome2026 .lf-btn-ghost:hover {
    transform: translateY(-2px);
    background: #fff;
}

#lfHome2026 .lf-btn-dark {
    color: #fff;
    background: linear-gradient(135deg, #30202a, #5b3443);
    box-shadow: 0 18px 35px rgba(48, 32, 42, 0.20);
}

#lfHome2026 .lf-btn-dark:hover {
    transform: translateY(-2px);
    box-shadow: 0 24px 46px rgba(48, 32, 42, 0.26);
}

#lfHome2026 .lf-trust-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 26px;
}

#lfHome2026 .lf-trust-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 13px;
    border-radius: 999px;
    background: rgba(255,255,255,0.76);
    border: 1px solid rgba(226, 217, 220, 0.86);
    color: #826f76;
    font-size: 12px;
    font-weight: 900;
    box-shadow: 0 10px 22px rgba(129, 89, 98, 0.06);
}

#lfHome2026 .lf-hero-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 30px;
    max-width: 610px;
}

#lfHome2026 .lf-metric {
    padding: 18px 16px;
    border-radius: 24px;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: 0 16px 34px rgba(129, 89, 98, 0.07);
}

#lfHome2026 .lf-metric strong {
    display: block;
    color: #382931;
    font-size: 23px;
    line-height: 1;
    font-weight: 950;
}

#lfHome2026 .lf-metric span {
    display: block;
    margin-top: 8px;
    color: #917e85;
    font-size: 12px;
    font-weight: 850;
    line-height: 1.35;
}

/* ============================================================
   VERSAO 1.1 - CELULAR DEMO / PERFIS
============================================================ */

#lfHome2026 .lf-showcase {
    position: relative;
    min-height: 560px;
}

#lfHome2026 .lf-phone-stage {
    position: relative;
    width: min(390px, 100%);
    margin: 0 auto;
}

#lfHome2026 .lf-phone-glow {
    position: absolute;
    inset: 38px -38px -38px;
    border-radius: 60px;
    background: radial-gradient(circle at 50% 30%, rgba(255, 49, 95, 0.22), rgba(255, 49, 95, 0) 64%);
    filter: blur(8px);
    z-index: 0;
}

#lfHome2026 .lf-phone {
    position: relative;
    z-index: 2;
    width: 100%;
    border-radius: 44px;
    padding: 18px;
    background:
        radial-gradient(circle at 15% 0%, rgba(255,255,255,0.98), rgba(255,255,255,0) 45%),
        linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,250,249,0.96));
    border: 1px solid rgba(255,255,255,0.95);
    box-shadow:
        0 34px 90px rgba(101, 73, 83, 0.20),
        inset 0 1px 0 rgba(255,255,255,1);
}

#lfHome2026 .lf-phone-logo {
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--lf-pink);
    font-size: 28px;
    font-weight: 950;
    letter-spacing: -1px;
}

#lfHome2026 .lf-phone-logo small {
    display: block;
    font-size: 9px;
    letter-spacing: 1.2px;
    color: #7b9ab2;
    text-transform: uppercase;
    margin-top: 2px;
}

#lfHome2026 .lf-search {
    height: 44px;
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 0 15px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(225, 214, 218, 0.92);
    color: #a09198;
    font-size: 12px;
    font-weight: 800;
    box-shadow: inset 0 1px 0 rgba(255,255,255,1);
}

#lfHome2026 .lf-profile-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 11px;
    margin-top: 12px;
}

#lfHome2026 .lf-profile-card {
    position: relative;
    height: 175px;
    border-radius: 22px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
    box-shadow: 0 16px 28px rgba(41, 29, 35, 0.14);
}

#lfHome2026 .lf-profile-card:before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(0,0,0,0) 42%, rgba(0,0,0,0.70) 100%),
        radial-gradient(circle at 80% 12%, rgba(255,255,255,0.26), rgba(255,255,255,0) 34%);
}

#lfHome2026 .lf-profile-card span {
    position: absolute;
    left: 13px;
    right: 13px;
    bottom: 12px;
    color: #fff;
    font-size: 15px;
    font-weight: 950;
    text-shadow: 0 3px 8px rgba(0,0,0,0.28);
}

#lfHome2026 .lf-card-tags {
    position: absolute;
    left: 10px;
    top: 10px;
    display: flex;
    gap: 6px;
}

#lfHome2026 .lf-card-tags b {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 23px;
    padding: 0 8px;
    border-radius: 999px;
    background: rgba(255,255,255,0.88);
    color: var(--lf-hot);
    font-size: 10px;
    font-weight: 950;
}

#lfHome2026 .lf-demo-nav {
    display: flex;
    align-items: center;
    justify-content: space-around;
    margin: 16px 12px 0;
    padding: 13px 10px;
    border-radius: 24px;
    background: rgba(255,255,255,0.82);
    border: 1px solid rgba(226, 217, 220, 0.72);
    box-shadow: 0 16px 34px rgba(129, 89, 98, 0.08);
    color: var(--lf-hot);
    font-size: 17px;
}

#lfHome2026 .lf-floating-card {
    position: absolute;
    z-index: 3;
    width: 218px;
    padding: 15px;
    border-radius: 24px;
    background: rgba(255,255,255,0.86);
    border: 1px solid rgba(255,255,255,0.88);
    box-shadow: 0 22px 48px rgba(101, 73, 83, 0.16);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

#lfHome2026 .lf-floating-card strong {
    display: block;
    color: #3d2d34;
    font-size: 14px;
    font-weight: 950;
}

#lfHome2026 .lf-floating-card span {
    display: block;
    margin-top: 6px;
    color: #8b7b82;
    font-size: 12px;
    line-height: 1.45;
    font-weight: 750;
}

#lfHome2026 .lf-floating-card.one {
    left: -70px;
    top: 74px;
}

#lfHome2026 .lf-floating-card.two {
    right: -82px;
    bottom: 86px;
}

/* ============================================================
   VERSAO 2 - BLOCOS GERAIS
============================================================ */

#lfHome2026 .lf-section {
    margin: 22px 0;
}

#lfHome2026 .lf-section-head {
    max-width: 760px;
    margin: 0 auto 28px;
    text-align: center;
}

#lfHome2026 .lf-section-head .lf-kicker {
    margin-bottom: 14px;
}

#lfHome2026 .lf-section-head h2 {
    margin: 0;
    color: #382931;
    font-size: clamp(30px, 4vw, 52px);
    line-height: 1.02;
    letter-spacing: -1.5px;
    font-weight: 950;
}

#lfHome2026 .lf-section-head p {
    max-width: 660px;
    margin: 14px auto 0;
    color: #826f76;
    font-size: 16px;
    line-height: 1.7;
    font-weight: 650;
}

#lfHome2026 .lf-feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

#lfHome2026 .lf-feature {
    min-height: 178px;
    padding: 24px;
    border-radius: 28px;
    background:
        radial-gradient(circle at 100% 0%, rgba(255,49,95,0.08), rgba(255,255,255,0) 42%),
        rgba(255,255,255,0.78);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: 0 18px 40px rgba(129, 89, 98, 0.07);
}

#lfHome2026 .lf-feature-icon {
    width: 46px;
    height: 46px;
    border-radius: 17px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,49,95,0.08);
    color: var(--lf-hot);
    font-size: 22px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.9);
}

#lfHome2026 .lf-feature h3 {
    margin: 18px 0 8px;
    color: #4d3740;
    font-size: 18px;
    font-weight: 950;
}

#lfHome2026 .lf-feature p {
    margin: 0;
    color: #8d7c83;
    font-size: 14px;
    line-height: 1.65;
    font-weight: 650;
}

/* ============================================================
   VERSAO 3 - DOIS MUNDOS: LOVENPETS + ARENA
============================================================ */

#lfHome2026 .lf-worlds-wrap {
    position: relative;
    padding: 18px;
    border-radius: 38px;
    background:
        radial-gradient(circle at 10% 20%, rgba(255,49,95,0.10), rgba(255,255,255,0) 34%),
        radial-gradient(circle at 90% 0%, rgba(255,146,43,0.12), rgba(255,255,255,0) 36%),
        linear-gradient(135deg, rgba(255,255,255,0.86), rgba(255,250,249,0.96));
    border: 1px solid rgba(226, 217, 220, 0.88);
    box-shadow: var(--lf-shadow);
    overflow: hidden;
}

#lfHome2026 .lf-worlds-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

#lfHome2026 .lf-world-card {
    position: relative;
    min-height: 420px;
    padding: 34px;
    border-radius: 30px;
    overflow: hidden;
    color: #fff;
    box-shadow: 0 24px 60px rgba(91, 52, 67, 0.14);
}

#lfHome2026 .lf-world-card:before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    background:
        radial-gradient(circle at 100% 0%, rgba(255,255,255,0.34), rgba(255,255,255,0) 42%),
        radial-gradient(circle at 0% 100%, rgba(255,255,255,0.16), rgba(255,255,255,0) 48%);
}

#lfHome2026 .lf-world-pets {
    background: linear-gradient(135deg, rgba(255,49,95,0.96), rgba(255,119,95,0.94));
}

#lfHome2026 .lf-world-arena {
    background: linear-gradient(135deg, rgba(52,34,45,0.98), rgba(131,67,84,0.94));
}

#lfHome2026 .lf-world-content {
    position: relative;
    z-index: 2;
}

#lfHome2026 .lf-world-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.17);
    border: 1px solid rgba(255,255,255,0.22);
    color: #fff;
    font-size: 12px;
    font-weight: 950;
}

#lfHome2026 .lf-world-card h2 {
    margin: 18px 0 12px;
    font-size: clamp(31px, 4vw, 52px);
    line-height: 1;
    letter-spacing: -1.5px;
    font-weight: 950;
}

#lfHome2026 .lf-world-card p {
    max-width: 500px;
    margin: 0;
    color: rgba(255,255,255,0.88);
    font-size: 15.5px;
    line-height: 1.75;
    font-weight: 650;
}

#lfHome2026 .lf-world-list {
    margin: 24px 0 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 10px;
}

#lfHome2026 .lf-world-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    color: rgba(255,255,255,0.92);
    font-size: 13px;
    line-height: 1.45;
    font-weight: 850;
}

#lfHome2026 .lf-world-list i {
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    border-radius: 50%;
    font-style: normal;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.16);
    border: 1px solid rgba(255,255,255,0.18);
}

#lfHome2026 .lf-world-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 28px;
}

#lfHome2026 .lf-world-actions .lf-btn {
    min-height: 48px;
    background: rgba(255,255,255,0.16);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.22);
    box-shadow: none;
}

#lfHome2026 .lf-world-actions .lf-btn:hover {
    background: rgba(255,255,255,0.24);
}

#lfHome2026 .lf-economy-strip {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

#lfHome2026 .lf-economy-card {
    min-height: 132px;
    padding: 18px;
    border-radius: 24px;
    background: rgba(255,255,255,0.80);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: 0 18px 38px rgba(129, 89, 98, 0.07);
}

#lfHome2026 .lf-economy-card strong {
    display: block;
    color: #3d2d34;
    font-size: 15px;
    font-weight: 950;
}

#lfHome2026 .lf-economy-card span {
    display: block;
    margin-top: 8px;
    color: #8d7c83;
    font-size: 12.5px;
    line-height: 1.55;
    font-weight: 700;
}

/* ============================================================
   VERSAO 4 - FLECHAS DO CUPIDO / BYX COIN
============================================================ */

#lfHome2026 .lf-cupid {
    position: relative;
    display: grid;
    grid-template-columns: 0.92fr 1.08fr;
    gap: 18px;
    align-items: stretch;
    padding: 18px;
    border-radius: 36px;
    background:
        radial-gradient(circle at 10% 20%, rgba(255,49,95,0.12), rgba(255,255,255,0) 34%),
        linear-gradient(135deg, rgba(255,255,255,0.86), rgba(255,250,249,0.96));
    border: 1px solid rgba(226, 217, 220, 0.88);
    box-shadow: var(--lf-shadow);
    overflow: hidden;
}

#lfHome2026 .lf-cupid-main {
    padding: 34px;
    border-radius: 28px;
    background:
        radial-gradient(circle at 100% 0%, rgba(255,255,255,0.72), rgba(255,255,255,0) 40%),
        linear-gradient(135deg, rgba(255,49,95,0.95), rgba(255,119,95,0.92));
    color: #fff;
    min-height: 330px;
}

#lfHome2026 .lf-cupid-main h2 {
    margin: 18px 0 12px;
    font-size: clamp(31px, 4vw, 52px);
    line-height: 1;
    letter-spacing: -1.5px;
    font-weight: 950;
}

#lfHome2026 .lf-cupid-main p {
    max-width: 470px;
    margin: 0;
    color: rgba(255,255,255,0.88);
    font-size: 16px;
    line-height: 1.75;
    font-weight: 650;
}

#lfHome2026 .lf-wallet-box {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 26px;
}

#lfHome2026 .lf-wallet-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.24);
    color: #fff;
    font-size: 13px;
    font-weight: 900;
}

#lfHome2026 .lf-arrow-plans {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

#lfHome2026 .lf-arrow-card {
    padding: 22px;
    border-radius: 26px;
    background: rgba(255,255,255,0.82);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: 0 18px 38px rgba(129, 89, 98, 0.07);
}

#lfHome2026 .lf-arrow-card b {
    display: inline-flex;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: rgba(255,49,95,0.08);
    color: var(--lf-hot);
    font-size: 22px;
}

#lfHome2026 .lf-arrow-card h3 {
    margin: 16px 0 8px;
    color: #4d3740;
    font-size: 17px;
    font-weight: 950;
}

#lfHome2026 .lf-arrow-card p {
    margin: 0;
    color: #8d7c83;
    font-size: 13.5px;
    line-height: 1.55;
    font-weight: 650;
}

/* ============================================================
   VERSAO 5 - COMO FUNCIONA
============================================================ */

#lfHome2026 .lf-steps {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
}

#lfHome2026 .lf-step {
    position: relative;
    min-height: 162px;
    padding: 22px 18px;
    border-radius: 28px;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: 0 18px 38px rgba(129, 89, 98, 0.07);
}

#lfHome2026 .lf-step-number {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--lf-hot);
    color: #fff;
    font-size: 13px;
    font-weight: 950;
    box-shadow: 0 12px 24px rgba(255, 49, 95, 0.20);
}

#lfHome2026 .lf-step h3 {
    margin: 18px 0 8px;
    color: #4d3740;
    font-size: 16px;
    font-weight: 950;
}

#lfHome2026 .lf-step p {
    margin: 0;
    color: #8d7c83;
    font-size: 13px;
    line-height: 1.55;
    font-weight: 650;
}

/* ============================================================
   VERSAO 6 - PREVIEW CHAT / DESCOBERTA
============================================================ */

#lfHome2026 .lf-preview-row {
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    gap: 18px;
    align-items: stretch;
}

#lfHome2026 .lf-chat-card,
#lfHome2026 .lf-discover-card {
    min-height: 500px;
    border-radius: 36px;
    background: rgba(255,255,255,0.82);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: var(--lf-shadow);
    overflow: hidden;
}

#lfHome2026 .lf-chat-header {
    min-height: 82px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(226, 217, 220, 0.80);
    background: rgba(255,255,255,0.74);
}

#lfHome2026 .lf-chat-person {
    display: flex;
    align-items: center;
    gap: 12px;
}

#lfHome2026 .lf-chat-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 10px 22px rgba(0,0,0,0.12);
    border: 2px solid #fff;
}

#lfHome2026 .lf-chat-person strong {
    display: block;
    color: #35272e;
    font-size: 16px;
    font-weight: 950;
}

#lfHome2026 .lf-chat-person span {
    display: block;
    margin-top: 2px;
    color: #29b873;
    font-size: 12px;
    font-weight: 850;
}

#lfHome2026 .lf-chat-actions {
    display: flex;
    gap: 8px;
}

#lfHome2026 .lf-chat-actions button {
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 50%;
    background: #fff;
    color: #49333b;
    box-shadow: 0 12px 24px rgba(129, 89, 98, 0.08);
}

#lfHome2026 .lf-chat-body {
    padding: 24px;
}

#lfHome2026 .lf-date-line {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #9a8c92;
    font-size: 12px;
    font-weight: 900;
    margin-bottom: 22px;
}

#lfHome2026 .lf-date-line:before,
#lfHome2026 .lf-date-line:after {
    content: "";
    height: 1px;
    background: rgba(226, 217, 220, 0.9);
    flex: 1;
}

#lfHome2026 .lf-message {
    display: flex;
    margin: 10px 0;
}

#lfHome2026 .lf-message.right {
    justify-content: flex-end;
}

#lfHome2026 .lf-bubble {
    max-width: 72%;
    padding: 12px 15px;
    border-radius: 19px 19px 19px 8px;
    background: linear-gradient(180deg, #fff, #f4f6f8);
    border: 1px solid rgba(210,216,223,0.92);
    color: #3f444b;
    font-size: 14px;
    line-height: 1.42;
    font-weight: 650;
    box-shadow: 0 14px 28px rgba(83,90,102,0.07);
}

#lfHome2026 .lf-message.right .lf-bubble {
    border-radius: 19px 19px 8px 19px;
    background: linear-gradient(180deg, #f9fafb, #edf0f3);
}

#lfHome2026 .lf-bubble small {
    display: inline-block;
    margin-left: 8px;
    color: #9299a2;
    font-size: 10px;
    font-weight: 850;
}

#lfHome2026 .lf-typing-preview {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-top: 18px;
    padding: 8px 13px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(226, 217, 220, 0.88);
    color: #887980;
    font-size: 12px;
    font-weight: 900;
    box-shadow: 0 12px 24px rgba(129, 89, 98, 0.08);
}

#lfHome2026 .lf-chat-input-preview {
    min-height: 64px;
    margin: 22px 24px 0;
    padding: 8px 8px 8px 18px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(226, 217, 220, 0.88);
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #9a8c92;
    font-size: 14px;
    font-weight: 800;
}

#lfHome2026 .lf-send-preview {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #000;
    color: #fff;
    font-size: 24px;
    box-shadow: 0 16px 30px rgba(0,0,0,0.22);
}

#lfHome2026 .lf-discover-card {
    padding: 22px;
}

#lfHome2026 .lf-discover-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

#lfHome2026 .lf-discover-profile {
    position: relative;
    height: 230px;
    border-radius: 26px;
    overflow: hidden;
    background-size: cover;
    background-position: center;
    box-shadow: 0 20px 40px rgba(41, 29, 35, 0.14);
}

#lfHome2026 .lf-discover-profile:before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, rgba(0,0,0,0) 38%, rgba(0,0,0,0.72) 100%),
        radial-gradient(circle at 80% 8%, rgba(255,255,255,0.26), rgba(255,255,255,0) 34%);
}

#lfHome2026 .lf-discover-profile .lf-profile-info {
    position: absolute;
    left: 14px;
    right: 14px;
    bottom: 14px;
    color: #fff;
}

#lfHome2026 .lf-discover-profile strong {
    display: block;
    font-size: 16px;
    font-weight: 950;
}

#lfHome2026 .lf-discover-profile span {
    display: inline-flex;
    margin-top: 7px;
    min-height: 24px;
    padding: 0 8px;
    align-items: center;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.20);
    font-size: 11px;
    font-weight: 850;
}

#lfHome2026 .lf-profile-actions {
    display: flex;
    gap: 10px;
    margin-top: 16px;
}

#lfHome2026 .lf-profile-actions button {
    flex: 1;
    min-height: 46px;
    border: 0;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 950;
    cursor: default;
}

#lfHome2026 .lf-profile-actions .pass {
    background: #fff;
    color: #8d7c83;
    border: 1px solid rgba(226, 217, 220, 0.9);
}

#lfHome2026 .lf-profile-actions .like {
    background: var(--lf-hot);
    color: #fff;
    box-shadow: 0 16px 30px rgba(255, 49, 95, 0.22);
}

/* ============================================================
   VERSAO 7 - SEGURANCA
============================================================ */

#lfHome2026 .lf-safety-grid {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 18px;
}

#lfHome2026 .lf-safety-main,
#lfHome2026 .lf-safety-list {
    border-radius: 34px;
    background: rgba(255,255,255,0.80);
    border: 1px solid rgba(226, 217, 220, 0.86);
    box-shadow: var(--lf-shadow-soft);
}

#lfHome2026 .lf-safety-main {
    padding: 34px;
}

#lfHome2026 .lf-safety-main h2 {
    margin: 0;
    color: #382931;
    font-size: clamp(30px, 4vw, 50px);
    line-height: 1.02;
    letter-spacing: -1.2px;
    font-weight: 950;
}

#lfHome2026 .lf-safety-main p {
    margin: 16px 0 0;
    color: #826f76;
    font-size: 16px;
    line-height: 1.75;
    font-weight: 650;
}

#lfHome2026 .lf-safety-list {
    padding: 22px;
}

#lfHome2026 .lf-safe-item {
    display: flex;
    gap: 14px;
    padding: 16px 0;
    border-bottom: 1px solid rgba(226, 217, 220, 0.70);
}

#lfHome2026 .lf-safe-item:last-child {
    border-bottom: 0;
}

#lfHome2026 .lf-safe-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,49,95,0.08);
    color: var(--lf-hot);
    font-size: 20px;
}

#lfHome2026 .lf-safe-item strong {
    display: block;
    color: #4d3740;
    font-size: 15px;
    font-weight: 950;
}

#lfHome2026 .lf-safe-item span {
    display: block;
    margin-top: 4px;
    color: #8d7c83;
    font-size: 13px;
    line-height: 1.45;
    font-weight: 650;
}

/* ============================================================
   VERSAO 8 - FINAL CTA
============================================================ */

#lfHome2026 .lf-final {
    margin: 24px 0 0;
    padding: 46px 28px;
    border-radius: 38px;
    text-align: center;
    background:
        radial-gradient(circle at 50% 0%, rgba(255,255,255,0.42), rgba(255,255,255,0) 42%),
        linear-gradient(135deg, rgba(255,49,95,0.95), rgba(255,119,95,0.92));
    color: #fff;
    box-shadow: var(--lf-shadow);
}

#lfHome2026 .lf-final h2 {
    margin: 0;
    font-size: clamp(34px, 5vw, 66px);
    line-height: 0.98;
    letter-spacing: -2px;
    font-weight: 950;
}

#lfHome2026 .lf-final p {
    max-width: 640px;
    margin: 16px auto 26px;
    color: rgba(255,255,255,0.88);
    font-size: 16px;
    line-height: 1.7;
    font-weight: 650;
}

#lfHome2026 .lf-final .lf-btn-ghost {
    color: #fff;
    background: rgba(255,255,255,0.16);
    border-color: rgba(255,255,255,0.24);
}

#lfHome2026 .lf-mobile-cta {
    display: none;
}

/* ============================================================
   VERSAO 9 - RESPONSIVO
============================================================ */

@media (max-width: 1180px) {
    #lfHome2026 .lf-hero {
        grid-template-columns: 1fr;
        text-align: center;
        min-height: auto;
        padding-top: 24px;
    }

    #lfHome2026 .lf-hero-copy {
        max-width: 780px;
        margin: 0 auto;
    }

    #lfHome2026 .lf-hero-lead,
    #lfHome2026 .lf-hero-metrics {
        margin-left: auto;
        margin-right: auto;
    }

    #lfHome2026 .lf-actions,
    #lfHome2026 .lf-trust-row {
        justify-content: center;
    }

    #lfHome2026 .lf-floating-card.one {
        left: 0;
    }

    #lfHome2026 .lf-floating-card.two {
        right: 0;
    }

    #lfHome2026 .lf-cupid,
    #lfHome2026 .lf-preview-row,
    #lfHome2026 .lf-safety-grid,
    #lfHome2026 .lf-worlds-grid {
        grid-template-columns: 1fr;
    }

    #lfHome2026 .lf-steps {
        grid-template-columns: repeat(3, 1fr);
    }

    #lfHome2026 .lf-economy-strip {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 860px) {
    #lfHome2026 {
        padding-top: 12px;
    }

    #lfHome2026 .lf-home-container {
        width: min(100% - 24px, 720px);
    }

    #lfHome2026 .lf-hero {
        gap: 34px;
    }

    #lfHome2026 .lf-hero h1 {
        font-size: clamp(48px, 15vw, 70px);
        letter-spacing: -2px;
    }

    #lfHome2026 .lf-hero-lead {
        font-size: 15.5px;
        line-height: 1.65;
    }

    #lfHome2026 .lf-hero-metrics,
    #lfHome2026 .lf-feature-grid,
    #lfHome2026 .lf-arrow-plans,
    #lfHome2026 .lf-steps,
    #lfHome2026 .lf-economy-strip {
        grid-template-columns: 1fr;
    }

    #lfHome2026 .lf-feature,
    #lfHome2026 .lf-step,
    #lfHome2026 .lf-arrow-card {
        min-height: auto;
    }

    #lfHome2026 .lf-world-card {
        min-height: auto;
        padding: 28px 22px;
    }

    #lfHome2026 .lf-floating-card {
        position: static;
        width: 100%;
        margin-top: 12px;
    }

    #lfHome2026 .lf-phone-stage {
        max-width: 360px;
    }

    #lfHome2026 .lf-profile-card {
        height: 158px;
    }

    #lfHome2026 .lf-cupid-main {
        padding: 28px 22px;
        min-height: auto;
    }

    #lfHome2026 .lf-discover-grid {
        grid-template-columns: 1fr;
    }

    #lfHome2026 .lf-discover-profile {
        height: 260px;
    }

    #lfHome2026 .lf-chat-card,
    #lfHome2026 .lf-discover-card {
        min-height: auto;
    }

    #lfHome2026 .lf-bubble {
        max-width: 86%;
    }

    #lfHome2026 .lf-actions .lf-btn {
        flex: 1 1 auto;
    }

    #lfHome2026 .lf-mobile-cta {
        position: sticky;
        bottom: 14px;
        z-index: 30;
        display: flex;
        gap: 10px;
        padding: 10px;
        margin: 16px auto 0;
        border-radius: 999px;
        background: rgba(255,255,255,0.80);
        border: 1px solid rgba(226, 217, 220, 0.80);
        box-shadow: 0 18px 40px rgba(129, 89, 98, 0.15);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    #lfHome2026 .lf-mobile-cta .lf-btn {
        flex: 1;
        min-height: 48px;
        padding: 0 12px;
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    #lfHome2026 .lf-profile-grid {
        gap: 9px;
    }

    #lfHome2026 .lf-profile-card {
        height: 146px;
        border-radius: 19px;
    }

    #lfHome2026 .lf-phone {
        padding: 14px;
        border-radius: 36px;
    }

    #lfHome2026 .lf-section-head h2,
    #lfHome2026 .lf-safety-main h2 {
        letter-spacing: -0.8px;
    }

    #lfHome2026 .lf-chat-header {
        padding: 14px;
    }

    #lfHome2026 .lf-chat-actions button {
        width: 36px;
        height: 36px;
    }

    #lfHome2026 .lf-chat-body,
    #lfHome2026 .lf-discover-card,
    #lfHome2026 .lf-safety-main,
    #lfHome2026 .lf-safety-list {
        padding: 18px;
    }

    #lfHome2026 .lf-worlds-wrap {
        padding: 12px;
        border-radius: 30px;
    }
}
</style>

<main id="lfHome2026">
    <div class="lf-home-container">

        <!-- ============================================================
             VERSAO 1 - HERO PRINCIPAL LOVE & FIRE
        ============================================================= -->
        <section class="lf-hero">
            <div class="lf-hero-copy">
                <span class="lf-kicker">🔥 App de namoro humano · jogo social · BYX Coin</span>

                <h1>Love & Fire</h1>

                <p class="lf-hero-lead">
                    Um ecossistema de relacionamento premium para descobrir pessoas, conversar com leveza,
                    criar matches reais e entrar em dois mundos: LovenPets para paquera e LovenFire Arena
                    para jogo, ranking e BYX Coin.
                </p>

                <div class="lf-actions">
                    <a class="lf-btn lf-btn-primary" href="<?php echo h($primaryHref); ?>">
                        <?php echo h($primaryText); ?> 🔥
                    </a>

                    <a class="lf-btn lf-btn-ghost" href="<?php echo h($petsHref); ?>">
                        Jogar LovenPets 💘
                    </a>

                    <a class="lf-btn lf-btn-dark" href="<?php echo h($arenaHref); ?>">
                        LovenFire Arena 🪙
                    </a>
                </div>

                <div class="lf-trust-row">
                    <span class="lf-trust-chip">💘 Matches e DMs livres</span>
                    <span class="lf-trust-chip">🐾 LovenPets para paquera</span>
                    <span class="lf-trust-chip">🏆 Arena com ranking</span>
                    <span class="lf-trust-chip">🪙 Wallet BYX Coin em breve</span>
                </div>

                <div class="lf-hero-metrics">
                    <div class="lf-metric">
                        <strong>2026</strong>
                        <span>Design de app moderno, responsivo e premium.</span>
                    </div>

                    <div class="lf-metric">
                        <strong>2 mundos</strong>
                        <span>Paquera social e arena competitiva separados.</span>
                    </div>

                    <div class="lf-metric">
                        <strong>BYX</strong>
                        <span>BYX Coin reservada para cards, ranking e economia da Arena.</span>
                    </div>
                </div>
            </div>

            <div class="lf-showcase">
                <div class="lf-phone-stage">
                    <div class="lf-phone-glow"></div>

                    <div class="lf-floating-card one">
                        <strong>💬 Chat acolhedor</strong>
                        <span>Status online, digitando, fotos no chat e mensagens com visual premium.</span>
                    </div>

                    <div class="lf-floating-card two">
                        <strong>🐾 LovenPets</strong>
                        <span>Adote, dispute, valorize perfis e transforme paquera em jogo social.</span>
                    </div>

                    <div class="lf-phone">
                        <div class="lf-phone-logo">
                            <div>
                                Love & Fire
                                <small>Powered by Buynnex</small>
                            </div>
                        </div>

                        <div class="lf-search">
                            <span>🔍</span>
                            <span>Buscar por nome ou interesse...</span>
                        </div>

                        <div class="lf-profile-grid">
                            <div class="lf-profile-card" style="background-image:url('<?php echo h($profileOne); ?>');">
                                <div class="lf-card-tags"><b>Online</b></div>
                                <span>Juliana, 27</span>
                            </div>

                            <div class="lf-profile-card" style="background-image:url('<?php echo h($profileTwo); ?>');">
                                <div class="lf-card-tags"><b>Match</b></div>
                                <span>Gabriel, 29</span>
                            </div>

                            <div class="lf-profile-card" style="background-image:url('<?php echo h($profileThree); ?>');">
                                <div class="lf-card-tags"><b>Pet</b></div>
                                <span>Camila, 25</span>
                            </div>

                            <div class="lf-profile-card" style="background-image:url('<?php echo h($profileFour); ?>');">
                                <div class="lf-card-tags"><b>BYX</b></div>
                                <span>Lucas, 31</span>
                            </div>
                        </div>

                        <div class="lf-demo-nav">
                            <span>🔥</span>
                            <span>♡</span>
                            <span>🐾</span>
                            <span>💬</span>
                            <span>🪙</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 2 - FEATURES PRINCIPAIS
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-feature-grid">
                <div class="lf-feature">
                    <span class="lf-feature-icon">✨</span>
                    <h3>Descoberta superior</h3>
                    <p>Cards com fotos grandes, busca elegante, filtros rápidos e sensação de app nativo.</p>
                </div>

                <div class="lf-feature">
                    <span class="lf-feature-icon">💘</span>
                    <h3>Match com emoção</h3>
                    <p>Experiência mais quente, humana e romântica para decidir com fluidez e prazer.</p>
                </div>

                <div class="lf-feature">
                    <span class="lf-feature-icon">🪙</span>
                    <h3>Economia social</h3>
                    <p>Base preparada para BYX Coin, cards sociais, posse temporária e ranking.</p>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 3 - DOIS MUNDOS SEPARADOS
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-section-head">
                <span class="lf-kicker">🔥 Novo DNA do Love & Fire</span>
                <h2>Dois mundos. Uma comunidade.</h2>
                <p>
                    O LovenFire separa paquera e jogo competitivo para manter tudo organizado:
                    LovenPets para interação romântica e LovenFire Arena para cards, ranking e BYX Coin.
                </p>
            </div>

            <div class="lf-worlds-wrap">
                <div class="lf-worlds-grid">
                    <div class="lf-world-card lf-world-pets">
                        <div class="lf-world-content">
                            <span class="lf-world-badge">🐾 Mundo 1 · Paquera social</span>
                            <h2>LovenPets</h2>
                            <p>
                                Um jogo social de paquera onde perfis podem ser adotados, disputados e valorizados
                                simbolicamente. Aqui o foco e charme, desejo, presentes, conexao e interacao.
                            </p>

                            <ul class="lf-world-list">
                                <li><i>💘</i> Adote perfis como LovenPets dentro do mundo social.</li>
                                <li><i>🔥</i> Aumente charme, envie presentes e dispute atencao.</li>
                                <li><i>🏆</i> Ranking dos mais desejados, mais adotados e mais populares.</li>
                                <li><i>🛡️</i> IDs reais preservados: a pessoa nunca muda de identidade.</li>
                            </ul>

                            <div class="lf-world-actions">
                                <a class="lf-btn" href="<?php echo h($petsHref); ?>">Entrar no LovenPets</a>
                                <a class="lf-btn" href="<?php echo h($primaryHref); ?>">Criar perfil</a>
                            </div>
                        </div>
                    </div>

                    <div class="lf-world-card lf-world-arena">
                        <div class="lf-world-content">
                            <span class="lf-world-badge">🪙 Mundo 2 · Jogo competitivo</span>
                            <h2>LovenFire Arena</h2>
                            <p>
                                A Arena usa BYX Coin para compra e posse de cards sociais. Quem compra fica como dono
                                atual do bloco/perfil ate outro jogador comprar novamente.
                            </p>

                            <ul class="lf-world-list">
                                <li><i>🪙</i> Compre cards sociais usando BYX Coin.</li>
                                <li><i>📈</i> Valor sobe pouco por compra, evitando superinflacao artificial.</li>
                                <li><i>👑</i> Dono atual permanece ate outro jogador tomar a posse.</li>
                                <li><i>🔒</i> ID do usuario e ID do bloco nunca sao alterados.</li>
                            </ul>

                            <div class="lf-world-actions">
                                <a class="lf-btn" href="<?php echo h($arenaHref); ?>">Conhecer a Arena</a>
                                <a class="lf-btn" href="<?php echo h($walletHref); ?>">Wallet BYX</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lf-economy-strip">
                    <div class="lf-economy-card">
                        <strong>IDs reais</strong>
                        <span>O usuario real continua com o mesmo ID. O card/bloco tambem permanece imutavel.</span>
                    </div>

                    <div class="lf-economy-card">
                        <strong>Posse atual</strong>
                        <span>O comprador vira dono atual do bloco ate outro jogador comprar novamente.</span>
                    </div>

                    <div class="lf-economy-card">
                        <strong>BYX Coin</strong>
                        <span>A moeda oficial da Arena sera usada para cards, rankings, boosts e recompensas.</span>
                    </div>

                    <div class="lf-economy-card">
                        <strong>Controle economico</strong>
                        <span>Aumento pequeno por faixa, taxas, reserva e historico para evitar bagunca.</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 4 - FLECHAS DO CUPIDO / BYX COIN
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-cupid">
                <div class="lf-cupid-main">
                    <span class="lf-kicker">🏹 Recurso exclusivo Love & Fire</span>

                    <h2>Flechas do Cupido</h2>

                    <p>
                        Uma forma divertida e romântica de demonstrar interesse. No futuro,
                        o apaixonado poderá usar BYX Coin para enviar flechas, destacar sua intenção
                        e ativar recursos premium.
                    </p>

                    <div class="lf-wallet-box">
                        <span class="lf-wallet-pill">🪙 Wallet BYX Coin reservada</span>
                        <span class="lf-wallet-pill">💘 Destaques românticos</span>
                        <span class="lf-wallet-pill">🔥 Recursos premium em breve</span>
                    </div>
                </div>

                <div class="lf-arrow-plans">
                    <div class="lf-arrow-card">
                        <b>🏹</b>
                        <h3>Flecha Simples</h3>
                        <p>Uma demonstração leve de interesse para iniciar uma aproximação.</p>
                    </div>

                    <div class="lf-arrow-card">
                        <b>💘</b>
                        <h3>Flecha Flamejante</h3>
                        <p>Um destaque mais forte para mostrar que aquela pessoa chamou atenção.</p>
                    </div>

                    <div class="lf-arrow-card">
                        <b>🪙</b>
                        <h3>Flecha BYX</h3>
                        <p>Reservada para ações especiais usando a futura BYX Coin.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 5 - COMO FUNCIONA
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-section-head">
                <span class="lf-kicker">Como funciona</span>
                <h2>Do primeiro olhar ao primeiro papo.</h2>
                <p>
                    O Love & Fire pode entregar uma jornada completa: descoberta, curtida,
                    match, flecha especial, LovenPets, Arena e conversa em tempo real.
                </p>
            </div>

            <div class="lf-steps">
                <div class="lf-step">
                    <span class="lf-step-number">1</span>
                    <h3>Descubra</h3>
                    <p>Veja pessoas próximas com cards bonitos, claros e fáceis de navegar.</p>
                </div>

                <div class="lf-step">
                    <span class="lf-step-number">2</span>
                    <h3>Curta</h3>
                    <p>Demonstre interesse com leveza e mantenha o fluxo natural.</p>
                </div>

                <div class="lf-step">
                    <span class="lf-step-number">3</span>
                    <h3>Adote</h3>
                    <p>No LovenPets, dispute perfis de forma simbólica, romântica e social.</p>
                </div>

                <div class="lf-step">
                    <span class="lf-step-number">4</span>
                    <h3>Entre na Arena</h3>
                    <p>Na Arena, cards sociais usam posse atual, BYX Coin, ranking e histórico.</p>
                </div>

                <div class="lf-step">
                    <span class="lf-step-number">5</span>
                    <h3>Converse</h3>
                    <p>Chat moderno com mensagens, fotos, status online e digitando.</p>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 6 - EXPERIENCIA PREMIUM
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-section-head">
                <span class="lf-kicker">Experiência premium</span>
                <h2>Mais bonito que uma landing. Mais vivo que um app comum.</h2>
                <p>
                    O visual precisa convencer no primeiro segundo. Por isso, o Love & Fire
                    deve parecer romântico, seguro, moderno, desejável e preparado para jogo social.
                </p>
            </div>

            <div class="lf-preview-row">
                <div class="lf-chat-card">
                    <div class="lf-chat-header">
                        <div class="lf-chat-person">
                            <img class="lf-chat-avatar" src="<?php echo h($profileTwo); ?>" alt="Gabriel">
                            <div>
                                <strong>Gabriel</strong>
                                <span>Online agora</span>
                            </div>
                        </div>

                        <div class="lf-chat-actions">
                            <button type="button">☎</button>
                            <button type="button">⋮</button>
                        </div>
                    </div>

                    <div class="lf-chat-body">
                        <div class="lf-date-line">Hoje</div>

                        <div class="lf-message">
                            <div class="lf-bubble">
                                Oi, adorei seu perfil 😊 <small>19:27</small>
                            </div>
                        </div>

                        <div class="lf-message right">
                            <div class="lf-bubble">
                                Obrigada! Também gostei do seu estilo 🔥 <small>19:28 ✓✓</small>
                            </div>
                        </div>

                        <div class="lf-message">
                            <div class="lf-bubble">
                                Posso te mandar uma Flecha do Cupido? <small>19:29</small>
                            </div>
                        </div>

                        <div class="lf-message right">
                            <div class="lf-bubble">
                                Pode sim 😍 <small>19:30 ✓✓</small>
                            </div>
                        </div>

                        <div class="lf-typing-preview">
                            <span>● ● ●</span>
                            <span>Gabriel está digitando...</span>
                        </div>
                    </div>

                    <div class="lf-chat-input-preview">
                        <span>Digite uma mensagem...</span>
                        <span class="lf-send-preview">↑</span>
                    </div>
                </div>

                <div class="lf-discover-card">
                    <div class="lf-discover-grid">
                        <div class="lf-discover-profile" style="background-image:url('<?php echo h($profileOne); ?>');">
                            <div class="lf-profile-info">
                                <strong>Juliana, 27</strong>
                                <span>Romântica · Online</span>
                            </div>
                        </div>

                        <div class="lf-discover-profile" style="background-image:url('<?php echo h($profileFive); ?>');">
                            <div class="lf-profile-info">
                                <strong>Marina, 24</strong>
                                <span>LovenPet · Viagem</span>
                            </div>
                        </div>

                        <div class="lf-discover-profile" style="background-image:url('<?php echo h($profileSix); ?>');">
                            <div class="lf-profile-info">
                                <strong>Rafael, 30</strong>
                                <span>Card BYX · Verificado</span>
                            </div>
                        </div>
                    </div>

                    <div class="lf-profile-actions">
                        <button class="pass" type="button">Passar</button>
                        <button class="like" type="button">Curtir ❤️</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 7 - SEGURANCA
        ============================================================= -->
        <section class="lf-section">
            <div class="lf-safety-grid">
                <div class="lf-safety-main">
                    <span class="lf-kicker">🛡️ Amor com segurança</span>
                    <h2>Seguro para conversar. Leve para se apaixonar.</h2>
                    <p>
                        O Love & Fire pode crescer com uma identidade forte: ambiente acolhedor,
                        visual premium, bloqueio, denúncia, verificação, IDs reais e experiência mais humana.
                    </p>
                </div>

                <div class="lf-safety-list">
                    <div class="lf-safe-item">
                        <span class="lf-safe-icon">✓</span>
                        <div>
                            <strong>Perfis mais confiáveis</strong>
                            <span>Espaço preparado para verificação e sinais de confiança.</span>
                        </div>
                    </div>

                    <div class="lf-safe-item">
                        <span class="lf-safe-icon">🚫</span>
                        <div>
                            <strong>Bloqueio e denúncia</strong>
                            <span>Fluxo claro para manter o ambiente saudável.</span>
                        </div>
                    </div>

                    <div class="lf-safe-item">
                        <span class="lf-safe-icon">🐾</span>
                        <div>
                            <strong>Posse sem alterar identidade</strong>
                            <span>No jogo, muda o dono atual do card/bloco. O ID real do usuário nunca muda.</span>
                        </div>
                    </div>

                    <div class="lf-safe-item">
                        <span class="lf-safe-icon">🪙</span>
                        <div>
                            <strong>Wallet BYX Coin reservada</strong>
                            <span>Base visual pronta para recursos pagos, Arena, cards e economia social.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 8 - FINAL CTA
        ============================================================= -->
        <section class="lf-final">
            <h2>Pronto para acender novas conexões?</h2>
            <p>
                Entre no Love & Fire e viva uma experiência de relacionamento mais bonita,
                humana, moderna e preparada para LovenPets, Arena e BYX Coin.
            </p>

            <div class="lf-actions" style="justify-content:center;margin-bottom:0;">
                <a class="lf-btn lf-btn-primary" href="<?php echo h($primaryHref); ?>">
                    <?php echo h($primaryText); ?>
                </a>

                <a class="lf-btn lf-btn-ghost" href="<?php echo h($petsHref); ?>">
                    Jogar LovenPets
                </a>

                <a class="lf-btn lf-btn-ghost" href="<?php echo h($arenaHref); ?>">
                    Ver Arena BYX
                </a>
            </div>
        </section>

        <!-- ============================================================
             VERSAO 9 - CTA MOBILE
        ============================================================= -->
        <div class="lf-mobile-cta">
            <a class="lf-btn lf-btn-primary" href="<?php echo h($primaryHref); ?>">
                <?php echo h($primaryText); ?>
            </a>

            <a class="lf-btn lf-btn-ghost" href="<?php echo h($petsHref); ?>">
                Pets
            </a>
        </div>
    </div>
</main>

<script>
(function () {
    var root = document.getElementById('lfHome2026');

    if (!root) {
        return;
    }

    var cards = root.querySelectorAll('.lf-profile-card, .lf-discover-profile');
    var index = 0;

    function pulseCard() {
        var i;

        for (i = 0; i < cards.length; i++) {
            cards[i].style.transform = '';
            cards[i].style.transition = 'transform .45s ease, box-shadow .45s ease';
        }

        if (cards[index]) {
            cards[index].style.transform = 'translateY(-4px) scale(1.015)';
            cards[index].style.boxShadow = '0 24px 50px rgba(41, 29, 35, 0.18)';
        }

        index++;

        if (index >= cards.length) {
            index = 0;
        }
    }

    pulseCard();

    setInterval(function () {
        pulseCard();
    }, 2200);
})();
</script>

<?php
render_footer();
?>
