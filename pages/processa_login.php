<?php
// Start session and setup autoload
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\AuthService;
use App\Support\Http;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Http::redirect('index.php');
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    Http::flashAndRedirect('flash', 'Preencha e-mail e senha.', 'index.php');
}

$auth = new AuthService();
$logged = $auth->attempt($email, $senha);
if (!$logged) {
    Http::flashAndRedirect('flash', 'Credenciais inválidas.', 'index.php');
}

Http::redirect('home.php');
exit;
