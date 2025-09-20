<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\UserRepository;
use App\Support\Http;

// Only accept POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Http::redirect('cadastro.php');
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$senha = $_POST['senha'] ?? '';
$senha_conf = $_POST['senha_conf'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $senha_conf === '') {
    Http::flashAndRedirect('flash', 'Preencha todos os campos obrigatórios.', 'cadastro.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    Http::flashAndRedirect('flash', 'E-mail inválido.', 'cadastro.php');
}

if ($senha !== $senha_conf) {
    Http::flashAndRedirect('flash', 'As senhas não coincidem.', 'cadastro.php');
}

$users = new UserRepository();

try {
    if ($users->existsByEmail($email)) {
        Http::flashAndRedirect('flash', 'E-mail já cadastrado. Utilize outro e-mail.', 'cadastro.php');
    }

    $passwordHash = password_hash($senha, PASSWORD_DEFAULT);
    $userId = $users->create($nome, $email, $telefone ?: null, $passwordHash);

    $_SESSION['user'] = [
        'id' => $userId,
        'name' => $nome,
        'email' => $email,
        'telefone' => $telefone,
    ];

    Http::redirect('home.php');
} catch (\Throwable $e) {
    Http::flashAndRedirect('flash', 'Erro ao cadastrar: ' . $e->getMessage(), 'cadastro.php');
}
