<?php

namespace App\Support;

use PDO;
use PDOException;

class Database
{
    /**
     * Create a PDO connection using env configuration.
     * Supports: mysql or sqlite (default).
     */
    public static function pdo(): PDO
    {
        // Ensure a consistent timezone across the app before any date() usage
        static $tzSet = false;
        if (!$tzSet) {
            $tz = Env::get('APP_TIMEZONE', 'America/Sao_Paulo') ?? 'America/Sao_Paulo';
            @date_default_timezone_set($tz);
            $tzSet = true;
        }
        $conn = strtolower(Env::get('DB_CONNECTION', 'sqlite') ?? 'sqlite');
        if ($conn === 'mysql') {
            $host = Env::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1';
            $port = Env::get('DB_PORT', '3306') ?? '3306';
            $db   = Env::get('DB_DATABASE', '') ?? '';
            $user = Env::get('DB_USERNAME', 'root') ?? 'root';
            $pass = Env::get('DB_PASSWORD', '') ?? '';

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $db);
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Align MySQL session time_zone with PHP if TIMESTAMP columns are used
            try {
                $tz = date_default_timezone_get();
                // MySQL accepts named timezones if timezone tables are loaded; otherwise, use offset
                // Attempt named timezone first, fallback to offset.
                $pdo->exec("SET time_zone = '" . str_replace("'", "''", $tz) . "'");
            } catch (\Throwable $e) {
                // Fallback: compute current offset like +03:00
                $offset = (new \DateTime('now'))->format('P');
                try { $pdo->exec("SET time_zone = '" . $offset . "'"); } catch (\Throwable $e2) { /* ignore */ }
            }
            return $pdo;
        }

        // sqlite default
        $dbPath = Env::projectRoot() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }
}
