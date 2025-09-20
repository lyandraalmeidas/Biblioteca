<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Http;

// Clear session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'] ?? '/', $params['domain'] ?? '',
        (bool)($params['secure'] ?? false), (bool)($params['httponly'] ?? false)
    );
}
session_destroy();

Http::redirect('index.php');
