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
