INSERT INTO admins (username, password_hash, full_name, is_active)
VALUES
  ('nicolas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi', 'Nicolas', 1),
  ('gabriel', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi', 'Gabriel', 1),
  ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi', 'Administrador', 1)
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  full_name = VALUES(full_name),
  is_active = VALUES(is_active);
