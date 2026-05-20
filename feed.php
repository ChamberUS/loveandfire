<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';

$user = auth_require();

$posts = db_fetch_all(
    'SELECT p.*, u.name, u.city, u.is_verified, u.avatar, u.birth_date, u.last_seen
     FROM posts p
     INNER JOIN users u ON u.id = p.user_id
     WHERE u.status = \'active\'
     ORDER BY p.created_at DESC
     LIMIT 50'
);

$usingMockPosts = false;

if (count($posts) === 0) {
    $usingMockPosts = true;

    $posts = array(
        array(
            'id' => 0,
            'user_id' => 101,
            'name' => 'Mariana',
            'city' => 'Sao Paulo',
            'is_verified' => 1,
            'avatar' => '',
            'birth_date' => '1999-09-12',
            'last_seen' => now_sql(),
            'created_at' => 'Agora',
            'body' => 'Quem tambem ama descobrir cafes pequenos e conversar sem pressa? Hoje achei um lugar que parece cena de filme.'
        ),
        array(
            'id' => 0,
            'user_id' => 103,
            'name' => 'Camila',
            'city' => 'Belo Horizonte',
            'is_verified' => 1,
            'avatar' => '',
            'birth_date' => '2001-11-03',
            'last_seen' => now_sql(),
            'created_at' => '10 min',
            'body' => 'Playlist de domingo: MPB, sol na janela e vontade de marcar uma viagem para a praia.'
        )
    );
}

function lf2_feed_first_name_safe($name) {
    if (function_exists('love_first_name')) {
        return love_first_name($name);
    }

    $name = trim($name);

    if ($name === '') {
        return 'Usuario';
    }

    $parts = explode(' ', $name);

    return $parts[0];
}

function lf2_feed_time_label($value) {
    $value = trim(strval($value));

    if ($value === '') {
        return 'agora';
    }

    if ($value === 'Agora' || strpos($value, 'min') !== false) {
        return $value;
    }

    $timestamp = strtotime($value);

    if (!$timestamp) {
        return $value;
    }

    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'agora';
    }

    if ($diff < 3600) {
        return floor($diff / 60) . ' min';
    }

    if ($diff < 86400) {
        return floor($diff / 3600) . ' h';
    }

    return date('d/m/Y H:i', $timestamp);
}

function lf2_feed_post_excerpt($text, $limit) {
    $text = trim(strval($text));

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') > $limit) {
            return mb_substr($text, 0, $limit, 'UTF-8') . '...';
        }

        return $text;
    }

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }

    return $text;
}

render_header('Curtidas', 'feed');
?>

<style>
#lf2Feed2026,
#lf2Feed2026 * {
    box-sizing: border-box;
}

#lf2Feed2026 {
    --lf2-feed-pink: #ff3d67;
    --lf2-feed-hot: #ff315f;
    --lf2-feed-coral: #ff7a2f;
    --lf2-feed-text: #49333b;
    --lf2-feed-muted: #8f7d84;
    --lf2-feed-soft: rgba(255,255,255,0.82);
    --lf2-feed-white: #ffffff;
    --lf2-feed-line: rgba(230, 210, 216, 0.82);
    --lf2-feed-shadow: 0 30px 80px rgba(129, 89, 98, 0.12);
    --lf2-feed-shadow-soft: 0 16px 36px rgba(129, 89, 98, 0.08);
    width: min(1320px, calc(100% - 32px));
    margin: 28px auto 42px;
    position: relative;
    isolation: isolate;
    color: var(--lf2-feed-text);
}

#lf2Feed2026:before,
#lf2Feed2026:after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: -1;
}

#lf2Feed2026:before {
    width: 420px;
    height: 420px;
    top: -90px;
    right: -130px;
    background: radial-gradient(circle, rgba(255,49,95,0.18), rgba(255,49,95,0) 68%);
}

#lf2Feed2026:after {
    width: 360px;
    height: 360px;
    bottom: 18%;
    left: -130px;
    background: radial-gradient(circle, rgba(255,122,47,0.16), rgba(255,122,47,0) 68%);
}

