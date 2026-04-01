<?php
// Quick database diagnostic

require 'vendor/autoload.php';

$kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', $_ENV['APP_DEBUG'] ?? false);
$kernel->boot();

$em = $kernel->getContainer()->get('doctrine')->getManager();

echo "═══════════════════════════════════════════════════════════\n";
echo "           📊 DATABASE DIAGNOSTIC\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Count users
$userCount = (int) $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User u')->getSingleScalarResult();
echo "👤 Users in database: $userCount\n";

// List all users
$users = $em->createQuery('SELECT u.id, u.email, u.roles FROM App\Entity\User u ORDER BY u.id')->getArrayResult();
if (!empty($users)) {
    echo "\n   Users found:\n";
    foreach ($users as $user) {
        $roles = json_decode($user['roles'], true);
        echo sprintf("   [ID: %d] %s - %s\n", $user['id'], $user['email'], implode(', ', $roles));
    }
}

// Count patients
$patientCount = (int) $em->createQuery('SELECT COUNT(p.id) FROM App\Entity\Patient p')->getSingleScalarResult();
echo "\n👥 Patients in database: $patientCount\n";

// List patient-doctor relationships
if ($patientCount > 0) {
    $patients = $em->createQuery('SELECT p.id, p.Name, p.FirstName FROM App\Entity\Patient p ORDER BY p.id LIMIT 5')->getArrayResult();
    echo "\n   Sample patients:\n";
    foreach ($patients as $patient) {
        echo sprintf("   [ID: %d] %s %s\n", $patient['id'], $patient['FirstName'], $patient['Name']);
    }
}

echo "\n═══════════════════════════════════════════════════════════\n";
echo "SUMMARY: " . ($userCount > 0 ? "✅ Users found" : "❌ No users") . " | " . ($patientCount > 0 ? "✅ Patients found" : "❌ No patients") . "\n";
echo "═══════════════════════════════════════════════════════════\n";
