<?php
// Reuse same .env parsing as processa_cadastro.php
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

if (strtolower($dbConnection) === 'mysql') {
    $dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? '127.0.0.1');
    $dbPort = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
    $dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? '');
    $dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'root');
    $dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? '');

    try {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "OK: connected to MySQL: $dbName@{$dbHost}:{$dbPort}\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables:\n";
        foreach ($tables as $t) echo " - $t\n";
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "Using sqlite\n";
}
