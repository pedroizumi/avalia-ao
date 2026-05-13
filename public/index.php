<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/security.php';
require_once dirname(__DIR__) . '/app/vendors.php';

start_secure_session();
client_token();

$vendors = active_vendors();
$flash = consume_flash();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="app-body">
    <main class="selection-shell">
        <section class="selection-hero" aria-labelledby="page-title">
            <div class="brand-mark" aria-hidden="true">AV</div>
            <p class="eyebrow">Avaliação rápida</p>
            <h1 id="page-title">Como foi seu atendimento?</h1>
            <p class="hero-copy">Toque na foto de quem atendeu você e registre sua experiência em poucos segundos.</p>

            <?php if ($flash): ?>
                <div class="flash flash-<?= h($flash['type']) ?>" role="status">
                    <?= h($flash['message']) ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="vendor-grid" aria-label="Escolha o vendedor">
            <?php foreach ($vendors as $vendor): ?>
                <a class="vendor-card"
                   href="/avaliar.php?vendedor=<?= h($vendor['slug']) ?>"
                   aria-label="Escolher <?= h($vendor['name']) ?>">
                    <img src="/<?= h($vendor['photo_path']) ?>" alt="Foto ilustrativa do vendedor" loading="eager">
                    <span class="vendor-card-overlay">
                        <span>Escolher</span>
                    </span>
                    <span class="vendor-card-name"><?= h($vendor['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </section>

        <footer class="public-footer">
            <span>QR Code pronto para uso</span>
            <a href="/admin/login.php">Admin</a>
        </footer>
    </main>
    <script src="/js/app.js" defer></script>
</body>
</html>

