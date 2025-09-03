<?php
session_start();

// Load simple .env fallback
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
    http_response_code(500);
    echo 'Erro de conexão com o banco de dados.';
    exit;
}

function resolve_redirect(string $to): string {
    // If $to is an absolute URL or starts with a slash, return as-is
    if (preg_match('#^https?://#i', $to) || strpos($to, '/') === 0) {
        return $to;
    }
    // Prepend the directory of the current script so relative paths resolve correctly
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
    return $base . '/' . ltrim($to, '\\/');
}

function flash_and_redirect($msg, $to = 'index.php') {
    $_SESSION['flash'] = $msg;
    header('Location: ' . resolve_redirect($to));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    flash_and_redirect('Preencha e-mail e senha.');
}

$stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_and_redirect('Credenciais inválidas.');
}

if (!password_verify($senha, $user['password'])) {
    flash_and_redirect('Credenciais inválidas.');
}

// Successful login
$_SESSION['user'] = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
];

header('Location: ' . resolve_redirect('home.php'));
exit;
