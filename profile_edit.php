<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';

$user = auth_require();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name = trim(post_text('name', 120));
    $city = trim(post_text('city', 80));
    $country = trim(post_text('country', 80));
    $gender = trim(post_text('gender', 30));
    $bio = trim(post_text('bio', 500));
    $dmMode = trim(post_text('dm_mode', 20));
    $lookingFor = trim(post_text('looking_for', 30));

    $minAge = intval(isset($_POST['min_age']) ? $_POST['min_age'] : 18);
    $maxAge = intval(isset($_POST['max_age']) ? $_POST['max_age'] : 80);
    $verifiedOnly = isset($_POST['verified_only']) ? 1 : 0;
    $removeAvatar = isset($_POST['remove_avatar']) ? 1 : 0;

    if ($name === '') {
        $name = $user['name'];
    }

    if ($city === '') {
        $city = $user['city'];
    }

    if ($country === '') {
        $country = $user['country'];
    }

    $allowedGenders = array('', 'female', 'male', 'non_binary', 'other');
    if (!in_array($gender, $allowedGenders)) {
        $gender = '';
    }

    $allowedDmModes = array('open', 'matches', 'closed');
    if (!in_array($dmMode, $allowedDmModes)) {
        $dmMode = 'open';
    }

    $allowedLookingFor = array('all', 'female', 'male', 'non_binary');
    if (!in_array($lookingFor, $allowedLookingFor)) {
        $lookingFor = 'all';
    }

    if ($minAge < 18) {
        $minAge = 18;
    }

    if ($maxAge < 18) {
        $maxAge = 18;
    }

    if ($minAge > 99) {
        $minAge = 99;
    }

    if ($maxAge > 99) {
        $maxAge = 99;
    }

    if ($maxAge < $minAge) {
        $maxAge = $minAge;
    }

    $avatarSql = '';
    $newAvatar = hc_profile_photo_upload();

    if ($newAvatar !== '') {
        hc_delete_profile_photo(hc_uploaded_profile_photo_name($user));
        $avatarSql = ', avatar = ' . db_escape($newAvatar);
    } elseif ($removeAvatar) {
        hc_delete_profile_photo(hc_uploaded_profile_photo_name($user));
        $avatarSql = ", avatar = ''";
    }

    db_query(
        'UPDATE users SET ' .
        'name = ' . db_escape($name) . ', ' .
        'city = ' . db_escape($city) . ', ' .
        'country = ' . db_escape($country) . ', ' .
        'gender = ' . db_escape($gender) . ', ' .
        'bio = ' . db_escape($bio) . ', ' .
        'dm_mode = ' . db_escape($dmMode) .
        $avatarSql . ', ' .
        'updated_at = ' . db_escape(now_sql()) . ' ' .
        'WHERE id = ' . intval($user['id'])
    );

    $pref = db_fetch_one('SELECT user_id FROM profile_preferences WHERE user_id = ' . intval($user['id']) . ' LIMIT 1');

    if ($pref) {
        db_query(
            'UPDATE profile_preferences SET ' .
            'looking_for = ' . db_escape($lookingFor) . ', ' .
            'min_age = ' . intval($minAge) . ', ' .
            'max_age = ' . intval($maxAge) . ', ' .
            'city = ' . db_escape($city) . ', ' .
            'verified_only = ' . intval($verifiedOnly) . ', ' .
            'updated_at = ' . db_escape(now_sql()) . ' ' .
            'WHERE user_id = ' . intval($user['id'])
        );
    } else {
        db_query(
            'INSERT INTO profile_preferences (' .
            'user_id, looking_for, min_age, max_age, city, verified_only, created_at, updated_at' .
            ') VALUES (' .
            intval($user['id']) . ', ' .
            db_escape($lookingFor) . ', ' .
            intval($minAge) . ', ' .
            intval($maxAge) . ', ' .
            db_escape($city) . ', ' .
            intval($verifiedOnly) . ', ' .
            db_escape(now_sql()) . ', ' .
            db_escape(now_sql()) .
            ')'
        );
    }

    flash_set('ok', 'Perfil atualizado.');
    redirect('profile_edit.php');
}

$freshUser = db_fetch_one('SELECT * FROM users WHERE id = ' . intval($user['id']) . ' LIMIT 1');
if ($freshUser) {
    $user = $freshUser;
}

