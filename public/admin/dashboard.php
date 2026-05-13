<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';

require_admin();

$totalReviews = (int) db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$totalSales = $totalReviews;

$vendorStatsStmt = db()->query(
    'SELECT
        v.id,
        v.name,
        v.slug,
        v.photo_path,
        COUNT(r.id) AS total_reviews,
        COALESCE(ROUND(AVG(r.rating), 2), 0) AS average_rating
     FROM vendors v
     LEFT JOIN reviews r ON r.vendor_id = v.id
     GROUP BY v.id, v.name, v.slug, v.photo_path, v.display_order
     ORDER BY average_rating DESC, total_reviews DESC, v.display_order ASC'
);
$vendorStats = $vendorStatsStmt->fetchAll();

$commentsStmt = db()->query(
    'SELECT
        r.evaluation_uuid,
        r.rating,
        r.comment,
        r.created_at,
        v.name AS vendor_name
     FROM reviews r
     INNER JOIN vendors v ON v.id = r.vendor_id
     WHERE r.comment IS NOT NULL AND TRIM(r.comment) <> ""
     ORDER BY r.created_at DESC
     LIMIT 12'
);
$comments = $commentsStmt->fetchAll();

$distribution = [];
for ($rating = 1; $rating <= 5; $rating++) {
    $distribution[$rating] = 0;
}

$distStmt = db()->query(
    'SELECT rating, COUNT(*) AS total
     FROM reviews
     GROUP BY rating
     ORDER BY rating ASC'
);

foreach ($distStmt->fetchAll() as $row) {
    $distribution[(int) $row['rating']] = (int) $row['total'];
}

$dashboardData = [
    'vendors' => array_map(static fn (array $vendor): array => [
        'name' => $vendor['name'],
        'average' => (float) $vendor['average_rating'],
        'reviews' => (int) $vendor['total_reviews'],
    ], $vendorStats),
    'distribution' => $distribution,
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard admin | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="dashboard-body">
    <aside class="dashboard-sidebar">
        <div class="brand-block">
            <div class="brand-mark" aria-hidden="true">AV</div>
            <div>
                <strong>Admin</strong>
                <span>Avaliações</span>
            </div>
        </div>
        <nav aria-label="Navegação administrativa">
            <a class="active" href="/admin/dashboard.php">Dashboard</a>
            <a href="/index.php">Página pública</a>
            <a href="/admin/logout.php">Sair</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-header">
            <div>
                <p class="eyebrow">Painel administrativo</p>
                <h1>Performance dos vendedores</h1>
            </div>
            <div class="admin-badge">
                <?= h((string) ($_SESSION['admin_name'] ?? 'Admin')) ?>
            </div>
        </header>

        <section class="metric-grid" aria-label="Resumo">
            <article class="metric-card">
                <span>Total de avaliações</span>
                <strong><?= $totalReviews ?></strong>
            </article>
            <article class="metric-card accent-green">
                <span>Total de vendas</span>
                <strong><?= $totalSales ?></strong>
            </article>
            <?php foreach ($vendorStats as $vendor): ?>
                <article class="metric-card">
                    <span>Média do <?= h($vendor['name']) ?></span>
                    <strong>&#9733; <?= number_format((float) $vendor['average_rating'], 1, ',', '.') ?></strong>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="dashboard-grid">
            <article class="panel">
                <div class="panel-heading">
                    <h2>Média por vendedor</h2>
                    <span>1 a 5 estrelas</span>
                </div>
                <div id="averageChart" class="chart-list" aria-label="Gráfico de média dos vendedores"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <h2>Vendas por vendedor</h2>
                    <span>1 avaliação = 1 venda</span>
                </div>
                <div id="reviewChart" class="chart-list" aria-label="Gráfico de vendas por vendedor"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <h2>Ranking</h2>
                    <span>Ordenado por média</span>
                </div>
                <ol class="ranking-list">
                    <?php foreach ($vendorStats as $index => $vendor): ?>
                        <li>
                            <span class="rank-position"><?= $index + 1 ?></span>
                            <img src="/<?= h($vendor['photo_path']) ?>" alt="Foto de <?= h($vendor['name']) ?>">
                            <div>
                                <strong><?= h($vendor['name']) ?></strong>
                                <small><?= (int) $vendor['total_reviews'] ?> vendas / avaliações</small>
                            </div>
                            <span class="rank-score">&#9733; <?= number_format((float) $vendor['average_rating'], 1, ',', '.') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <h2>Distribuição de notas</h2>
                    <span>Todos os vendedores</span>
                </div>
                <div id="ratingDistribution" class="distribution-chart" aria-label="Distribuição de notas"></div>
            </article>
        </section>

        <section class="panel comments-panel">
            <div class="panel-heading">
                <h2>Comentários recentes</h2>
                <span><?= count($comments) ?> últimos com texto</span>
            </div>

            <?php if (!$comments): ?>
                <p class="empty-state">Nenhum comentário recebido ainda.</p>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <article class="comment-card">
                            <div>
                                <strong><?= h($comment['vendor_name']) ?></strong>
                                <span>&#9733; <?= (int) $comment['rating'] ?></span>
                            </div>
                            <p><?= h($comment['comment']) ?></p>
                            <small><?= h(date('d/m/Y H:i', strtotime((string) $comment['created_at']))) ?> · <?= h($comment['evaluation_uuid']) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script id="dashboard-data" type="application/json">
        <?= json_encode($dashboardData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>
    <script src="/js/admin.js" defer></script>
</body>
</html>

