<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

const DEFAULT_LOCAL_ADMIN_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi';

function log_line(string $message): void
{
    fwrite(STDOUT, '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL);
}

function migration_db_with_retry(): PDO
{
    $attempts = (int) (env_value('DB_CONNECT_RETRIES', '30') ?? '30');
    $sleep = (int) (env_value('DB_CONNECT_SLEEP_SECONDS', '2') ?? '2');
    $lastError = null;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
        try {
            return db();
        } catch (Throwable $error) {
            $lastError = $error;
            log_line("Banco ainda indisponível. Tentativa {$attempt}/{$attempts}.");
            sleep($sleep);
        }
    }

    throw $lastError ?? new RuntimeException('Não foi possível conectar ao banco.');
}

function exec_sql_batch(PDO $pdo, string $sql): void
{
    $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

    foreach ($statements as $statement) {
        $statement = trim($statement);

        if ($statement === '') {
            continue;
        }

        $pdo->exec($statement);
    }
}

function apply_migrations(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS schema_migrations (
            migration VARCHAR(190) PRIMARY KEY,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $files = glob(dirname(__DIR__) . '/database/migrations/*.sql') ?: [];
    sort($files);

    foreach ($files as $file) {
        $name = basename($file);
        $check = $pdo->prepare('SELECT COUNT(*) FROM schema_migrations WHERE migration = :migration');
        $check->execute(['migration' => $name]);

        if ((int) $check->fetchColumn() > 0) {
            log_line("Migration já aplicada: {$name}");
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException("Não foi possível ler {$name}");
        }

        log_line("Aplicando migration: {$name}");
        exec_sql_batch($pdo, $sql);
        $insert = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
        $insert->execute(['migration' => $name]);
    }
}

function seed_defaults(PDO $pdo): void
{
    $adminHash = env_value('ADMIN_PASSWORD_HASH');

    if (is_production() && !$adminHash) {
        throw new RuntimeException('Defina ADMIN_PASSWORD_HASH em produção antes de executar seeds.');
    }

    $adminHash = $adminHash ?: DEFAULT_LOCAL_ADMIN_HASH;
    $adminUsername = env_value('ADMIN_USERNAME', 'admin') ?? 'admin';
    $adminFullName = env_value('ADMIN_FULL_NAME', 'Administrador') ?? 'Administrador';

    $vendors = [
        ['Nicolas', 'nicolas', env_value('NICOLAS_PHOTO_PATH', 'images/nicolas-placeholder.svg') ?? 'images/nicolas-placeholder.svg', 1],
        ['Gabriel', 'gabriel', env_value('GABRIEL_PHOTO_PATH', 'images/gabriel-placeholder.svg') ?? 'images/gabriel-placeholder.svg', 2],
    ];

    $vendorStmt = $pdo->prepare(
        'INSERT INTO vendors (name, slug, photo_path, is_active, display_order)
         VALUES (:name, :slug, :photo_path, 1, :display_order)
         ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            photo_path = VALUES(photo_path),
            is_active = VALUES(is_active),
            display_order = VALUES(display_order)'
    );

    foreach ($vendors as [$name, $slug, $photoPath, $order]) {
        $vendorStmt->execute([
            'name' => $name,
            'slug' => $slug,
            'photo_path' => $photoPath,
            'display_order' => $order,
        ]);
    }

    $adminStmt = $pdo->prepare(
        'INSERT INTO admins (username, password_hash, full_name, is_active)
         VALUES (:username, :password_hash, :full_name, 1)
         ON DUPLICATE KEY UPDATE
            password_hash = VALUES(password_hash),
            full_name = VALUES(full_name),
            is_active = VALUES(is_active)'
    );
    $adminStmt->execute([
        'username' => $adminUsername,
        'password_hash' => $adminHash,
        'full_name' => $adminFullName,
    ]);

    if (env_bool('SEED_SAMPLE_REVIEWS', false)) {
        $sampleSql = file_get_contents(dirname(__DIR__) . '/database/seeds/001_sample_reviews.sql');
        if ($sampleSql !== false) {
            exec_sql_batch($pdo, $sampleSql);
        }
    }

    log_line('Seeds aplicados.');
}

$pdo = migration_db_with_retry();
apply_migrations($pdo);

if (env_bool('RUN_SEEDS', false)) {
    seed_defaults($pdo);
}

log_line('Banco pronto.');
