<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\BookRepository;
use App\Support\Http;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    Http::redirect('livros.php');
}

// must be logged in (reuse same policy as favorites)
if (empty($_SESSION['user']['id'])) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'login_required']);
        exit;
    }
    Http::flashAndRedirect('flash_error', 'Faça login para excluir itens.', 'index.php');
}

// accept application/json or form-encoded
$bookId = 0;
if (isset($_POST['book_id'])) {
    $bookId = (int)($_POST['book_id'] ?? 0);
} else {
    $raw = file_get_contents('php://input') ?: '';
    if ($raw !== '') {
        $data = json_decode($raw, true);
        if (is_array($data) && isset($data['book_id'])) {
            $bookId = (int)$data['book_id'];
        }
    }
}

if ($bookId <= 0) {
    $msg = 'ID inválido.';
    $_SESSION['flash_error'] = $msg;
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'invalid_id']);
        exit;
    }
    Http::redirect('livros.php');
}

$repo = new BookRepository();
$ok = $repo->deleteById($bookId);

if ($ok) {
    $_SESSION['flash_success'] = 'Item excluído com sucesso.';
} else {
    $_SESSION['flash_error'] = 'Não foi possível excluir o item.';
}

$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (str_contains($accept, 'application/json') || str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

Http::redirect('livros.php');
exit;
