<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';

start_secure_session();

if (admin_is_logged_in()) {
    redirect_to('/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $csrf = (string) ($_POST['csrf_token'] ?? '');

    if (!verify_csrf($csrf)) {
        $error = 'Sessão expirada. Recarregue a página e tente novamente.';
    } elseif ($username === '' || $password === '') {
        $error = 'Informe usuário e senha.';
    } elseif (login_admin($username, $password)) {
        redirect_to('/admin/dashboard.php');
    } else {
        $error = 'Usuário ou senha inválidos.';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login admin | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="admin-login-body">
    <main class="login-shell">
        <section class="login-card" aria-labelledby="login-title">
            <div class="brand-mark" aria-hidden="true">AV</div>
            <p class="eyebrow">Área administrativa</p>
            <h1 id="login-title">Entrar no painel</h1>

            <?php if ($error !== ''): ?>
                <div class="flash flash-error" role="alert"><?= h($error) ?></div>
            <?php endif; ?>

            <form class="admin-form" method="post" action="/admin/login.php">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <label for="username">Usuário</label>
                <input id="username" name="username" type="text" autocomplete="username" required>

                <label for="password">Senha</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>

                <button class="primary-button" type="submit">Acessar dashboard</button>
            </form>
        </section>
    </main>
</body>
</html>

