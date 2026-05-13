UPDATE admins
SET password_hash = '$2y$10$Qd0z7Y6xU7Wf6KcqJh2L9e6tF2EZkOPo7pPz4bDH7xHYjMd3Bj6Yq',
    is_active = 1
WHERE username IN ('nicolas', 'gabriel', 'admin');
