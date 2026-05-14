<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/security.php';
require_once dirname(__DIR__) . '/app/vendors.php';

start_secure_session();
client_token();

$slug = isset($_GET['vendedor']) ? trim((string) $_GET['vendedor']) : '';
$vendor = $slug !== '' ? vendor_by_slug($slug) : null;

if (!$vendor) {
    flash('error', 'Vendedor não encontrado. Escolha uma das fotos para avaliar.');
    redirect_to('/index.php');
}

$summary = vendor_rating_summary((int) $vendor['id']);
$comments = vendor_recent_comments((int) $vendor['id'], 5);
$flash = consume_flash();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avaliar <?= h($vendor['name']) ?> | <?= h(APP_NAME) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="app-body">
    <main class="review-shell">
        <a class="back-link" href="/index.php" aria-label="Voltar para a escolha do vendedor">
            <span aria-hidden="true">&larr;</span> Trocar vendedor
        </a>

        <section class="review-card" aria-labelledby="review-title">
            <div class="review-person">
                <img src="/<?= h($vendor['photo_path']) ?>" alt="Foto ilustrativa de <?= h($vendor['name']) ?>">
                <div>
                    <p class="eyebrow">Você escolheu</p>
                    <h1 id="review-title"><?= h($vendor['name']) ?></h1>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="flash flash-<?= h($flash['type']) ?>" role="alert">
                    <?= h($flash['message']) ?>
                </div>
            <?php endif; ?>

            <section class="review-insights" aria-label="Resumo das avaliações do vendedor">
                <article class="score-panel">
                    <span>Média de avaliações</span>
                    <?php if ($summary['total_reviews'] > 0): ?>
                        <strong>&#9733; <?= number_format($summary['average_rating'], 1, ',', '.') ?></strong>
                        <small><?= $summary['total_reviews'] ?> avaliação<?= $summary['total_reviews'] === 1 ? '' : 'ões' ?></small>
                    <?php else: ?>
                        <strong>Sem notas</strong>
                        <small>Seja o primeiro a avaliar</small>
                    <?php endif; ?>
                </article>

                <div class="public-comments">
                    <div class="public-comments-heading">
                        <h2>Comentários recentes</h2>
                        <span><?= count($comments) ?> exibido<?= count($comments) === 1 ? '' : 's' ?></span>
                    </div>

                    <?php if (!$comments): ?>
                        <p class="empty-state">Ainda não há comentários para este vendedor.</p>
                    <?php else: ?>
                        <div class="public-comments-list">
                            <?php foreach ($comments as $comment): ?>
                                <article class="public-comment">
                                    <div>
                                        <span>&#9733; <?= (int) $comment['rating'] ?></span>
                                        <small><?= h(date('d/m/Y', strtotime((string) $comment['created_at']))) ?></small>
                                    </div>
                                    <p><?= h($comment['comment']) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <form class="rating-form" action="/actions/submit_review.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="vendor_id" value="<?= (int) $vendor['id'] ?>">
                <label class="hidden-field">
                    Website
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </label>

                <fieldset class="stars-fieldset">
                    <legend>Escolha sua nota</legend>
                    <div class="star-rating" role="radiogroup" aria-label="Nota de 1 a 5 estrelas">
                        <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                            <input type="radio" id="star-<?= $rating ?>" name="rating" value="<?= $rating ?>" required>
                            <label for="star-<?= $rating ?>" title="<?= $rating ?> estrela<?= $rating > 1 ? 's' : '' ?>">
                                &#9733;
                            </label>
                        <?php endfor; ?>
                    </div>
                    <p class="rating-hint" data-rating-hint>Toque nas estrelas para avaliar.</p>
                </fieldset>

                <label class="comment-label" for="comment">Comentário opcional</label>
                <textarea id="comment" name="comment" maxlength="<?= MAX_COMMENT_LENGTH ?>" rows="5"
                          placeholder="Conte rapidamente o que você achou do atendimento."></textarea>
                <div class="form-meta">
                    <span data-comment-counter>0/<?= MAX_COMMENT_LENGTH ?></span>
                    <span>Proteção anti-spam ativa</span>
                </div>

                <button class="primary-button" type="submit">Enviar avaliação</button>
            </form>
        </section>
    </main>
    <script src="/js/app.js" defer></script>
</body>
</html>
