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

function vendor_rating_summary(int $vendorId): array
{
    $stmt = db()->prepare(
        'SELECT
            COUNT(*) AS total_reviews,
            COALESCE(ROUND(AVG(rating), 2), 0) AS average_rating
         FROM reviews
         WHERE vendor_id = :vendor_id'
    );
    $stmt->execute(['vendor_id' => $vendorId]);
    $summary = $stmt->fetch() ?: ['total_reviews' => 0, 'average_rating' => 0];

    return [
        'total_reviews' => (int) $summary['total_reviews'],
        'average_rating' => (float) $summary['average_rating'],
    ];
}

function vendor_recent_comments(int $vendorId, int $limit = 5): array
{
    $limit = max(1, min(10, $limit));
    $stmt = db()->prepare(
        "SELECT rating, comment, created_at
         FROM reviews
         WHERE vendor_id = :vendor_id
           AND comment IS NOT NULL
           AND TRIM(comment) <> ''
         ORDER BY created_at DESC
         LIMIT {$limit}"
    );
    $stmt->execute(['vendor_id' => $vendorId]);

    return $stmt->fetchAll();
}