$pref = db_fetch_one('SELECT * FROM profile_preferences WHERE user_id = ' . intval($user['id']) . ' LIMIT 1');

$currentName = isset($user['name']) ? $user['name'] : '';
$currentCity = isset($user['city']) ? $user['city'] : '';
$currentCountry = isset($user['country']) ? $user['country'] : '';
$currentGender = isset($user['gender']) ? $user['gender'] : '';
$currentBio = isset($user['bio']) ? $user['bio'] : '';
$currentDmMode = isset($user['dm_mode']) ? $user['dm_mode'] : 'open';
$currentAvatar = isset($user['avatar']) ? $user['avatar'] : '';

$prefLookingFor = $pref ? $pref['looking_for'] : 'all';
$prefMinAge = $pref ? intval($pref['min_age']) : 18;
$prefMaxAge = $pref ? intval($pref['max_age']) : 80;
$prefVerifiedOnly = $pref ? intval($pref['verified_only']) : 0;

$firstName = $currentName;
if (strpos($firstName, ' ') !== false) {
    $parts = explode(' ', trim($firstName));
    if (isset($parts[0]) && $parts[0] !== '') {
        $firstName = $parts[0];
    }
}
if ($firstName === '') {
    $firstName = 'Perfil';
}

render_header('Meu perfil', 'profile');
?>

<style>
.lf2p-shell,
.lf2p-shell * {
    box-sizing: border-box;
}

.lf2p-shell {
    --lf2p-bg1: #fff8f7;
    --lf2p-bg2: #ffe8e8;
    --lf2p-white: #ffffff;
    --lf2p-card: rgba(255,255,255,0.82);
    --lf2p-line: rgba(242, 154, 167, 0.18);
    --lf2p-text: #4c353d;
    --lf2p-muted: #8f7980;
    --lf2p-pink: #ff3d67;
    --lf2p-hot: #ff7a2f;
    --lf2p-red: #ff4d6d;
    --lf2p-orange: #ff922b;
    --lf2p-soft-shadow: 0 16px 40px rgba(135, 77, 92, 0.08);
    --lf2p-big-shadow: 0 30px 80px rgba(135, 77, 92, 0.12);
    position: relative;
    width: min(1280px, calc(100% - 30px));
    margin: 28px auto 38px auto;
    color: var(--lf2p-text);
    font-family: inherit;
}

.lf2p-shell:before,
.lf2p-shell:after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
}

.lf2p-shell:before {
    width: 380px;
    height: 380px;
    top: -60px;
    right: -90px;
    background: radial-gradient(circle, rgba(255, 115, 139, 0.20), rgba(255, 115, 139, 0) 68%);
}

.lf2p-shell:after {
    width: 320px;
    height: 320px;
    bottom: 40px;
    left: -80px;
    background: radial-gradient(circle, rgba(255, 160, 122, 0.18), rgba(255, 160, 122, 0) 68%);
}

.lf2p-inner {
    position: relative;
    z-index: 2;
}

.lf2p-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
    gap: 18px;
    margin-bottom: 18px;
}

.lf2p-hero-card,
.lf2p-wallet-card,
.lf2p-form-card,
.lf2p-preview-card {
    background: linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,250,250,0.84));
    border: 1px solid var(--lf2p-line);
    box-shadow: var(--lf2p-big-shadow);
    border-radius: 34px;
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
}

.lf2p-hero-card {
    padding: 28px 28px 24px 28px;
    position: relative;
    overflow: hidden;
}

.lf2p-hero-card:before {
    content: "";
    position: absolute;
    right: -20px;
    top: -40px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 74, 108, 0.10), rgba(255, 74, 108, 0) 70%);
}

.lf2p-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 36px;
    padding: 0 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 900;
    color: #ff4d6d;
    background: rgba(255,255,255,0.80);
    border: 1px solid rgba(255, 77, 109, 0.12);
    box-shadow: 0 8px 20px rgba(255, 77, 109, 0.08);
}

.lf2p-hero-title {
    margin: 16px 0 10px 0;
    font-size: clamp(34px, 4vw, 56px);
    line-height: 1.02;
    letter-spacing: -1.5px;
    color: #ff3d67;
    font-weight: 900;
}

.lf2p-hero-desc {
    margin: 0;
    font-size: 16px;
    line-height: 1.7;
    color: var(--lf2p-muted);
    max-width: 720px;
    font-weight: 600;
}

