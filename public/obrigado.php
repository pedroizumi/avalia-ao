<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/security.php';

start_secure_session();

$evaluationId = isset($_GET['id']) ? trim((string) $_GET['id']) : ($_SESSION['last_evaluation_uuid'] ?? '');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Obrigado | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="app-body">
    <main class="thanks-shell">
        <section class="thanks-card">
            <div class="success-icon" aria-hidden="true">&#10003;</div>
            <p class="eyebrow">Avaliação enviada</p>
            <h1>Obrigado pelo feedback.</h1>
            <p>Sua avaliação foi registrada e já conta como uma venda no painel administrativo.</p>

            <?php if ($evaluationId !== ''): ?>
                <div class="evaluation-id">
                    <span>Identificador</span>
                    <strong><?= h($evaluationId) ?></strong>
                </div>
            <?php endif; ?>

            <a class="primary-button as-link" href="/index.php">Fazer outra avaliação</a>
        </section>
    </main>
</body>
</html>

