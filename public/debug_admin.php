<?php

require_once dirname(__DIR__) . '/app/db.php';

if (($_GET['token'] ?? '') !== 'debug123') {
    http_response_code(403);
    exit('Bloqueado');
}

$admins = db()->query("SELECT username, full_name, is_active, password_hash FROM admins ORDER BY username")->fetchAll();

echo "<pre>";
echo "Admins encontrados:\n\n";

foreach ($admins as $admin) {
    echo "Usuario: " . $admin['username'] . "\n";
    echo "Nome: " . $admin['full_name'] . "\n";
    echo "Ativo: " . $admin['is_active'] . "\n";
    echo "Tamanho do hash: " . strlen($admin['password_hash']) . "\n";
    echo "Senha password funciona? " . (password_verify('password', $admin['password_hash']) ? 'SIM' : 'NAO') . "\n";
    echo "----------------------\n";
}
echo "</pre>";
