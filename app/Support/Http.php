<?php

namespace App\Support;

class Http
{
    public static function resolveRedirect(string $to): string
    {
        if (preg_match('#^https?://#i', $to) || str_starts_with($to, '/')) {
            return $to;
        }
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '\\/');
        return $base . '/' . ltrim($to, '\\/');
    }

    public static function redirect(string $to): void
    {
        header('Location: ' . self::resolveRedirect($to));
        exit;
    }

    public static function flash(string $key, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION[$key] = $message;
    }

    public static function flashAndRedirect(string $key, string $message, string $to): void
    {
        self::flash($key, $message);
        self::redirect($to);
    }
}