#lf2Feed2026 .lf2-feed-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
    gap: 18px;
    align-items: stretch;
    margin-bottom: 18px;
}

#lf2Feed2026 .lf2-feed-hero-card,
#lf2Feed2026 .lf2-feed-wallet,
#lf2Feed2026 .lf2-feed-compose,
#lf2Feed2026 .lf2-feed-card,
#lf2Feed2026 .lf2-feed-side-card {
    background:
        radial-gradient(circle at 100% 0%, rgba(255,255,255,0.78), rgba(255,255,255,0) 42%),
        linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,250,250,0.86));
    border: 1px solid var(--lf2-feed-line);
    box-shadow: var(--lf2-feed-shadow);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

#lf2Feed2026 .lf2-feed-hero-card {
    border-radius: 36px;
    padding: 30px;
    overflow: hidden;
    position: relative;
}

#lf2Feed2026 .lf2-feed-hero-card:before {
    content: "";
    position: absolute;
    right: -70px;
    top: -70px;
    width: 210px;
    height: 210px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,49,95,0.12), rgba(255,49,95,0) 70%);
}

#lf2Feed2026 .lf2-feed-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 15px;
    border-radius: 999px;
    background: rgba(255,255,255,0.86);
    border: 1px solid rgba(255,49,95,0.13);
    color: var(--lf2-feed-hot);
    font-size: 12px;
    font-weight: 900;
    box-shadow: 0 12px 26px rgba(255,49,95,0.08);
}

#lf2Feed2026 .lf2-feed-title {
    position: relative;
    z-index: 2;
    margin: 18px 0 10px;
    color: var(--lf2-feed-pink);
    font-size: clamp(40px, 5vw, 72px);
    line-height: .96;
    letter-spacing: -2px;
    font-weight: 950;
    text-shadow: 0 14px 30px rgba(242,54,95,0.12);
}

#lf2Feed2026 .lf2-feed-desc {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0;
    color: #816d75;
    font-size: 16px;
    line-height: 1.75;
    font-weight: 650;
}

#lf2Feed2026 .lf2-feed-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
    position: relative;
    z-index: 2;
}

#lf2Feed2026 .lf2-feed-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 36px;
    padding: 0 13px;
    border-radius: 999px;
    background: rgba(255,255,255,0.76);
    border: 1px solid rgba(230,210,216,0.72);
    color: #7e6870;
    font-size: 12px;
    font-weight: 900;
    box-shadow: 0 10px 22px rgba(129,89,98,0.06);
}

#lf2Feed2026 .lf2-feed-wallet {
    border-radius: 36px;
    padding: 24px;
    position: relative;
    overflow: hidden;
}

#lf2Feed2026 .lf2-feed-wallet:before {
    content: "";
    position: absolute;
    right: -50px;
    top: -50px;
    width: 170px;
    height: 170px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,122,47,0.18), rgba(255,122,47,0) 70%);
}

#lf2Feed2026 .lf2-feed-wallet h2 {
    position: relative;
    z-index: 2;
    margin: 14px 0 8px;
    color: #3c2b32;
    font-size: 30px;
    line-height: 1.05;
    font-weight: 950;
}

#lf2Feed2026 .lf2-feed-wallet p {
    position: relative;
    z-index: 2;
    margin: 0;
    color: var(--lf2-feed-muted);
    font-size: 14px;
    line-height: 1.65;
    font-weight: 650;
}

#lf2Feed2026 .lf2-feed-bix-box {
    position: relative;
    z-index: 2;
    margin-top: 18px;
    padding: 18px;
    border-radius: 26px;
    color: #fff;
    background: linear-gradient(135deg, var(--lf2-feed-hot), var(--lf2-feed-coral));
    box-shadow: 0 18px 42px rgba(255,94,82,0.22);
}

#lf2Feed2026 .lf2-feed-bix-box strong {
    display: block;
    font-size: 26px;
    line-height: 1;
    font-weight: 950;
}

