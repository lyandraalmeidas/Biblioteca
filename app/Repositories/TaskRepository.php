<?php

namespace App\Repositories;

use App\Support\Database;
use PDO;

class TaskRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
    }

    /**
     * Fetch all tasks for a given user ordered by due_at (NULLs last) then created_at.
     * @return array<int,array<string,mixed>>
     */
    public function allByUser(int $userId): array
    {
        $sql = "SELECT id, user_id, title, due_at, completed_at, created_at, updated_at
                FROM tasks
                WHERE user_id = :uid
                ORDER BY (due_at IS NULL), due_at ASC, created_at ASC";
        $st = $this->pdo->prepare($sql);
        $st->execute([':uid' => $userId]);
        return $st->fetchAll();
    }

    /**
     * Create a new task for user. Returns created row as array.
     */
    public function add(int $userId, string $title, ?string $dueAt = null): array
    {
        $now = date('Y-m-d H:i:s');
        $st = $this->pdo->prepare("INSERT INTO tasks (user_id, title, due_at, created_at, updated_at)
                                   VALUES (:uid, :title, :due_at, :created_at, :updated_at)");
        $st->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':due_at' => $dueAt,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        $id = (int)$this->pdo->lastInsertId();
        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT id, user_id, title, due_at, completed_at, created_at, updated_at FROM tasks WHERE id = :id");
        $st->execute([':id' => $id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public function delete(int $userId, int $taskId): bool
    {
        $st = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id AND user_id = :uid");
        $st->execute([':id' => $taskId, ':uid' => $userId]);
        return $st->rowCount() > 0;
    }
}