.lf2p-hero-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 22px;
}

.lf2p-stat {
    padding: 16px;
    border-radius: 22px;
    background: rgba(255,255,255,0.76);
    border: 1px solid rgba(255, 77, 109, 0.08);
    box-shadow: var(--lf2p-soft-shadow);
}

.lf2p-stat strong {
    display: block;
    font-size: 22px;
    line-height: 1;
    color: #3d2a31;
    font-weight: 900;
}

.lf2p-stat span {
    display: block;
    margin-top: 7px;
    font-size: 12px;
    line-height: 1.4;
    color: var(--lf2p-muted);
    font-weight: 700;
}

.lf2p-wallet-card {
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
}

.lf2p-wallet-card:before {
    content: "";
    position: absolute;
    top: -70px;
    right: -70px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 146, 43, 0.18), rgba(255, 146, 43, 0) 70%);
}

.lf2p-wallet-head {
    position: relative;
    z-index: 2;
}

.lf2p-wallet-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(255, 244, 232, 0.92);
    border: 1px solid rgba(255, 146, 43, 0.16);
    color: #ff8b1f;
    font-size: 12px;
    font-weight: 900;
}

.lf2p-wallet-title {
    margin: 14px 0 8px 0;
    font-size: 28px;
    line-height: 1.05;
    color: #3a2a30;
    font-weight: 900;
}

.lf2p-wallet-desc {
    margin: 0;
    font-size: 14px;
    line-height: 1.65;
    color: var(--lf2p-muted);
    font-weight: 600;
}

.lf2p-wallet-box {
    position: relative;
    z-index: 2;
    margin-top: 18px;
    padding: 18px;
    border-radius: 26px;
    background: linear-gradient(135deg, #ff4d6d, #ff9331);
    color: #fff;
    box-shadow: 0 18px 40px rgba(255, 94, 82, 0.22);
}

.lf2p-wallet-box strong {
    display: block;
    font-size: 24px;
    line-height: 1;
    font-weight: 900;
}

.lf2p-wallet-box span {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    line-height: 1.5;
    color: rgba(255,255,255,0.92);
    font-weight: 700;
}

.lf2p-wallet-list {
    margin: 16px 0 0 0;
    padding: 0;
    list-style: none;
}

.lf2p-wallet-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 0;
    font-size: 13px;
    color: #624a52;
    font-weight: 700;
}

.lf2p-wallet-list i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-style: normal;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 77, 109, 0.08);
    color: #ff4d6d;
    flex: 0 0 28px;
}

.lf2p-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
    gap: 18px;
    align-items: start;
}

.lf2p-form-card {
    padding: 24px;
}

.lf2p-top-panels {
    display: grid;
    grid-template-columns: 1.15fr .85fr;
    gap: 16px;
    margin-bottom: 16px;
}

.lf2p-photo-panel,
.lf2p-mini-panel {
    min-height: 100%;
    border-radius: 28px;
    border: 1px solid var(--lf2p-line);
    background: rgba(255,255,255,0.70);
    padding: 18px;
    box-shadow: var(--lf2p-soft-shadow);
}

.lf2p-photo-panel {
    display: flex;
    gap: 18px;
    align-items: center;
    flex-wrap: wrap;
}

.lf2p-photo-preview-wrap {
    width: 110px;
    flex: 0 0 110px;
    text-align: center;
}

.lf2p-photo-preview {
    width: 110px;
    height: 110px;
    border-radius: 28px;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.92);
    box-shadow: 0 16px 36px rgba(92, 61, 71, 0.14);
    background: #fff;
}

.lf2p-photo-tip {
    margin-top: 8px;
    font-size: 11px;
    color: var(--lf2p-muted);
    font-weight: 700;
}

.lf2p-photo-copy {
    flex: 1 1 280px;
}

.lf2p-panel-title {
    margin: 0;
    font-size: 20px;
    color: #442f37;
    font-weight: 900;
}

.lf2p-panel-desc {
    margin: 6px 0 16px 0;
    font-size: 13px;
    line-height: 1.6;
    color: var(--lf2p-muted);
    font-weight: 600;
}

