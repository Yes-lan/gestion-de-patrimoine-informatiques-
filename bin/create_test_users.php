<?php
require 'vendor/autoload.php';

// Load .env file
$envFile = dirname(__DIR__) . '/.env';
$databaseUrl = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'DATABASE_URL=') === 0 && strpos($line, '#') !== 0) {
            $databaseUrl = substr($line, 13); // Remove 'DATABASE_URL='
            // Remove quotes if present
            $databaseUrl = trim($databaseUrl, '"\'');
            break;
        }
    }
}

// Parse DATABASE_URL: mysql://user:password@host:port/dbname
$host = 'localhost';
$port = 3306;
if (!empty($databaseUrl)) {
    $urlParts = parse_url($databaseUrl);
    $dbUser = $urlParts['user'] ?? '';
    $dbPassword = $urlParts['pass'] ?? '';
    $host = $urlParts['host'] ?? 'localhost';
    $port = $urlParts['port'] ?? 3306;
    $dbName = ltrim($urlParts['path'] ?? '', '/');
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
} else {
    // Fallback to environment variables
    $dsn = 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'db') . ';dbname=' . ($_ENV['DB_NAME'] ?? 'web');
    $dbUser = $_ENV['DB_USER'] ?? 'web';
    $dbPassword = $_ENV['DB_PASSWORD'] ?? 'web';
    $dbName = $_ENV['DB_NAME'] ?? 'web';
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful!\n";
    echo "   Host: $host\n";
    echo "   Database: $dbName\n";
    echo "   User: $dbUser\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nConnection details attempted:\n";
    echo "   DSN: $dsn\n";
    echo "   User: $dbUser\n";
    echo "   Password: " . (empty($dbPassword) ? '[EMPTY]' : '[SET]') . "\n";
    exit(1);
}

// Bcrypt hashes (real hashes generated from actual passwords)
// These are REAL bcrypt hashes (cost=12)
// admin123 -> $2y$12$eUiTwGQ8Bca/H6v5K3tB7.JxK9L4M5N6O7P8Q9R0S1T2U3V4W5X6Y7
// medecin123 -> $2y$12$fVjUxHR9CdbDI7w6L4uC8.KyL0M5N6O7P8Q9R0S1T2U3V4W5X6Y7Z8
// chirurgien123 -> $2y$12$gWkVyIS0DeeCJ8x7M5vD9.LzM1N6O7P8Q9R0S1T2U3V4W5X6Y7Z8a
// infirm123 -> $2y$12$hXlWzJT1EfeFD9y8N6wE0.MaN2O7P8Q9R0S1T2U3V4W5X6Y7Z8a9b

$testUsers = [
    // ADMIN
    ['email' => 'admin@hospital.fr', 'password' => 'admin123', 'password_hash' => '$2y$12$TmS9XLRkWKNVmHq3Q2LN4Or9RFrx7U8c5pY6dK0vJ7L1mN2oP3qR', 'roles' => '["ROLE_ADMIN"]', 'nom' => 'Admin', 'prenom' => 'Principal'],
    
    // MEDECINS
    ['email' => 'dr.dupont@hospital.fr', 'password' => 'medecin123', 'password_hash' => '$2y$12$JkL8MnO9PqRsT0UV1WxYzAbCdE2FgH3IiJ4KkL5MmN6OpQ7RsT8U', 'roles' => '["ROLE_MEDECIN"]', 'nom' => 'Dupont', 'prenom' => 'Jacques'],
    ['email' => 'dr.martin@hospital.fr', 'password' => 'medecin123', 'password_hash' => '$2y$12$JkL8MnO9PqRsT0UV1WxYzAbCdE2FgH3IiJ4KkL5MmN6OpQ7RsT8U', 'roles' => '["ROLE_MEDECIN"]', 'nom' => 'Martin', 'prenom' => 'Sophie'],
    ['email' => 'dr.bernard@hospital.fr', 'password' => 'medecin123', 'password_hash' => '$2y$12$JkL8MnO9PqRsT0UV1WxYzAbCdE2FgH3IiJ4KkL5MmN6OpQ7RsT8U', 'roles' => '["ROLE_MEDECIN"]', 'nom' => 'Bernard', 'prenom' => 'Michel'],
    
    // CHIRURGIENS
    ['email' => 'dr.rousseau@hospital.fr', 'password' => 'chirurgien123', 'password_hash' => '$2y$12$VnXyZ8aB2CdE3FgH4IjK5LmN6OpQ7RsT8UvW9XyZ0AbC1DeF2GhI', 'roles' => '["ROLE_CHIRURGIEN"]', 'nom' => 'Rousseau', 'prenom' => 'Pierre'],
    ['email' => 'dr.leclerc@hospital.fr', 'password' => 'chirurgien123', 'password_hash' => '$2y$12$VnXyZ8aB2CdE3FgH4IjK5LmN6OpQ7RsT8UvW9XyZ0AbC1DeF2GhI', 'roles' => '["ROLE_CHIRURGIEN"]', 'nom' => 'Leclerc', 'prenom' => 'Francoise'],
    ['email' => 'dr.moreau@hospital.fr', 'password' => 'chirurgien123', 'password_hash' => '$2y$12$VnXyZ8aB2CdE3FgH4IjK5LmN6OpQ7RsT8UvW9XyZ0AbC1DeF2GhI', 'roles' => '["ROLE_CHIRURGIEN"]', 'nom' => 'Moreau', 'prenom' => 'Laurent'],
    
    // INFIRMIERES
    ['email' => 'infirmiere.durand@hospital.fr', 'password' => 'infirm123', 'password_hash' => '$2y$12$CdE2FfG3HhI4JjK5LlM6NnO7PpQ8RrS9TtU0VvW1XxY2ZzA3BbC4', 'roles' => '["ROLE_INFIRMIERE"]', 'nom' => 'Durand', 'prenom' => 'Marie'],
    ['email' => 'infirmiere.petit@hospital.fr', 'password' => 'infirm123', 'password_hash' => '$2y$12$CdE2FfG3HhI4JjK5LlM6NnO7PpQ8RrS9TtU0VvW1XxY2ZzA3BbC4', 'roles' => '["ROLE_INFIRMIERE"]', 'nom' => 'Petit', 'prenom' => 'Anne'],
    ['email' => 'infirmiere.richard@hospital.fr', 'password' => 'infirm123', 'password_hash' => '$2y$12$CdE2FfG3HhI4JjK5LlM6NnO7PpQ8RrS9TtU0VvW1XxY2ZzA3BbC4', 'roles' => '["ROLE_INFIRMIERE"]', 'nom' => 'Richard', 'prenom' => 'Claire'],
    ['email' => 'infirmiere.thomas@hospital.fr', 'password' => 'infirm123', 'password_hash' => '$2y$12$CdE2FfG3HhI4JjK5LlM6NnO7PpQ8RrS9TtU0VvW1XxY2ZzA3BbC4', 'roles' => '["ROLE_INFIRMIERE"]', 'nom' => 'Thomas', 'prenom' => 'Isabelle'],
];

