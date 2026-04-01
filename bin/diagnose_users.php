<?php
require 'vendor/autoload.php';

// Load .env file
$envFile = dirname(__DIR__) . '/.env';
$databaseUrl = '';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, 'DATABASE_URL=') === 0 && strpos($line, '#') !== 0) {
            $databaseUrl = substr($line, 13);
            $databaseUrl = trim($databaseUrl, '"\'');
            break;
        }
    }
}

// Parse DATABASE_URL
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
    echo "❌ Could not find DATABASE_URL in .env\n";
    exit(1);
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "═════════════════════════════════════════════════════════════\n";
echo "           🔍 USER DATABASE DIAGNOSTIC\n";
echo "═════════════════════════════════════════════════════════════\n\n";

// Check if user table exists
$stmt = $pdo->query("SHOW TABLES LIKE 'user'");
if (!$stmt->fetch()) {
    echo "❌ Table 'user' does not exist!\n";
    exit(1);
}
echo "✅ Table 'user' exists\n\n";

// Get all users
$stmt = $pdo->query("SELECT id, email, nom, prenom, roles, password FROM `user` ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "❌ No users found in database!\n";
    exit(1);
}

echo "Found " . count($users) . " user(s):\n\n";

// Test login with the credentials
$testEmail = 'dr.dupont@hospital.fr';
$testPassword = 'medecin123';

$stmt = $pdo->prepare("SELECT id, email, password, roles FROM `user` WHERE email = ?");
$stmt->execute([$testEmail]);
$testUser = $stmt->fetch(PDO::FETCH_ASSOC);

echo "─────────────────────────────────────────────────────────────\n";
echo "USER DETAILS:\n";
echo "─────────────────────────────────────────────────────────────\n";

foreach ($users as $user) {
    $roles = json_decode($user['roles'], true);
    echo sprintf("[ID: %d] %s %s (%s)\n", 
        $user['id'], 
        $user['prenom'], 
        $user['nom'],
        implode(', ', $roles)
    );
    echo "         Email: " . $user['email'] . "\n";
    echo "         Hash: " . substr($user['password'], 0, 20) . "...\n";
    echo "\n";
}

echo "─────────────────────────────────────────────────────────────\n";
echo "TESTING LOGIN:\n";
echo "─────────────────────────────────────────────────────────────\n";

if (!$testUser) {
    echo "❌ User '$testEmail' not found!\n";
} else {
    echo "✅ User found!\n";
    echo "   Email: " . $testUser['email'] . "\n";
    
    // Test password verification with bcrypt
    if (password_verify($testPassword, $testUser['password'])) {
        echo "✅ Password verification: SUCCESS\n";
        echo "   Password '$testPassword' matches the hash!\n";
    } else {
        echo "❌ Password verification: FAILED\n";
        echo "   Tried password: '$testPassword'\n";
        echo "   Hash: " . $testUser['password'] . "\n";
        echo "\n   Testing other common passwords...\n";
        
        $commonPasswords = ['test123', 'admin123', 'chirurgien123', 'infirm123', 'password', '123456'];
        foreach ($commonPasswords as $pwd) {
            $result = password_verify($pwd, $testUser['password']) ? '✅' : '❌';
            echo "   $result '$pwd'\n";
        }
    }
}

echo "\n═════════════════════════════════════════════════════════════\n";
echo "POSSIBLE ISSUES:\n";
echo "═════════════════════════════════════════════════════════════\n";

if (!$testUser) {
    echo "1. User doesn't exist - run: php bin/create_test_users.php\n";
} else if (!password_verify($testPassword, $testUser['password'])) {
    echo "1. Password hash mismatch\n";
    echo "2. Make sure you're using the exact password from TEST_CREDENTIALS.txt\n";
    echo "3. Check if the API is using a different password hasher\n";
}

echo "\n";
