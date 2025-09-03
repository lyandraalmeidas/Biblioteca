<?php
// Insert a user and then run the same SELECT used by processa_login.php
$env = [];
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \"'");
    }
}
$dbConnection = getenv('DB_CONNECTION') ?: ($env['DB_CONNECTION'] ?? 'sqlite');

try {
    if (strtolower($dbConnection) === 'mysql') {
        $dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? '127.0.0.1');
        $dbPort = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
        $dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? '');
        $dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'root');
        $dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        $dbPath = __DIR__ . '/../database/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    echo "DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$email = 'login_test+' . time() . '@example.local';
$nome = 'Login Test';
$senha_raw = 'mysecret';
$senha = password_hash($senha_raw, PASSWORD_DEFAULT);
$now = (new DateTime())->format('Y-m-d H:i:s');

try {
    $insert = $pdo->prepare('INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, :created_at, :updated_at)');
    $insert->execute([
        ':name' => $nome,
        ':email' => $email,
        ':password' => $senha,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $id = $pdo->lastInsertId();
    echo "Inserted test user id=$id email=$email\n";

    // simulate processa_login.php SELECT
    $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "SELECT returned no user\n";
    } else {
        echo "SELECT found id={$user['id']} name={$user['name']}\n";
        if (password_verify($senha_raw, $user['password'])) {
            echo "Password verification OK\n";
        } else {
            echo "Password verification FAILED\n";
        }
    }

    // cleanup
    $del = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $del->execute([':id' => $id]);
    echo "Cleanup done, deleted id=$id\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
