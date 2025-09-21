<?php

namespace App\Repositories;

use App\Support\Database;
use PDO;

class FavoriteRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        // Create table if not exists (supporting sqlite and mysql/mariadb)
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'mysql') {
            // MySQL/MariaDB syntax
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS favorites (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id INT UNSIGNED NOT NULL,
                    book_id INT UNSIGNED NOT NULL,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    INDEX idx_favorites_user_book (user_id, book_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
        } else {
            // SQLite syntax
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS favorites (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    book_id INTEGER NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                )'
            );
            // Basic index to speed up toggles (SQLite supports IF NOT EXISTS)
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_favorites_user_book ON favorites(user_id, book_id)');
        }
    }

    /**
     * @return int[]
     */
    public function allByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT book_id FROM favorites WHERE user_id = :u ORDER BY created_at DESC');
        $stmt->execute([':u' => $userId]);
        return array_map('intval', array_column($stmt->fetchAll() ?: [], 'book_id'));
    }

    public function isFavorited(int $userId, int $bookId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM favorites WHERE user_id = :u AND book_id = :b LIMIT 1');
        $stmt->execute([':u' => $userId, ':b' => $bookId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Toggle favorite; returns action performed: added|removed.
     */
    public function toggle(int $userId, int $bookId): string
    {
        if ($this->isFavorited($userId, $bookId)) {
            $del = $this->pdo->prepare('DELETE FROM favorites WHERE user_id = :u AND book_id = :b');
            $del->execute([':u' => $userId, ':b' => $bookId]);
            return 'removed';
        }
        $ins = $this->pdo->prepare('INSERT INTO favorites (user_id, book_id) VALUES (:u, :b)');
        $ins->execute([':u' => $userId, ':b' => $bookId]);
        return 'added';
    }
}