#lf2Feed2026 .lf2-feed-bix-box span {
    display: block;
    margin-top: 8px;
    color: rgba(255,255,255,0.90);
    font-size: 13px;
    line-height: 1.5;
    font-weight: 750;
}

#lf2Feed2026 .lf2-feed-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 18px;
    align-items: start;
}

#lf2Feed2026 .lf2-feed-main {
    min-width: 0;
}

#lf2Feed2026 .lf2-feed-compose {
    border-radius: 34px;
    padding: 18px;
    margin-bottom: 16px;
}

#lf2Feed2026 .lf2-compose-head {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 14px;
}

#lf2Feed2026 .lf2-compose-avatar {
    width: 52px;
    height: 52px;
    border-radius: 18px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.94);
    box-shadow: 0 12px 24px rgba(60,40,48,0.12);
}

#lf2Feed2026 .lf2-compose-head strong {
    display: block;
    color: #3c2b32;
    font-size: 16px;
    font-weight: 950;
}

#lf2Feed2026 .lf2-compose-head span {
    display: block;
    margin-top: 3px;
    color: var(--lf2-feed-muted);
    font-size: 12px;
    font-weight: 800;
}

#lf2Feed2026 .lf2-feed-compose textarea {
    width: 100%;
    min-height: 124px;
    resize: vertical;
    border: 1px solid rgba(230,210,216,0.92);
    border-radius: 24px;
    background: rgba(255,255,255,0.90);
    outline: none;
    padding: 18px;
    color: #49333b;
    font-size: 15px;
    line-height: 1.65;
    font-weight: 700;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.90);
}

#lf2Feed2026 .lf2-feed-compose textarea:focus {
    border-color: rgba(255,49,95,0.34);
    box-shadow: 0 0 0 4px rgba(255,49,95,0.08), inset 0 1px 0 rgba(255,255,255,0.90);
    background: #fff;
}

#lf2Feed2026 .lf2-compose-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-top: 12px;
    flex-wrap: wrap;
}

#lf2Feed2026 .lf2-compose-tools {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

#lf2Feed2026 .lf2-compose-tool {
    min-height: 38px;
    padding: 0 12px;
    border-radius: 999px;
    border: 1px solid rgba(230,210,216,0.82);
    background: rgba(255,255,255,0.76);
    color: #7f6a72;
    font-size: 12px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
}

#lf2Feed2026 .lf2-feed-btn {
    min-height: 46px;
    border: 0;
    border-radius: 999px;
    padding: 0 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none !important;
    font-size: 13px;
    font-weight: 950;
    cursor: pointer;
    transition: all .2s ease;
}

#lf2Feed2026 .lf2-feed-btn-primary {
    color: #fff;
    background: linear-gradient(135deg, var(--lf2-feed-hot), var(--lf2-feed-coral));
    box-shadow: 0 16px 32px rgba(255,94,82,0.20);
}

#lf2Feed2026 .lf2-feed-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 42px rgba(255,94,82,0.26);
}

#lf2Feed2026 .lf2-feed-btn-ghost {
    color: var(--lf2-feed-hot);
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(255,49,95,0.13);
    box-shadow: 0 12px 24px rgba(129,89,98,0.06);
}

#lf2Feed2026 .lf2-feed-btn-danger {
    color: #e54862;
    background: rgba(255,255,255,0.85);
    border: 1px solid rgba(229,72,98,0.16);
}

#lf2Feed2026 .lf2-feed-tabs {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 16px;
    overflow-x: auto;
    padding-bottom: 2px;
}

#lf2Feed2026 .lf2-feed-tab {
    white-space: nowrap;
    min-height: 40px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.72);
    border: 1px solid rgba(230,210,216,0.78);
    color: #826e76;
    font-size: 12px;
    font-weight: 950;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 10px 22px rgba(129,89,98,0.05);
}

#lf2Feed2026 .lf2-feed-tab.active {
    color: #fff;
    background: linear-gradient(135deg, var(--lf2-feed-hot), var(--lf2-feed-coral));
    border-color: transparent;
}

