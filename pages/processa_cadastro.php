<?php
session_start();

// Load simple .env fallback (when running the plain PHP pages outside of Laravel boot)
$env = [];
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \"'");
    }
}

// Determine DB connection (env var -> .env -> default sqlite)
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
        // fallback to sqlite file
        $dbPath = __DIR__ . '/../database/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    // fatal
    http_response_code(500);
    echo 'Erro de conexão com o banco de dados.';
    exit;
}

// Helper to flash message and redirect
function resolve_redirect(string $to): string {
    // If $to is an absolute URL or starts with a slash, return as-is
    if (preg_match('#^https?://#i', $to) || strpos($to, '/') === 0) {
        return $to;
    }
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
    return $base . '/' . ltrim($to, '\\/');
}

function flash_and_redirect($msg, $to = 'cadastro.php') {
    $_SESSION['flash'] = $msg;
    header('Location: ' . resolve_redirect($to));
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit;
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha = $_POST['senha'] ?? '';
$senha_conf = $_POST['senha_conf'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $senha_conf === '') {
    flash_and_redirect('Preencha todos os campos obrigatórios.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_and_redirect('E-mail inválido.');
}

if ($senha !== $senha_conf) {
    flash_and_redirect('As senhas não coincidem.');
}

// Check if email exists
$passwordHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        flash_and_redirect('E-mail j\u00e1 cadastrado. Utilize outro e-mail.');
    }

    $now = (new DateTime())->format('Y-m-d H:i:s');
        $insert = $pdo->prepare('INSERT INTO users (name, email, telefone, password, created_at, updated_at) VALUES (:name, :email, :telefone, :password, :created_at, :updated_at)');
        $insert->execute([
            ':name' => $nome,
            ':email' => $email,
            ':telefone' => $telefone,
            ':password' => $passwordHash,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

    $userId = $pdo->lastInsertId();

    // Set session
    $_SESSION['user'] = [
        'id' => $userId,
        'name' => $nome,
        'email' => $email,
        'telefone' => $telefone,
    ];

    header('Location: ' . resolve_redirect('home.php'));
    exit;
} catch (PDOException $e) {
    // Log minimal error to session flash (avoid exposing sensitive info in production)
    $_SESSION['flash'] = 'Erro ao cadastrar: ' . $e->getMessage();
    // Redirect back to the form so the message is shown
    header('Location: ' . resolve_redirect('cadastro.php'));
    exit;
}
