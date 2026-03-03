<?php
require 'vendor/autoload.php';

$password = 'admin';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password hash for 'admin': $hash\n";
echo "\nInsert this SQL:\n";
echo "INSERT INTO \`user\` (email, roles, password) VALUES ('nolan.pichon87@gmail.com', '[\"ROLE_ADMIN\"]', '$hash');\n";
