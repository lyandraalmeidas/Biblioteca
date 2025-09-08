<?php
session_start();

// Basic POST handler to save favorite book IDs into storage/favorites.json
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: livros.php');
    exit;
}

// accept JSON body for fetch as well
$bookId = 0;
if (isset($_POST['book_id'])) {
    $bookId = (int)$_POST['book_id'];
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
    header('Location: livros.php');
    exit;
}

$storageDir = __DIR__ . '/../storage';
if (!is_dir($storageDir)) @mkdir($storageDir, 0755, true);
$favoritesFile = $storageDir . '/favorites.json';

$favorites = [];
if (file_exists($favoritesFile)) {
    $content = file_get_contents($favoritesFile);
    $decoded = json_decode($content, true);
    if (is_array($decoded)) $favorites = $decoded;
}

// Toggle: remove if exists, otherwise add
$action = '';
if (in_array($bookId, $favorites, true)) {
    // remove
    $favorites = array_values(array_filter($favorites, function($v) use ($bookId) { return $v !== $bookId; }));
    file_put_contents($favoritesFile, json_encode($favorites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['flash_success'] = 'Livro removido dos favoritos.';
    $action = 'removed';
} else {
    $favorites[] = $bookId;
    file_put_contents($favoritesFile, json_encode(array_values($favorites), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $_SESSION['flash_success'] = 'Livro adicionado aos favoritos.';
    $action = 'added';
}

// If request expects JSON (AJAX), return JSON
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (strpos($accept, 'application/json') !== false || strpos($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') !== false) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'action' => $action, 'favorites' => array_values($favorites)]);
    exit;
}

header('Location: livros.php');
exit;