.lf2p-file-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.lf2p-file-btn {
    position: relative;
    min-height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 18px;
    border-radius: 999px;
    background: linear-gradient(135deg, #ff4d6d, #ff9331);
    color: #fff;
    font-size: 14px;
    font-weight: 900;
    cursor: pointer;
    box-shadow: 0 16px 30px rgba(255, 94, 82, 0.18);
}

.lf2p-file-btn input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.lf2p-checkline {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-height: 42px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(255,255,255,0.84);
    border: 1px solid rgba(255, 77, 109, 0.10);
    color: #6a545c;
    font-size: 13px;
    font-weight: 800;
}

.lf2p-checkline input {
    width: 16px;
    height: 16px;
    accent-color: #ff4d6d;
}

.lf2p-mini-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 12px;
}

.lf2p-mini-avatar {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    object-fit: cover;
    border: 2px solid rgba(255,255,255,0.92);
    box-shadow: 0 12px 24px rgba(92, 61, 71, 0.12);
    background: #fff;
}

.lf2p-mini-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 999px;
    background: rgba(255, 244, 232, 0.92);
    color: #ff8a1a;
    font-size: 11px;
    font-weight: 900;
}

.lf2p-mini-name {
    margin: 0;
    font-size: 18px;
    color: #402c34;
    font-weight: 900;
}

.lf2p-mini-sub {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: var(--lf2p-muted);
    font-weight: 700;
}

.lf2p-mini-list {
    display: grid;
    gap: 10px;
    margin-top: 14px;
}

.lf2p-mini-item {
    padding: 12px 14px;
    border-radius: 18px;
    background: rgba(255,255,255,0.84);
    border: 1px solid rgba(255, 77, 109, 0.08);
}

.lf2p-mini-item strong {
    display: block;
    font-size: 13px;
    color: #4c353d;
    font-weight: 900;
}

.lf2p-mini-item span {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    line-height: 1.5;
    color: var(--lf2p-muted);
    font-weight: 700;
}

.lf2p-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.lf2p-field,
.lf2p-field-full {
    display: block;
}

.lf2p-field-full {
    grid-column: 1 / -1;
}

.lf2p-label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    color: #654d56;
    font-weight: 900;
}

.lf2p-input,
.lf2p-select,
.lf2p-textarea {
    width: 100%;
    border: 1px solid rgba(255, 77, 109, 0.12);
    background: rgba(255,255,255,0.90);
    color: #4c353d;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 10px 24px rgba(135, 77, 92, 0.05);
    outline: none;
    transition: all .22s ease;
    font-family: inherit;
}

.lf2p-input,
.lf2p-select {
    min-height: 56px;
    padding: 0 16px;
    border-radius: 18px;
    font-size: 15px;
    font-weight: 800;
}

.lf2p-textarea {
    min-height: 140px;
    resize: vertical;
    padding: 16px;
    border-radius: 22px;
    font-size: 15px;
    line-height: 1.65;
    font-weight: 700;
}

.lf2p-input:focus,
.lf2p-select:focus,
.lf2p-textarea:focus {
    border-color: rgba(255, 77, 109, 0.34);
    box-shadow: 0 0 0 4px rgba(255, 77, 109, 0.08), 0 16px 36px rgba(135, 77, 92, 0.08);
    background: #fff;
}

.lf2p-inline-note {
    display: block;
    margin-top: 7px;
    font-size: 12px;
    color: var(--lf2p-muted);
    font-weight: 700;
    line-height: 1.45;
}

.lf2p-switch-wrap {
    grid-column: 1 / -1;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 22px;
    background: rgba(255,255,255,0.74);
    border: 1px solid rgba(255, 77, 109, 0.10);
    box-shadow: var(--lf2p-soft-shadow);
}

.lf2p-switch {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #5f474f;
    font-weight: 800;
}

.lf2p-switch input {
    width: 18px;
    height: 18px;
    accent-color: #ff4d6d;
}

.lf2p-switch-copy {
    flex: 1 1 240px;
}

.lf2p-switch-copy strong {
    display: block;
    font-size: 15px;
    color: #4c353d;
    font-weight: 900;
}

.lf2p-switch-copy span {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: var(--lf2p-muted);
    line-height: 1.5;
    font-weight: 700;
}

.lf2p-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 18px;
}

.lf2p-save-btn {
    min-height: 58px;
    padding: 0 28px;
    border: 0;
    border-radius: 999px;
    background: linear-gradient(135deg, #ff3d67, #ff9331);
    color: #fff;
    font-size: 15px;
    font-weight: 900;
    box-shadow: 0 18px 34px rgba(255, 94, 82, 0.20);
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
}

.lf2p-save-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 22px 42px rgba(255, 94, 82, 0.26);
}

