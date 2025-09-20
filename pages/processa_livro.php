<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\BookRepository;
use App\Support\Http;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    Http::redirect('home.php');
}

$title = trim($_POST['title'] ?? '');
$year = trim($_POST['year'] ?? '');
$author_name = trim($_POST['author'] ?? '');
$category_name = trim($_POST['category'] ?? '');
$publisher_name = trim($_POST['publisher'] ?? '');

if ($title === '' || $author_name === '') {
    Http::flashAndRedirect('flash', 'Título e autor são obrigatórios.', 'home.php');
}

// Normalize nullable values
$year = $year === '' ? null : (int)$year;
$author_name = $author_name === '' ? null : $author_name;
$category_name = $category_name === '' ? null : $category_name;
$publisher_name = $publisher_name === '' ? null : $publisher_name;

try {
    $repo = new BookRepository();
    $author_id = $repo->findOrCreateAuthor($author_name);
    $category_id = $repo->findOrCreateCategory($category_name);
    $publisher_id = $repo->findOrCreatePublisher($publisher_name);
    $repo->create($title, $year, $author_id, $publisher_id, $category_id);

    $_SESSION['flash'] = 'Livro adicionado com sucesso.';
    Http::redirect('home.php');
} catch (\Throwable $e) {
    $_SESSION['flash'] = 'Erro ao salvar livro: ' . $e->getMessage();
    Http::redirect('home.php');
}
