<?php

namespace App\Repositories;

use App\Support\Database;
use PDO;

class ReadingStatRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
        $this->ensureTable();
    }

    /**
     * Add seconds to (user_id, book_id, date). Creates the row if missing.
     */
    public function addSeconds(int $userId, int $bookId, int $seconds, ?string $date = null): void
    {
        $seconds = max(0, (int)$seconds);
        if ($seconds === 0) return;
        $date = $date ?: date('Y-m-d');

        $driver = strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        if ($driver === 'mysql' || $driver === 'mariadb') {
            // Upsert using INSERT ... ON DUPLICATE KEY UPDATE
            $sql = 'INSERT INTO reading_stats (user_id, book_id, date, seconds, created_at, updated_at)
                    VALUES (:user_id, :book_id, :date, :seconds, :now, :now)
                    ON DUPLICATE KEY UPDATE seconds = seconds + VALUES(seconds), updated_at = VALUES(updated_at)';
            $stmt = $this->pdo->prepare($sql);
            $now = date('Y-m-d H:i:s');
            $stmt->execute([
                ':user_id' => $userId,
                ':book_id' => $bookId,
                ':date' => $date,
                ':seconds' => $seconds,
                ':now' => $now,
            ]);
            return;
        }

        // SQLite fallback: try update then insert if not exists
        $now = date('Y-m-d H:i:s');
        $upd = $this->pdo->prepare('UPDATE reading_stats SET seconds = seconds + :seconds, updated_at = :now WHERE user_id = :user_id AND book_id = :book_id AND date = :date');
        $upd->execute([':seconds' => $seconds, ':now' => $now, ':user_id' => $userId, ':book_id' => $bookId, ':date' => $date]);
        if ($upd->rowCount() === 0) {
            $ins = $this->pdo->prepare('INSERT INTO reading_stats (user_id, book_id, date, seconds, created_at, updated_at) VALUES (:user_id, :book_id, :date, :seconds, :now, :now)');
            $ins->execute([':user_id' => $userId, ':book_id' => $bookId, ':date' => $date, ':seconds' => $seconds, ':now' => $now]);
        }
    }

    /**
     * Sum seconds for last N days grouped by date for a user.
     * @return array<int,array{date:string,seconds:int}>
     */
    public function lastDaysByDate(int $userId, int $days = 7): array
    {
        $days = max(1, min(90, $days));
        $since = (new \DateTime($days . ' days ago'))->format('Y-m-d');
        $stmt = $this->pdo->prepare('SELECT date, SUM(seconds) AS seconds FROM reading_stats WHERE user_id = :user_id AND date >= :since GROUP BY date ORDER BY date ASC');
        $stmt->execute([':user_id' => $userId, ':since' => $since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $map = [];
        foreach ($rows as $r) { $map[$r['date']] = (int)($r['seconds'] ?? 0); }
        // Normalize continuous range
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = new \DateTime('-' . $i . ' days');
            $ds = $d->format('Y-m-d');
            $out[] = ['date' => $ds, 'seconds' => $map[$ds] ?? 0];
        }
        return $out;
    }

    /** Total seconds across all time for user */
    public function totalSeconds(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(seconds),0) AS total FROM reading_stats WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Top books by time for the user.
     * @return array<int,array{book_id:int,title:string|null,seconds:int}>
     */
    public function topBooks(int $userId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $sql = 'SELECT rs.book_id, b.title AS title, SUM(rs.seconds) AS seconds
                FROM reading_stats rs
                LEFT JOIN books b ON b.id = rs.book_id
                WHERE rs.user_id = :user_id
                GROUP BY rs.book_id, b.title
                ORDER BY seconds DESC
                LIMIT ' . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r) => [
            'book_id' => (int)($r['book_id'] ?? 0),
            'title' => $r['title'] ?? null,
            'seconds' => (int)($r['seconds'] ?? 0),
        ], $rows);
    }

    /**
     * Returns map [book_id => seconds] for the user across all time.
     * @return array<int,int>
     */
    public function timesByBookForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT book_id, SUM(seconds) AS seconds FROM reading_stats WHERE user_id = :user_id GROUP BY book_id');
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['book_id']] = (int)($r['seconds'] ?? 0);
        }
        return $out;
    }

    /**
     * List all books with user's total seconds (0 if never read).
     * @return array<int,array{book_id:int,title:string,seconds:int}>
     */
    public function listAllBooksWithUserTime(int $userId): array
    {
        $sql = 'SELECT b.id AS book_id, b.title AS title, COALESCE(SUM(rs.seconds),0) AS seconds
                FROM books b
                LEFT JOIN reading_stats rs ON rs.book_id = b.id AND rs.user_id = :user_id
                GROUP BY b.id, b.title
                ORDER BY title ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return array_map(fn($r) => [
            'book_id' => (int)($r['book_id'] ?? 0),
            'title' => (string)($r['title'] ?? ''),
            'seconds' => (int)($r['seconds'] ?? 0),
        ], $rows);
    }

    /** Ensure table exists when migrations haven't run yet (best-effort). */
    private function ensureTable(): void
    {
        try {
            $driver = strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $this->pdo->exec('CREATE TABLE IF NOT EXISTS `reading_stats` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `user_id` bigint unsigned NOT NULL,
                    `book_id` bigint unsigned NOT NULL,
                    `date` date NOT NULL,
                    `seconds` int unsigned NOT NULL DEFAULT 0,
                    `created_at` datetime NULL,
                    `updated_at` datetime NULL,
                    UNIQUE KEY `reading_stats_user_book_date_unique` (`user_id`,`book_id`,`date`),
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            } elseif ($driver === 'sqlite') {
                $this->pdo->exec('CREATE TABLE IF NOT EXISTS reading_stats (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    book_id INTEGER NOT NULL,
                    date TEXT NOT NULL,
                    seconds INTEGER NOT NULL DEFAULT 0,
                    created_at TEXT NULL,
                    updated_at TEXT NULL
                )');
                // Add unique index if absent
                $this->pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS reading_stats_user_book_date_unique ON reading_stats (user_id, book_id, date)');
            } else {
                // Best-effort generic SQL
                $this->pdo->exec('CREATE TABLE IF NOT EXISTS reading_stats (
                    id INTEGER PRIMARY KEY,
                    user_id INTEGER NOT NULL,
                    book_id INTEGER NOT NULL,
                    date VARCHAR(10) NOT NULL,
                    seconds INTEGER NOT NULL DEFAULT 0,
                    created_at VARCHAR(19) NULL,
                    updated_at VARCHAR(19) NULL
                )');
            }
        } catch (\Throwable $e) {
            // ignore; if creation fails, subsequent queries may fail when user first sends data
        }
    }
}
