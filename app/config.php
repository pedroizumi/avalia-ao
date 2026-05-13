<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

load_env_file(dirname(__DIR__) . '/.env');

const APP_NAME = 'Avaliação de Vendedores';
const MAX_COMMENT_LENGTH = 800;

function app_env(): string
{
    return env_value('APP_ENV', 'local') ?? 'local';
}

function is_production(): bool
{
    return app_env() === 'production';
}

function app_url(): string
{
    return rtrim(env_value('APP_URL', 'http://localhost:8080') ?? 'http://localhost:8080', '/');
}

function app_key(): string
{
    return env_value('APP_KEY', 'local-development-key-change-me') ?? 'local-development-key-change-me';
}

function spam_cooldown_seconds(): int
{
    return max(30, (int) (env_value('SPAM_COOLDOWN_SECONDS', '180') ?? '180'));
}

function trusted_proxy_enabled(): bool
{
    return env_bool('APP_TRUST_PROXY', is_production());
}

function database_config(): array
{
    $url = env_value('DATABASE_URL', env_value('MYSQL_URL', env_value('CLEARDB_DATABASE_URL')));

    if ($url) {
        $parsed = parse_url($url);

        if ($parsed !== false) {
            $path = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';
            parse_str($parsed['query'] ?? '', $query);

            return [
                'host' => $parsed['host'] ?? 'db',
                'port' => (string) ($parsed['port'] ?? 3306),
                'database' => $path,
                'username' => urldecode($parsed['user'] ?? ''),
                'password' => urldecode($parsed['pass'] ?? ''),
                'ssl_mode' => $query['sslmode'] ?? env_value('DB_SSL_MODE', ''),
                'ssl_ca' => $query['ssl_ca'] ?? env_value('DB_SSL_CA', ''),
            ];
        }
    }

    return [
        'host' => env_value('DB_HOST', env_value('MYSQLHOST', 'db')),
        'port' => env_value('DB_PORT', env_value('MYSQLPORT', '3306')),
        'database' => env_value('DB_NAME', env_value('MYSQLDATABASE', 'seller_reviews')),
        'username' => env_value('DB_USER', env_value('MYSQLUSER', 'seller_user')),
        'password' => env_value('DB_PASS', env_value('MYSQLPASSWORD', 'seller_pass')),
        'ssl_mode' => env_value('DB_SSL_MODE', ''),
        'ssl_ca' => env_value('DB_SSL_CA', ''),
    ];
}
