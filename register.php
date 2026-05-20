<?php
require_once dirname(__FILE__) . '/core/bootstrap.php';
if (auth_current_user()) { redirect('dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = post_text('name', 120);
    $email = post_text('email', 160);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $birthDate = post_text('birth_date', 10);
    $gender = post_text('gender', 30);
    $city = post_text('city', 80);
    $country = post_text('country', 80);

    if (!$name || !$email || !$password || !$birthDate) {
        flash_set('err', 'Preencha nome, e-mail, senha e nascimento.');
    } else {
        $result = auth_register($name, $email, $password, $birthDate, $gender, $city, $country);
        if ($result[0]) {
            flash_set('ok', $result[1]);
            redirect('dashboard.php');
        } else {
            flash_set('err', $result[1]);
        }
    }
}

render_header('Criar conta', 'register');
?>
<section class="hc-auth-card">
    <div class="hc-auth-logo"></div>
    <h1>Criar conta</h1>
    <p>Comece com dados basicos. Depois voce pode completar seu perfil e seus interesses.</p>
    <form method="post" class="hc-form">
        <?php echo csrf_field(); ?>
        <label>Nome <input type="text" name="name" required></label>
        <label>E-mail <input type="email" name="email" required></label>
        <label>Senha <input type="password" name="password" required></label>
        <label>Data de nascimento <input type="date" name="birth_date" required></label>
        <label>Genero
            <select name="gender">
                <option value="">Prefiro nao informar</option>
                <option value="female">Feminino</option>
                <option value="male">Masculino</option>
                <option value="non_binary">Nao binario</option>
                <option value="other">Outro</option>
            </select>
        </label>
        <label>Cidade <input type="text" name="city"></label>
        <label>Pais <input type="text" name="country" value="Brasil"></label>
        <button class="hc-btn hc-btn-primary" type="submit">Criar conta</button>
    </form>
</section>
<?php render_footer(); ?>