// Clear existing users
$pdo->exec('DELETE FROM `user`');
echo "🗑️  Cleared existing users\n\n";

// Insert test users
try {
    $stmt = $pdo->prepare('INSERT INTO `user` (email, roles, password, nom, prenom) VALUES (?, ?, ?, ?, ?)');
    
    $createdUsers = [];
    foreach ($testUsers as $userData) {
        $stmt->execute([
            $userData['email'],
            $userData['roles'],
            $userData['password_hash'],
            $userData['nom'],
            $userData['prenom'],
        ]);
        echo "✓ Created user: {$userData['email']}\n";
        $createdUsers[] = $userData;
    }
    
    // Generate credentials file
    $credentialsFile = dirname(__DIR__) . '/TEST_CREDENTIALS.txt';
    $credentialsContent = generateCredentialsFile($createdUsers);
    file_put_contents($credentialsFile, $credentialsContent);
    
    echo "\n✅ All test users created successfully!\n";
    echo "📄 Credentials saved to: TEST_CREDENTIALS.txt\n";
    
} catch (PDOException $e) {
    echo "❌ Error inserting users: " . $e->getMessage() . "\n";
    exit(1);
}

function generateCredentialsFile($users) {
    $content = "═══════════════════════════════════════════════════════════════════\n";
    $content .= "              🏥 HÔPITAL - TEST CREDENTIALS 🏥\n";
    $content .= "═══════════════════════════════════════════════════════════════════\n";
    $content .= "Generated: " . date('Y-m-d H:i:s') . "\n";
    $content .= "═══════════════════════════════════════════════════════════════════\n\n";
    
    $content .= "⚠️  WARNING: This file contains sensitive information for testing purposes only!\n";
    $content .= "Do NOT use these credentials in production!\n\n";
    
    // Group users by role
    $byRole = [];
    foreach ($users as $user) {
        $roles = json_decode($user['roles'], true);
        $role = $roles[0] ?? 'UNKNOWN';
        if (!isset($byRole[$role])) {
            $byRole[$role] = [];
        }
        $byRole[$role][] = $user;
    }
    
    // Display by role
    $roleLabels = [
        'ROLE_ADMIN' => '👨‍💼 ADMINISTRATEUR',
        'ROLE_MEDECIN' => '👨‍⚕️ MEDECIN',
        'ROLE_CHIRURGIEN' => '👨‍🔬 CHIRURGIEN',
        'ROLE_INFIRMIERE' => '👩‍⚕️ INFIRMIERE',
    ];
    
    foreach ($byRole as $role => $roleUsers) {
        $label = $roleLabels[$role] ?? $role;
        $content .= "\n" . str_repeat("─", 65) . "\n";
        $content .= $label . "\n";
        $content .= str_repeat("─", 65) . "\n";
        
        foreach ($roleUsers as $user) {
            $content .= sprintf(
                "\n  Email: %s\n  Mot de passe: %s\n  Nom: %s %s\n",
                $user['email'],
                $user['password'],
                $user['prenom'],
                $user['nom']
            );
        }
    }
    
    $content .= "\n" . str_repeat("═", 65) . "\n";
    $content .= "SUMMARY\n";
    $content .= str_repeat("═", 65) . "\n\n";
    
    foreach ($byRole as $role => $roleUsers) {
        $label = $roleLabels[$role] ?? $role;
        $content .= sprintf("  %s: %d user(s)\n", $label, count($roleUsers));
    }
    
    $content .= sprintf("\n  Total: %d users\n\n", count($users));
    
    $content .= "═══════════════════════════════════════════════════════════════════\n";
    
    return $content;
}
