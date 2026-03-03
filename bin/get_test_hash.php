<?php
require 'vendor/autoload.php';

$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? false);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();

// Récupérer le hash du user test@test.com qui fonctionne
$testUser = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'test@test.com']);
if ($testUser) {
    echo "Hash from test@test.com: " . $testUser->getPassword() . "\n";
    
    // Vérifier que test1234 fonctionne avec ce hash
    if (password_verify('test1234', $testUser->getPassword())) {
        echo "✓ Password 'test1234' matches this hash!\n";
    }
}
