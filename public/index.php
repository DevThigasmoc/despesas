<?php
require __DIR__ . '/../app/helpers.php';

if (current_user()) {
    redirect('/public/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $login = trim((string)($_POST['login'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, email, password_hash FROM users WHERE email = :login LIMIT 1');
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$user['id'], 'email' => $user['email']];
        flash('success', 'Login realizado com sucesso.');
        redirect('/public/dashboard.php');
    }

    flash('danger', 'Credenciais inválidas.');
    redirect('/public/index.php');
}

render_header('Login');
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Entrar</h1>
                <form method="post">
                    <?= csrf_input() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="login" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha</label>
                        <input class="form-control" type="password" name="password" required>
                    </div>
                    <button class="btn btn-primary w-100">Acessar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