.lf2p-save-btn:active {
    transform: translateY(0);
}

.lf2p-preview-card {
    padding: 22px;
    position: sticky;
    top: 22px;
}

.lf2p-preview-head {
    margin-bottom: 16px;
}

.lf2p-preview-title {
    margin: 0;
    font-size: 24px;
    color: #442f37;
    font-weight: 900;
}

.lf2p-preview-desc {
    margin: 6px 0 0 0;
    font-size: 13px;
    line-height: 1.6;
    color: var(--lf2p-muted);
    font-weight: 700;
}

.lf2p-profile-card {
    position: relative;
    min-height: 390px;
    overflow: hidden;
    border-radius: 28px;
    background: linear-gradient(180deg, rgba(255,255,255,0.42), rgba(255,255,255,0.04)), url('<?php echo h(user_avatar($user)); ?>') center center / cover no-repeat;
    box-shadow: 0 24px 60px rgba(92, 61, 71, 0.14);
}

.lf2p-profile-card:before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 80% 18%, rgba(255,255,255,0.26), rgba(255,255,255,0) 34%),
        linear-gradient(180deg, rgba(0,0,0,0.08) 0%, rgba(0,0,0,0.10) 28%, rgba(0,0,0,0.68) 100%);
}

.lf2p-profile-content {
    position: absolute;
    left: 18px;
    right: 18px;
    bottom: 18px;
    z-index: 2;
    color: #fff;
}

.lf2p-profile-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.lf2p-profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.24);
    font-size: 11px;
    font-weight: 900;
    color: #fff;
}

.lf2p-profile-name {
    margin: 0;
    font-size: 28px;
    line-height: 1.05;
    font-weight: 900;
    letter-spacing: -.8px;
}

.lf2p-profile-meta {
    margin: 8px 0 0 0;
    font-size: 13px;
    line-height: 1.6;
    color: rgba(255,255,255,0.88);
    font-weight: 700;
}

.lf2p-preview-list {
    display: grid;
    gap: 10px;
    margin-top: 16px;
}

.lf2p-preview-item {
    padding: 14px 16px;
    border-radius: 20px;
    background: rgba(255,255,255,0.78);
    border: 1px solid rgba(255, 77, 109, 0.08);
    box-shadow: var(--lf2p-soft-shadow);
}

.lf2p-preview-item strong {
    display: block;
    font-size: 13px;
    color: #4a343c;
    font-weight: 900;
}

.lf2p-preview-item span {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    line-height: 1.5;
    color: var(--lf2p-muted);
    font-weight: 700;
}

@media (max-width: 1080px) {
    .lf2p-hero,
    .lf2p-layout {
        grid-template-columns: 1fr;
    }

    .lf2p-preview-card {
        position: static;
    }
}

@media (max-width: 860px) {
    .lf2p-shell {
        width: min(100% - 18px, 1000px);
        margin-top: 18px;
        margin-bottom: 24px;
    }

    .lf2p-hero-card,
    .lf2p-wallet-card,
    .lf2p-form-card,
    .lf2p-preview-card {
        border-radius: 26px;
    }

    .lf2p-top-panels,
    .lf2p-grid,
    .lf2p-hero-stats {
        grid-template-columns: 1fr;
    }

    .lf2p-photo-panel {
        align-items: flex-start;
    }

    .lf2p-form-card,
    .lf2p-wallet-card,
    .lf2p-preview-card,
    .lf2p-hero-card {
        padding: 18px;
    }
}

@media (max-width: 520px) {
    .lf2p-hero-title {
        font-size: 34px;
        letter-spacing: -1px;
    }

    .lf2p-photo-preview-wrap {
        width: 88px;
        flex-basis: 88px;
    }

    .lf2p-photo-preview {
        width: 88px;
        height: 88px;
        border-radius: 22px;
    }

    .lf2p-save-btn {
        width: 100%;
        justify-content: center;
    }

    .lf2p-profile-card {
        min-height: 320px;
    }
}
</style>