#lf2Feed2026 .lf2-feed-card {
    border-radius: 34px;
    padding: 18px;
    margin-bottom: 16px;
    transition: transform .24s ease, box-shadow .24s ease;
}

#lf2Feed2026 .lf2-feed-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 36px 86px rgba(129,89,98,0.15);
}

#lf2Feed2026 .lf2-post-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 14px;
}

#lf2Feed2026 .lf2-post-author {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

#lf2Feed2026 .lf2-post-avatar-wrap {
    position: relative;
    flex: 0 0 auto;
}

#lf2Feed2026 .lf2-post-avatar {
    width: 58px;
    height: 58px;
    border-radius: 20px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.94);
    box-shadow: 0 14px 28px rgba(60,40,48,0.12);
}

#lf2Feed2026 .lf2-online-dot {
    position: absolute;
    right: -1px;
    bottom: 3px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #27c77a;
    border: 3px solid #fff;
}

#lf2Feed2026 .lf2-post-author strong {
    display: block;
    color: #3c2b32;
    font-size: 16px;
    font-weight: 950;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#lf2Feed2026 .lf2-post-author span {
    display: block;
    margin-top: 4px;
    color: var(--lf2-feed-muted);
    font-size: 12px;
    font-weight: 800;
}

#lf2Feed2026 .lf2-verified {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    margin-left: 5px;
    border-radius: 50%;
    background: rgba(255,49,95,0.10);
    color: var(--lf2-feed-hot);
    font-size: 12px;
    vertical-align: middle;
}

#lf2Feed2026 .lf2-post-menu {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
}

#lf2Feed2026 .lf2-post-badge {
    min-height: 34px;
    padding: 0 11px;
    border-radius: 999px;
    background: rgba(255,49,95,0.08);
    color: var(--lf2-feed-hot);
    font-size: 11px;
    font-weight: 950;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

#lf2Feed2026 .lf2-post-dots {
    width: 38px;
    height: 38px;
    border: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.86);
    color: #716169;
    font-size: 20px;
    font-weight: 950;
    box-shadow: 0 12px 24px rgba(129,89,98,0.07);
}

#lf2Feed2026 .lf2-post-body {
    padding: 18px;
    border-radius: 26px;
    background: rgba(255,255,255,0.62);
    border: 1px solid rgba(230,210,216,0.66);
}

#lf2Feed2026 .lf2-post-body p {
    margin: 0;
    color: #5f4851;
    font-size: 15px;
    line-height: 1.75;
    font-weight: 700;
}

#lf2Feed2026 .lf2-post-mood {
    margin-top: 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

#lf2Feed2026 .lf2-mood-chip {
    min-height: 30px;
    padding: 0 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(230,210,216,0.72);
    color: #826e76;
    font-size: 11px;
    font-weight: 900;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

#lf2Feed2026 .lf2-post-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
    margin-top: 14px;
}

#lf2Feed2026 .lf2-comments {
    margin-top: 14px;
    padding: 14px;
    border-radius: 26px;
    background: rgba(255,255,255,0.56);
    border: 1px solid rgba(230,210,216,0.62);
}

#lf2Feed2026 .lf2-comment {
    padding: 10px 12px;
    margin-bottom: 8px;
    border-radius: 18px;
    background: rgba(255,255,255,0.76);
    color: #604a53;
    font-size: 13px;
    line-height: 1.5;
    font-weight: 650;
}

#lf2Feed2026 .lf2-comment strong {
    color: #3c2b32;
    font-weight: 950;
}

#lf2Feed2026 .lf2-comment-form {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

#lf2Feed2026 .lf2-comment-form input {
    flex: 1;
    min-width: 0;
    min-height: 48px;
    border-radius: 999px;
    border: 1px solid rgba(230,210,216,0.86);
    background: rgba(255,255,255,0.90);
    color: #49333b;
    outline: none;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 750;
}

