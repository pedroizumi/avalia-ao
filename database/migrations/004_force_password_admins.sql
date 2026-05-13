UPDATE admins
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC2RmY5n5i5bSQWE2Hzi',
    is_active = 1
WHERE username IN ('nicolas', 'gabriel', 'admin');