<section class="lf2p-shell">
    <div class="lf2p-inner">

        <div class="lf2p-hero">
            <div class="lf2p-hero-card">
                <span class="lf2p-kicker">💖 Perfil premium 2026</span>
                <h1 class="lf2p-hero-title">Meu perfil</h1>
                <p class="lf2p-hero-desc">
                    Controle sua apresentação, privacidade, preferências de descoberta e deixe seu perfil
                    com aparência mais forte, moderna e impactante dentro do Love &amp; Fire.
                </p>

                <div class="lf2p-hero-stats">
                    <div class="lf2p-stat">
                        <strong>Visual</strong>
                        <span>Seu perfil é a primeira impressão dentro do app.</span>
                    </div>
                    <div class="lf2p-stat">
                        <strong>DMs</strong>
                        <span>Defina com quem e como deseja receber mensagens.</span>
                    </div>
                    <div class="lf2p-stat">
                        <strong>BIX</strong>
                        <span>Área reservada para a futura wallet da plataforma.</span>
                    </div>
                </div>
            </div>

            <div class="lf2p-wallet-card">
                <div class="lf2p-wallet-head">
                    <span class="lf2p-wallet-badge">🪙 Wallet BIX · reservado</span>
                    <h2 class="lf2p-wallet-title">Sua carteira BIX</h2>
                    <p class="lf2p-wallet-desc">
                        Espaço preparado para a moeda digital do Love &amp; Fire. Aqui futuramente você poderá
                        usar BIX para Flechas do Cupido, boosts, destaques e outros recursos premium.
                    </p>
                </div>

                <div class="lf2p-wallet-box">
                    <strong>0.00 BIX</strong>
                    <span>Saldo ilustrativo por enquanto. Recurso ainda não funcional.</span>
                </div>

                <ul class="lf2p-wallet-list">
                    <li><i>🏹</i> Comprar Flechas do Cupido</li>
                    <li><i>🔥</i> Impulsionar visibilidade</li>
                    <li><i>⭐</i> Destaques premium do perfil</li>
                    <li><i>💬</i> Recursos especiais de interação</li>
                </ul>
            </div>
        </div>

        <div class="lf2p-layout">
            <div class="lf2p-form-card">
                <form method="post" enctype="multipart/form-data" autocomplete="off">
                    <?php echo csrf_field(); ?>

                    <div class="lf2p-top-panels">
                        <div class="lf2p-photo-panel">
                            <div class="lf2p-photo-preview-wrap">
                                <img
                                    id="lf2pProfilePreview"
                                    class="lf2p-photo-preview"
                                    src="<?php echo h(user_avatar($user)); ?>"
                                    alt="<?php echo h($currentName); ?>"
                                >
                                <div class="lf2p-photo-tip">Foto principal</div>
                            </div>

                            <div class="lf2p-photo-copy">
                                <h3 class="lf2p-panel-title">Foto principal do perfil</h3>
                                <p class="lf2p-panel-desc">
                                    Essa foto aparece no seu perfil, nas conversas e na identidade visual da sua conta.
                                </p>

                                <div class="lf2p-file-row">
                                    <label class="lf2p-file-btn">
                                        Trocar foto
                                        <input
                                            id="lf2pProfilePhotoInput"
                                            type="file"
                                            name="avatar_file"
                                            accept="image/png,image/jpeg,image/webp,image/gif"
                                        >
                                    </label>

                                    <?php if ($currentAvatar !== '') { ?>
                                        <label class="lf2p-checkline">
                                            <input id="lf2pRemoveAvatar" type="checkbox" name="remove_avatar" value="1">
                                            Remover foto atual
                                        </label>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="lf2p-mini-panel">
                            <div class="lf2p-mini-top">
                                <div>
                                    <span class="lf2p-mini-chip">✨ Prévia do perfil</span>
                                    <h3 class="lf2p-mini-name"><?php echo h($firstName); ?></h3>
                                    <p class="lf2p-mini-sub"><?php echo h($currentCity !== '' ? $currentCity : 'Sua cidade'); ?> · <?php echo h($currentCountry !== '' ? $currentCountry : 'Seu país'); ?></p>
                                </div>

                                <img class="lf2p-mini-avatar" src="<?php echo h(user_avatar($user)); ?>" alt="<?php echo h($currentName); ?>">
                            </div>

                            <div class="lf2p-mini-list">
                                <div class="lf2p-mini-item">
                                    <strong>Visibilidade</strong>
                                    <span>Quanto mais completo o perfil, melhor a experiência no app.</span>
                                </div>
                                <div class="lf2p-mini-item">
                                    <strong>Privacidade</strong>
                                    <span>Escolha se deseja DMs abertas, apenas matches ou perfil fechado para mensagens.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lf2p-grid">
                        <label class="lf2p-field">
                            <span class="lf2p-label">Nome</span>
                            <input class="lf2p-input" type="text" name="name" value="<?php echo h($currentName); ?>">
                            <small class="lf2p-inline-note">Esse é o nome exibido nas conversas e no perfil.</small>
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Cidade</span>
                            <input class="lf2p-input" type="text" name="city" value="<?php echo h($currentCity); ?>">
                            <small class="lf2p-inline-note">Ajuda na descoberta de pessoas próximas.</small>
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">País</span>
                            <input class="lf2p-input" type="text" name="country" value="<?php echo h($currentCountry); ?>">
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Gênero</span>
                            <select class="lf2p-select" name="gender">
                                <option value="" <?php if ($currentGender === '') echo 'selected="selected"'; ?>>Prefiro não informar</option>
                                <option value="female" <?php if ($currentGender === 'female') echo 'selected="selected"'; ?>>Feminino</option>
                                <option value="male" <?php if ($currentGender === 'male') echo 'selected="selected"'; ?>>Masculino</option>
                                <option value="non_binary" <?php if ($currentGender === 'non_binary') echo 'selected="selected"'; ?>>Não binário</option>
                                <option value="other" <?php if ($currentGender === 'other') echo 'selected="selected"'; ?>>Outro</option>
                            </select>
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Quem quero encontrar</span>
                            <select class="lf2p-select" name="looking_for">
                                <option value="all" <?php if ($prefLookingFor === 'all') echo 'selected="selected"'; ?>>Todos</option>
                                <option value="female" <?php if ($prefLookingFor === 'female') echo 'selected="selected"'; ?>>Mulheres</option>
                                <option value="male" <?php if ($prefLookingFor === 'male') echo 'selected="selected"'; ?>>Homens</option>
                                <option value="non_binary" <?php if ($prefLookingFor === 'non_binary') echo 'selected="selected"'; ?>>Não binário</option>
                            </select>
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Idade mínima</span>
                            <input class="lf2p-input" type="number" name="min_age" min="18" max="99" value="<?php echo h($prefMinAge); ?>">
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Idade máxima</span>
                            <input class="lf2p-input" type="number" name="max_age" min="18" max="99" value="<?php echo h($prefMaxAge); ?>">
                        </label>

                        <label class="lf2p-field">
                            <span class="lf2p-label">Privacidade das DMs</span>
                            <select class="lf2p-select" name="dm_mode">
                                <option value="open" <?php if ($currentDmMode === 'open') echo 'selected="selected"'; ?>>Aberta com limite anti-spam</option>
                                <option value="matches" <?php if ($currentDmMode === 'matches') echo 'selected="selected"'; ?>>Apenas matches</option>
                                <option value="closed" <?php if ($currentDmMode === 'closed') echo 'selected="selected"'; ?>>Fechada</option>
                            </select>
                            <small class="lf2p-inline-note">Você controla quem pode iniciar conversa com você.</small>
                        </label>

                        <div class="lf2p-switch-wrap">
                            <label class="lf2p-switch">
                                <input type="checkbox" name="verified_only" value="1" <?php if ($prefVerifiedOnly === 1) echo 'checked="checked"'; ?>>
                                Preferir perfis verificados
                            </label>

                            <div class="lf2p-switch-copy">
                                <strong>Descoberta mais segura</strong>
                                <span>Ao ativar essa opção, o app prioriza perfis com sinais de confiança dentro das descobertas.</span>
                            </div>
                        </div>

                        <label class="lf2p-field-full">
                            <span class="lf2p-label">Bio</span>
                            <textarea class="lf2p-textarea" name="bio" rows="6"><?php echo h($currentBio); ?></textarea>
                            <small class="lf2p-inline-note">Escreva algo leve, real e atraente sobre você. Isso ajuda a criar melhores conexões.</small>
                        </label>
                    </div>

                    <div class="lf2p-actions">
                        <button class="lf2p-save-btn" type="submit">Salvar perfil</button>
                    </div>
                </form>
            </div>

            <aside class="lf2p-preview-card">
                <div class="lf2p-preview-head">
                    <h3 class="lf2p-preview-title">Como seu perfil aparece</h3>
                    <p class="lf2p-preview-desc">
                        Uma prévia visual do seu espaço dentro do Love &amp; Fire.
                    </p>
                </div>

                <div class="lf2p-profile-card" id="lf2pPreviewCard">
                    <div class="lf2p-profile-content">
                        <div class="lf2p-profile-badges">
                            <span class="lf2p-profile-badge">💬 DMs</span>
                            <span class="lf2p-profile-badge">✨ Premium look</span>
                            <span class="lf2p-profile-badge">🪙 BIX</span>
                        </div>

                        <h4 class="lf2p-profile-name" id="lf2pLiveName"><?php echo h($firstName); ?></h4>
                        <p class="lf2p-profile-meta" id="lf2pLiveMeta">
                            <?php echo h($currentCity !== '' ? $currentCity : 'Sua cidade'); ?> · <?php echo h($currentCountry !== '' ? $currentCountry : 'Seu país'); ?>
                        </p>
                    </div>
                </div>

                <div class="lf2p-preview-list">
                    <div class="lf2p-preview-item">
                        <strong>Wallet BIX</strong>
                        <span>Área reservada para futura compra de Flechas do Cupido, boosts e destaques.</span>
                    </div>

                    <div class="lf2p-preview-item">
                        <strong>Privacidade inteligente</strong>
                        <span>As configurações do formulário já controlam o comportamento das DMs e descoberta.</span>
                    </div>

                    <div class="lf2p-preview-item">
                        <strong>Visual forte e romântico</strong>
                        <span>Seu perfil agora acompanha o mesmo padrão premium do chat e da home.</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

<script>
(function () {
    var photoInput = document.getElementById('lf2pProfilePhotoInput');
    var preview = document.getElementById('lf2pProfilePreview');
    var removeAvatar = document.getElementById('lf2pRemoveAvatar');
    var liveName = document.getElementById('lf2pLiveName');
    var liveMeta = document.getElementById('lf2pLiveMeta');
    var previewCard = document.getElementById('lf2pPreviewCard');

    var nameInput = document.querySelector('input[name="name"]');
    var cityInput = document.querySelector('input[name="city"]');
    var countryInput = document.querySelector('input[name="country"]');

    function firstWord(str) {
        str = (str || '').replace(/^\s+|\s+$/g, '');
        if (!str) {
            return 'Perfil';
        }
        var parts = str.split(/\s+/);
        return parts[0] || 'Perfil';
    }

    function updateLivePreview() {
        var name = nameInput ? nameInput.value : '';
        var city = cityInput ? cityInput.value : '';
        var country = countryInput ? countryInput.value : '';

        if (liveName) {
            liveName.textContent = firstWord(name);
        }

        if (liveMeta) {
            var cityText = city.replace(/^\s+|\s+$/g, '') || 'Sua cidade';
            var countryText = country.replace(/^\s+|\s+$/g, '') || 'Seu país';
            liveMeta.textContent = cityText + ' · ' + countryText;
        }
    }

    if (nameInput) {
        nameInput.addEventListener('input', updateLivePreview);
    }

    if (cityInput) {
        cityInput.addEventListener('input', updateLivePreview);
    }

    if (countryInput) {
        countryInput.addEventListener('input', updateLivePreview);
    }

    if (photoInput) {
        photoInput.addEventListener('change', function () {
            if (!photoInput.files || !photoInput.files[0]) {
                return;
            }

            var file = photoInput.files[0];
            var reader = new FileReader();

            reader.onload = function (e) {
                if (preview) {
                    preview.src = e.target.result;
                }

                var miniAvatar = document.querySelector('.lf2p-mini-avatar');
                if (miniAvatar) {
                    miniAvatar.src = e.target.result;
                }

                if (previewCard) {
                    previewCard.style.backgroundImage = 'linear-gradient(180deg, rgba(255,255,255,0.42), rgba(255,255,255,0.04)), url("' + e.target.result + '")';
                    previewCard.style.backgroundPosition = 'center center';
                    previewCard.style.backgroundSize = 'cover';
                    previewCard.style.backgroundRepeat = 'no-repeat';
                }

                if (removeAvatar) {
                    removeAvatar.checked = false;
                }
            };

            reader.readAsDataURL(file);
        });
    }

    if (removeAvatar) {
        removeAvatar.addEventListener('change', function () {
            if (!removeAvatar.checked) {
                return;
            }
        });
    }

    updateLivePreview();
})();
</script>

<?php render_footer(); ?>