#lf2Feed2026 .lf2-comment-form input:focus {
    border-color: rgba(255,49,95,0.30);
    box-shadow: 0 0 0 4px rgba(255,49,95,0.07);
}

#lf2Feed2026 .lf2-comment-form button {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    border: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--lf2-feed-hot), var(--lf2-feed-coral));
    color: #fff;
    font-size: 18px;
    box-shadow: 0 16px 30px rgba(255,94,82,0.20);
}

#lf2Feed2026 .lf2-feed-sidebar {
    position: sticky;
    top: 18px;
    display: grid;
    gap: 16px;
}

#lf2Feed2026 .lf2-feed-side-card {
    border-radius: 30px;
    padding: 20px;
}

#lf2Feed2026 .lf2-feed-side-card h3 {
    margin: 0 0 8px;
    color: #3c2b32;
    font-size: 20px;
    line-height: 1.15;
    font-weight: 950;
}

#lf2Feed2026 .lf2-feed-side-card p {
    margin: 0;
    color: var(--lf2-feed-muted);
    font-size: 13px;
    line-height: 1.6;
    font-weight: 650;
}

#lf2Feed2026 .lf2-side-list {
    display: grid;
    gap: 10px;
    margin-top: 14px;
}

#lf2Feed2026 .lf2-side-item {
    padding: 13px 14px;
    border-radius: 20px;
    background: rgba(255,255,255,0.70);
    border: 1px solid rgba(230,210,216,0.62);
}

#lf2Feed2026 .lf2-side-item strong {
    display: block;
    color: #4b3540;
    font-size: 13px;
    font-weight: 950;
}

#lf2Feed2026 .lf2-side-item span {
    display: block;
    margin-top: 5px;
    color: #8a7880;
    font-size: 12px;
    line-height: 1.45;
    font-weight: 700;
}

#lf2Feed2026 .lf2-prompt-btn {
    width: 100%;
    border: 0;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
}

#lf2Feed2026 .lf2-feed-empty {
    padding: 24px;
    border-radius: 28px;
    background: rgba(255,255,255,0.72);
    border: 1px dashed rgba(230,210,216,0.92);
    color: var(--lf2-feed-muted);
    font-size: 14px;
    font-weight: 800;
    text-align: center;
}

@media (max-width: 1120px) {
    #lf2Feed2026 .lf2-feed-hero,
    #lf2Feed2026 .lf2-feed-layout {
        grid-template-columns: 1fr;
    }

    #lf2Feed2026 .lf2-feed-sidebar {
        position: static;
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 820px) {
    #lf2Feed2026 {
        width: min(100% - 20px, 760px);
        margin-top: 18px;
    }

    #lf2Feed2026 .lf2-feed-hero-card,
    #lf2Feed2026 .lf2-feed-wallet,
    #lf2Feed2026 .lf2-feed-compose,
    #lf2Feed2026 .lf2-feed-card,
    #lf2Feed2026 .lf2-feed-side-card {
        border-radius: 26px;
    }

    #lf2Feed2026 .lf2-feed-hero-card,
    #lf2Feed2026 .lf2-feed-wallet {
        padding: 20px;
    }

    #lf2Feed2026 .lf2-feed-title {
        font-size: clamp(38px, 12vw, 58px);
        letter-spacing: -1.3px;
    }

    #lf2Feed2026 .lf2-feed-sidebar {
        grid-template-columns: 1fr;
    }

    #lf2Feed2026 .lf2-compose-footer {
        align-items: stretch;
    }

    #lf2Feed2026 .lf2-feed-btn-primary {
        width: 100%;
    }

    #lf2Feed2026 .lf2-post-head {
        align-items: flex-start;
    }

    #lf2Feed2026 .lf2-post-badge {
        display: none;
    }

    #lf2Feed2026 .lf2-comment-form {
        gap: 8px;
    }
}

