<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\ReadingStatRepository;

header('Content-Type: application/json; charset=utf-8');

// Require login
if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'not_authenticated']);
    exit;
}

$input = file_get_contents('php://input');
$payload = json_decode($input, true);
if (!is_array($payload)) { $payload = $_POST; }

$bookId = isset($payload['book_id']) ? (int)$payload['book_id'] : 0;
$seconds = isset($payload['seconds']) ? (int)$payload['seconds'] : 0;
$date = isset($payload['date']) ? preg_replace('/[^0-9\-]/', '', (string)$payload['date']) : null; // YYYY-MM-DD

if ($bookId <= 0 || $seconds <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'invalid_params']);
    exit;
}

try {
    (new ReadingStatRepository())->addSeconds((int)$_SESSION['user']['id'], $bookId, $seconds, $date);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
