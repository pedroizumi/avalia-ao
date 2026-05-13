<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function active_vendors(): array
{
    $stmt = db()->query(
        'SELECT id, name, slug, photo_path
         FROM vendors
         WHERE is_active = 1
         ORDER BY display_order ASC, id ASC'
    );

    return $stmt->fetchAll();
}

function vendor_by_slug(string $slug): ?array
{
    $stmt = db()->prepare(
        'SELECT id, name, slug, photo_path
         FROM vendors
         WHERE slug = :slug AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['slug' => $slug]);
    $vendor = $stmt->fetch();

    return $vendor ?: null;
}

function vendor_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT id, name, slug, photo_path
         FROM vendors
         WHERE id = :id AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $vendor = $stmt->fetch();

    return $vendor ?: null;
}