@media (max-width: 520px) {
    #lf2Feed2026 .lf2-feed-compose,
    #lf2Feed2026 .lf2-feed-card,
    #lf2Feed2026 .lf2-feed-side-card {
        padding: 14px;
    }

    #lf2Feed2026 .lf2-post-avatar {
        width: 50px;
        height: 50px;
        border-radius: 17px;
    }

    #lf2Feed2026 .lf2-post-body {
        padding: 15px;
        border-radius: 22px;
    }

    #lf2Feed2026 .lf2-post-actions .lf2-feed-btn {
        flex: 1 1 auto;
    }
}
</style>

<section id="lf2Feed2026">
    <div class="lf2-feed-hero">
        <div class="lf2-feed-hero-card">
            <span class="lf2-feed-pill">💘 Feed interno · somente usuários logados</span>

            <h1 class="lf2-feed-title">Sinais que combinam</h1>

            <p class="lf2-feed-desc">
                Compartilhe um pensamento, publique uma vibe do dia, curta sinais de interesse
                e abra conversas leves com quem fez seu coração aquecer.
            </p>

            <div class="lf2-feed-tags">
                <span class="lf2-feed-tag">🔥 Posts com intenção</span>
                <span class="lf2-feed-tag">💬 DMs rápidas</span>
                <span class="lf2-feed-tag">♡ Curtidas românticas</span>
                <span class="lf2-feed-tag">🏹 Flechas em breve</span>
            </div>
        </div>

        <aside class="lf2-feed-wallet">
            <span class="lf2-feed-pill">🪙 Wallet BIX</span>
            <h2>Moeda do amor</h2>
            <p>
                Área reservada para BIX. Futuramente o usuário poderá comprar Flechas do Cupido,
                destacar posts e chamar atenção de quem mexeu com ele.
            </p>

            <div class="lf2-feed-bix-box">
                <strong>0 BIX</strong>
                <span>Saldo ilustrativo. Recurso ainda reservado para próxima etapa.</span>
            </div>
        </aside>
    </div>

    <div class="lf2-feed-layout">
        <main class="lf2-feed-main">
            <form action="api/feed_create.php" method="post" class="lf2-feed-compose">
                <?php echo csrf_field(); ?>

                <div class="lf2-compose-head">
                    <img class="lf2-compose-avatar" src="<?php echo h(user_avatar($user)); ?>" alt="<?php echo h($user['name']); ?>">

                    <div>
                        <strong><?php echo h(lf2_feed_first_name_safe($user['name'])); ?>, publique uma chama</strong>
                        <span>O feed aparece para usuários logados dentro do Love &amp; Fire.</span>
                    </div>
                </div>

                <textarea id="lf2FeedComposer" name="body" rows="4" maxlength="700" placeholder="Conte o que acendeu seu dia..."></textarea>

                <div class="lf2-compose-footer">
                    <div class="lf2-compose-tools">
                        <button class="lf2-compose-tool" type="button" data-prompt="Hoje eu queria conhecer alguém que...">💭 Ideia</button>
                        <button class="lf2-compose-tool" type="button" data-prompt="Minha vibe perfeita para um encontro seria...">💘 Encontro</button>
                        <button class="lf2-compose-tool" type="button" data-prompt="Uma conversa boa para mim começa com...">💬 Conversa</button>
                    </div>

                    <button class="lf2-feed-btn lf2-feed-btn-primary" type="submit">❤️ Publicar</button>
                </div>
            </form>

            <div class="lf2-feed-tabs">
                <span class="lf2-feed-tab active">🔥 Recentes</span>
                <span class="lf2-feed-tab">💘 Românticos</span>
                <span class="lf2-feed-tab">💬 Prontos para conversar</span>
                <span class="lf2-feed-tab">🏹 Flechas em breve</span>
            </div>

            <?php if (count($posts) === 0) { ?>
                <div class="lf2-feed-empty">
                    Nenhum sinal publicado ainda. Seja o primeiro a acender o feed.
                </div>
            <?php } ?>

            <?php $i = 0; ?>
            <?php foreach ($posts as $post) { ?>
                <?php
                if ($usingMockPosts) {
                    $comments = array(
                        array('name' => 'Juliana', 'body' => 'Amei essa vibe!'),
                        array('name' => 'Rafael', 'body' => 'Ja quero a indicacao.')
                    );
                } else {
                    $comments = db_fetch_all(
                        'SELECT c.*, u.name
                         FROM post_comments c
                         INNER JOIN users u ON u.id = c.user_id
                         WHERE c.post_id = ' . intval($post['id']) . '
                         ORDER BY c.created_at ASC
                         LIMIT 8'
                    );
                }

                $isMine = intval($post['user_id']) === intval($user['id']);
                $timeLabel = lf2_feed_time_label($post['created_at']);
                $authorName = lf2_feed_first_name_safe($post['name']);
                $avatarUrl = love_profile_photo($post, $i);
                ?>

                <article class="lf2-feed-card">
                    <header class="lf2-post-head">
                        <div class="lf2-post-author">
                            <span class="lf2-post-avatar-wrap">
                                <img class="lf2-post-avatar" src="<?php echo h($avatarUrl); ?>" alt="<?php echo h($post['name']); ?>">
                                <span class="lf2-online-dot"></span>
                            </span>

                            <span>
                                <strong>
                                    <?php echo h($authorName); ?>
                                    <?php if (!empty($post['is_verified'])) { ?>
                                        <span class="lf2-verified">✓</span>
                                    <?php } ?>
                                </strong>
                                <span><?php echo h($post['city']); ?> · <?php echo h($timeLabel); ?></span>
                            </span>
                        </div>

                        <div class="lf2-post-menu">
                            <span class="lf2-post-badge">Feed interno</span>
                            <button class="lf2-post-dots" type="button">⋯</button>
                        </div>
                    </header>

                    <div class="lf2-post-body">
                        <p><?php echo nl2br(h($post['body'])); ?></p>

                        <div class="lf2-post-mood">
                            <span class="lf2-mood-chip">🔥 sinal quente</span>
                            <span class="lf2-mood-chip">💬 bom para puxar assunto</span>
                            <span class="lf2-mood-chip">♡ curtir com leveza</span>
                        </div>
                    </div>

                    <div class="lf2-post-actions">
                        <?php if ($usingMockPosts) { ?>
                            <a class="lf2-feed-btn lf2-feed-btn-primary" href="chat.php">💬 Enviar DM</a>
                            <a class="lf2-feed-btn lf2-feed-btn-ghost" href="swipe.php">♡ Curtir</a>
                        <?php } elseif (!$isMine) { ?>
                            <a class="lf2-feed-btn lf2-feed-btn-primary" href="api/dm_start.php?target_id=<?php echo intval($post['user_id']); ?>&post_id=<?php echo intval($post['id']); ?>">💬 Enviar DM</a>
                            <a class="lf2-feed-btn lf2-feed-btn-ghost" href="api/post_like.php?post_id=<?php echo intval($post['id']); ?>">♡ Curtir</a>
                            <a class="lf2-feed-btn lf2-feed-btn-danger" href="api/report_user.php?target_id=<?php echo intval($post['user_id']); ?>&post_id=<?php echo intval($post['id']); ?>">Denunciar</a>
                        <?php } else { ?>
                            <a class="lf2-feed-btn lf2-feed-btn-ghost" href="api/post_like.php?post_id=<?php echo intval($post['id']); ?>">♡ Curtir meu post</a>
                        <?php } ?>
                    </div>

                    <div class="lf2-comments">
                        <?php if (count($comments) > 0) { ?>
                            <?php foreach ($comments as $comment) { ?>
                                <div class="lf2-comment">
                                    <strong><?php echo h(lf2_feed_first_name_safe($comment['name'])); ?>:</strong>
                                    <?php echo h($comment['body']); ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="lf2-comment">
                                Seja o primeiro a responder com carinho.
                            </div>
                        <?php } ?>

                        <?php if (!$usingMockPosts) { ?>
                            <form action="api/post_comment.php" method="post" class="lf2-comment-form">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="post_id" value="<?php echo intval($post['id']); ?>">
                                <input type="text" name="body" placeholder="Responder com carinho...">
                                <button type="submit">🔥</button>
                            </form>
                        <?php } ?>
                    </div>
                </article>

                <?php $i++; ?>
            <?php } ?>
        </main>

        <aside class="lf2-feed-sidebar">
            <div class="lf2-feed-side-card">
                <h3>Privacidade do feed</h3>
                <p>
                    Este feed é fechado para visitantes, mas visível para usuários logados do app.
                    Para deixar só por matches, precisa mudar a regra da consulta.
                </p>

                <div class="lf2-side-list">
                    <div class="lf2-side-item">
                        <strong>Atual</strong>
                        <span>Usuários logados veem posts de usuários ativos.</span>
                    </div>

                    <div class="lf2-side-item">
                        <strong>Opção futura</strong>
                        <span>Filtrar por matches, cidade, idade ou preferência.</span>
                    </div>
                </div>
            </div>

            <div class="lf2-feed-side-card">
                <h3>Flechas do Cupido</h3>
                <p>
                    Área reservada para transformar posts em oportunidades românticas com BIX.
                </p>

                <div class="lf2-side-list">
                    <div class="lf2-side-item">
                        <strong>🏹 Flecha no post</strong>
                        <span>Destacar interesse diretamente em uma publicação.</span>
                    </div>

                    <div class="lf2-side-item">
                        <strong>🔥 Boost romântico</strong>
                        <span>Fazer um sinal aparecer mais para pessoas compatíveis.</span>
                    </div>

                    <div class="lf2-side-item">
                        <strong>🪙 Wallet BIX</strong>
                        <span>Reservado para compras e recursos premium.</span>
                    </div>
                </div>
            </div>

            <div class="lf2-feed-side-card">
                <h3>Ideias para publicar</h3>
                <p>Clique em uma inspiração e complete do seu jeito.</p>

                <div class="lf2-side-list">
                    <button class="lf2-side-item lf2-prompt-btn" type="button" data-prompt="Hoje eu queria conversar com alguém que goste de...">
                        <strong>💬 Puxar assunto</strong>
                        <span>Hoje eu queria conversar com alguém que goste de...</span>
                    </button>

                    <button class="lf2-side-item lf2-prompt-btn" type="button" data-prompt="Meu encontro ideal teria...">
                        <strong>💘 Encontro ideal</strong>
                        <span>Meu encontro ideal teria...</span>
                    </button>

                    <button class="lf2-side-item lf2-prompt-btn" type="button" data-prompt="Uma coisa simples que me conquista é...">
                        <strong>✨ Algo que conquista</strong>
                        <span>Uma coisa simples que me conquista é...</span>
                    </button>
                </div>
            </div>
        </aside>
    </div>
</section>

<script>
(function () {
    var root = document.getElementById('lf2Feed2026');
    var composer = document.getElementById('lf2FeedComposer');

    if (!root || !composer) {
        return;
    }

    var promptButtons = root.querySelectorAll('[data-prompt]');
    var cards = root.querySelectorAll('.lf2-feed-card');
    var i;

    function trimText(text) {
        return String(text || '').replace(/^\s+|\s+$/g, '');
    }

    function setPrompt(text) {
        if (trimText(composer.value) === '') {
            composer.value = text;
        } else {
            composer.value = composer.value + "\n" + text;
        }

        composer.focus();

        try {
            composer.setSelectionRange(composer.value.length, composer.value.length);
        } catch (e) {}
    }

    for (i = 0; i < promptButtons.length; i++) {
        promptButtons[i].onclick = function () {
            setPrompt(this.getAttribute('data-prompt'));
        };
    }

    if (cards.length > 0) {
        var active = 0;

        setInterval(function () {
            var x;

            for (x = 0; x < cards.length; x++) {
                cards[x].style.transform = '';
            }

            if (cards[active]) {
                cards[active].style.transform = 'translateY(-3px)';
            }

            active++;

            if (active >= cards.length) {
                active = 0;
            }
        }, 2800);
    }
})();
</script>

<?php render_footer(); ?>