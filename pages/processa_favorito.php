<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\FavoriteService;
use App\Support\Http;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Http::redirect('livros.php');
}

// must be logged in
if (empty($_SESSION['user']['id'])) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'login_required']);
        exit;
    }
    Http::flashAndRedirect('flash_error', 'Faça login para favoritar livros.', 'index.php');
}

// accept JSON body for fetch as well
$bookId = 0;
if (isset($_POST['book_id'])) {
    $bookId = (int)($_POST['book_id'] ?? 0);
} else {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decodedRaw = json_decode($raw, true);
        if (is_array($decodedRaw) && isset($decodedRaw['book_id'])) {
            $bookId = (int)$decodedRaw['book_id'];
        }
    }
}

if ($bookId <= 0) {
    $_SESSION['flash_error'] = 'ID de livro inválido.';
    Http::redirect('livros.php');
}

$service = new FavoriteService();
$userId = (int)$_SESSION['user']['id'];
$result = $service->toggle($userId, $bookId);

$_SESSION['flash_success'] = $result['action'] === 'added' ? 'Livro adicionado aos favoritos.' : 'Livro removido dos favoritos.';

// If request expects JSON (AJAX), return JSON
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (str_contains($accept, 'application/json') || str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true] + $result);
    exit;
}

Http::redirect('livros.php');
exit;
