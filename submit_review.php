<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/security.php';
require_once dirname(__DIR__, 2) . '/app/vendors.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/index.php');
}

$vendorId = filter_input(INPUT_POST, 'vendor_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = trim((string) ($_POST['comment'] ?? ''));
$honeypot = trim((string) ($_POST['website'] ?? ''));
$csrf = (string) ($_POST['csrf_token'] ?? '');

if (!verify_csrf($csrf)) {
    flash('error', 'Sessão expirada. Tente enviar novamente.');
    redirect_to('/index.php');
}

if ($honeypot !== '') {
    flash('error', 'Não foi possível registrar a avaliação.');
    redirect_to('/index.php');
}

if (!$vendorId || !$rating || $rating < 1 || $rating > 5) {
    flash('error', 'Escolha uma nota de 1 a 5 estrelas.');
    redirect_to('/index.php');
}

$vendor = vendor_by_id((int) $vendorId);
if (!$vendor) {
    flash('error', 'Vendedor inválido.');
    redirect_to('/index.php');
}

if (mb_strlen($comment) > MAX_COMMENT_LENGTH) {
    flash('error', 'O comentário passou do limite permitido.');
    redirect_to('/avaliar.php?vendedor=' . urlencode($vendor['slug']));
}

$lockCookie = isset($_COOKIE['review_lock']) ? (int) $_COOKIE['review_lock'] : 0;
if ($lockCookie > 0 && time() - $lockCookie < spam_cooldown_seconds()) {
    flash('error', 'Aguarde alguns instantes antes de enviar outra avaliação.');
    redirect_to('/avaliar.php?vendedor=' . urlencode($vendor['slug']));
}

$clientToken = client_token();
$identity = [
    'ip_hash' => identity_hash(request_ip()),
    'user_agent_hash' => identity_hash(user_agent()),
    'client_token_hash' => identity_hash($clientToken),
    'session_id_hash' => identity_hash(session_id()),
];

$cooldown = (new DateTimeImmutable())
    ->modify('-' . spam_cooldown_seconds() . ' seconds')
    ->format('Y-m-d H:i:s');

$spamCheck = db()->prepare(
    'SELECT COUNT(*) AS total
     FROM reviews
     WHERE created_at >= :cooldown
       AND (
           client_token_hash = :client_token_hash
           OR session_id_hash = :session_id_hash
       )'
);
$spamCheck->execute([
    'cooldown' => $cooldown,
    'client_token_hash' => $identity['client_token_hash'],
    'session_id_hash' => $identity['session_id_hash'],
]);

if ((int) $spamCheck->fetchColumn() > 0) {
    flash('error', 'Aguarde alguns instantes antes de enviar outra avaliação.');
    redirect_to('/avaliar.php?vendedor=' . urlencode($vendor['slug']));
}

$evaluationUuid = make_uuid();

$insert = db()->prepare(
    'INSERT INTO reviews
        (evaluation_uuid, vendor_id, rating, comment, ip_hash, user_agent_hash, client_token_hash, session_id_hash, created_at)
     VALUES
        (:evaluation_uuid, :vendor_id, :rating, :comment, :ip_hash, :user_agent_hash, :client_token_hash, :session_id_hash, NOW())'
);
$insert->execute([
    'evaluation_uuid' => $evaluationUuid,
    'vendor_id' => $vendor['id'],
    'rating' => $rating,
    'comment' => $comment !== '' ? $comment : null,
    'ip_hash' => $identity['ip_hash'],
    'user_agent_hash' => $identity['user_agent_hash'],
    'client_token_hash' => $identity['client_token_hash'],
    'session_id_hash' => $identity['session_id_hash'],
]);

$_SESSION['last_evaluation_uuid'] = $evaluationUuid;
setcookie('review_lock', (string) time(), cookie_options(time() + spam_cooldown_seconds()));

redirect_to('/obrigado.php?id=' . urlencode($evaluationUuid));
