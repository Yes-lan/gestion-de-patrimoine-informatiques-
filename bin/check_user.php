<?php
require 'vendor/autoload.php';

$conn = new \PDO('mysql:host=db;dbname=web', 'web', 'web');
$stmt = $conn->prepare('SELECT id, email, roles, password FROM user WHERE email = ?');
$stmt->execute(['nolan.pichon87@gmail.com']);
$row = $stmt->fetch(\PDO::FETCH_ASSOC);

if ($row) {
    echo "ID: " . $row['id'] . "\n";
    echo "Email: " . $row['email'] . "\n";
    echo "Roles: " . $row['roles'] . "\n";
    echo "Password Hash: " . $row['password'] . "\n";
    
    // Test if password 'admin' matches
    $testPassword = 'admin';
    if (password_verify($testPassword, $row['password'])) {
        echo "\n✅ Password 'admin' MATCHES the hash!\n";
    } else {
        echo "\n❌ Password 'admin' DOES NOT MATCH the hash!\n";
    }
} else {
    echo "Utilisateur non trouvé\n";
}
