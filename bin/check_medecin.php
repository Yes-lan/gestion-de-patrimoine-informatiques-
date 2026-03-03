<?php
require 'vendor/autoload.php';

$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? false);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();
$user = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'medecin@test.fr']);

if (!$user) {
    echo "❌ Utilisateur 'medecin@test.fr' NOT FOUND\n";
    exit(1);
}

echo "✅ Utilisateur trouvé!\n";
echo "Email: " . $user->getEmail() . "\n";
echo "Roles: " . json_encode($user->getRoles()) . "\n";

if (password_verify('test1234', $user->getPassword())) {
    echo "✓ Password 'test1234' MATCHES!\n";
} else {
    echo "✗ Password DOES NOT MATCH!\n";
}
