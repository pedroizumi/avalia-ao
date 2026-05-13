<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

function admin_is_logged_in(): bool
{
    start_secure_session();

    return isset($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_is_logged_in()) {
        redirect_to('/admin/login.php');
    }
}

function login_admin(string $username, string $password): bool
{
    start_secure_session();

    $stmt = db()->prepare(
        'SELECT id, username, password_hash, full_name
         FROM admins
         WHERE username = :username AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['username' => $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        usleep(250000);
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['full_name'] ?: $admin['username'];

    $update = db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
    $update->execute(['id' => $admin['id']]);

    return true;
}

function logout_admin(): void
{
    start_secure_session();
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
    session_regenerate_id(true);
}

