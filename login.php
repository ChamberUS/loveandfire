<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
if (auth_current_user()) { redirect('dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = post_text('email', 160);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $result = auth_login($email, $password);
    if ($result[0]) {
        flash_set('ok', $result[1]);
        redirect('dashboard.php');
    } else {
        flash_set('err', $result[1]);
    }
}

render_header('Entrar', 'login');
?>
<section class="hc-auth-card">
    <div class="hc-auth-logo"></div>
    <h1>Entrar</h1>
    <p>Volte para suas conversas, curtidas e novos encontros.</p>
    <form method="post" class="hc-form">
        <?php echo csrf_field(); ?>
        <label>E-mail <input type="email" name="email" required></label>
        <label>Senha <input type="password" name="password" required></label>
        <button class="hc-btn hc-btn-primary" type="submit">Entrar</button>
    </form>
</section>
<?php render_footer(); ?>
