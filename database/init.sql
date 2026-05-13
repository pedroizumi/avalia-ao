CREATE DATABASE IF NOT EXISTS seller_reviews
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE seller_reviews;

CREATE TABLE IF NOT EXISTS vendors (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  photo_path VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(140) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  evaluation_uuid CHAR(36) NOT NULL UNIQUE,
  vendor_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  ip_hash CHAR(64) NOT NULL,
  user_agent_hash CHAR(64) NOT NULL,
  client_token_hash CHAR(64) NOT NULL,
  session_id_hash CHAR(64) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_vendor
    FOREIGN KEY (vendor_id) REFERENCES vendors(id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
  INDEX idx_reviews_vendor_created (vendor_id, created_at),
  INDEX idx_reviews_created (created_at),
  INDEX idx_reviews_spam_ip (ip_hash, created_at),
  INDEX idx_reviews_spam_client (client_token_hash, created_at),
  INDEX idx_reviews_spam_session (session_id_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schema_migrations (
  migration VARCHAR(190) PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_migrations (migration)
VALUES ('001_initial_schema.sql')
ON DUPLICATE KEY UPDATE applied_at = applied_at;

INSERT INTO vendors (name, slug, photo_path, is_active, display_order)
VALUES
  ('Nicolas', 'nicolas', 'images/nicolas-placeholder.svg', 1, 1),
  ('Gabriel', 'gabriel', 'images/gabriel-placeholder.svg', 1, 2)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  photo_path = VALUES(photo_path),
  is_active = VALUES(is_active),
  display_order = VALUES(display_order);

INSERT INTO admins (username, password_hash, full_name, is_active)
VALUES
  ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi', 'Administrador', 1)
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  full_name = VALUES(full_name),
  is_active = VALUES(is_active);

INSERT INTO reviews
  (evaluation_uuid, vendor_id, rating, comment, ip_hash, user_agent_hash, client_token_hash, session_id_hash, created_at)
VALUES
  ('00000000-0000-4000-8000-000000000001', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, 'Atendimento excelente, muito atencioso.', REPEAT('a', 64), REPEAT('b', 64), REPEAT('c', 64), REPEAT('d', 64), NOW() - INTERVAL 6 DAY),
  ('00000000-0000-4000-8000-000000000002', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, 'Explicou tudo com clareza.', REPEAT('e', 64), REPEAT('f', 64), REPEAT('1', 64), REPEAT('2', 64), NOW() - INTERVAL 5 DAY),
  ('00000000-0000-4000-8000-000000000003', (SELECT id FROM vendors WHERE slug = 'nicolas'), 4, 'Foi rápido e resolveu minhas dúvidas.', REPEAT('3', 64), REPEAT('4', 64), REPEAT('5', 64), REPEAT('6', 64), NOW() - INTERVAL 4 DAY),
  ('00000000-0000-4000-8000-000000000004', (SELECT id FROM vendors WHERE slug = 'nicolas'), 5, NULL, REPEAT('7', 64), REPEAT('8', 64), REPEAT('9', 64), REPEAT('0', 64), NOW() - INTERVAL 3 DAY),
  ('00000000-0000-4000-8000-000000000005', (SELECT id FROM vendors WHERE slug = 'gabriel'), 5, 'Muito educado e objetivo.', REPEAT('a1', 32), REPEAT('b1', 32), REPEAT('c1', 32), REPEAT('d1', 32), NOW() - INTERVAL 6 DAY),
  ('00000000-0000-4000-8000-000000000006', (SELECT id FROM vendors WHERE slug = 'gabriel'), 4, 'Bom atendimento.', REPEAT('e1', 32), REPEAT('f1', 32), REPEAT('a2', 32), REPEAT('b2', 32), NOW() - INTERVAL 4 DAY),
  ('00000000-0000-4000-8000-000000000007', (SELECT id FROM vendors WHERE slug = 'gabriel'), 4, 'Gostei da experiência.', REPEAT('c2', 32), REPEAT('d2', 32), REPEAT('e2', 32), REPEAT('f2', 32), NOW() - INTERVAL 2 DAY),
  ('00000000-0000-4000-8000-000000000008', (SELECT id FROM vendors WHERE slug = 'gabriel'), 5, NULL, REPEAT('a3', 32), REPEAT('b3', 32), REPEAT('c3', 32), REPEAT('d3', 32), NOW() - INTERVAL 1 DAY)
ON DUPLICATE KEY UPDATE
  rating = VALUES(rating),
  comment = VALUES(comment);

DROP VIEW IF EXISTS vendor_sales_summary;

CREATE VIEW vendor_sales_summary AS
SELECT
  v.id AS vendor_id,
  v.name,
  COUNT(r.id) AS total_sales,
  COUNT(r.id) AS total_reviews,
  COALESCE(ROUND(AVG(r.rating), 2), 0) AS average_rating
FROM vendors v
LEFT JOIN reviews r ON r.vendor_id = v.id
GROUP BY v.id, v.name;

