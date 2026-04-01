<?php

declare(strict_types=1);

$dsn = 'mysql:host=database;port=3306;dbname=app;charset=utf8mb4';
$user = 'app';
$pass = '!ChangeMe!';

$credentials = [
    'admin@hospital.fr' => 'admin123',
    'dr.dupont@hospital.fr' => 'medecin123',
    'dr.martin@hospital.fr' => 'medecin123',
    'dr.bernard@hospital.fr' => 'medecin123',
    'dr.rousseau@hospital.fr' => 'chirurgien123',
    'dr.leclerc@hospital.fr' => 'chirurgien123',
    'dr.moreau@hospital.fr' => 'chirurgien123',
    'infirmiere.durand@hospital.fr' => 'infirm123',
    'infirmiere.petit@hospital.fr' => 'infirm123',
    'infirmiere.richard@hospital.fr' => 'infirm123',
    'infirmiere.thomas@hospital.fr' => 'infirm123',
];

$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$stmt = $pdo->prepare('UPDATE `user` SET password = :password WHERE email = :email');

foreach ($credentials as $email => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt->execute([
        ':password' => $hash,
        ':email' => $email,
    ]);
    echo "Updated password for {$email}\n";
}

echo "Done.\n";
