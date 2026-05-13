<?php

require_once dirname(__DIR__) . '/app/db.php';

if (($_GET['token'] ?? '') !== 'trocar123') {
    http_response_code(403);
    exit('Bloqueado');
}

$hash = password_hash('password', PASSWORD_DEFAULT);

$stmt = db()->prepare("
    INSERT INTO admins (username, password_hash, full_name, is_active)
    VALUES
      ('nicolas', :hash1, 'Nicolas', 1),
      ('gabriel', :hash2, 'Gabriel', 1),
      ('admin', :hash3, 'Administrador', 1)
    ON DUPLICATE KEY UPDATE
      password_hash = VALUES(password_hash),
      full_name = VALUES(full_name),
      is_active = 1
");

$stmt->execute([
    'hash1' => $hash,
    'hash2' => $hash,
    'hash3' => $hash,
]);

echo 'Senhas corrigidas. Agora todos usam a senha: password';
