<?php

namespace App\Repositories;

use App\Support\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * @return int Inserted user id
     */
    public function create(string $name, string $email, ?string $phone, string $passwordHash): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, telefone, password, created_at, updated_at) VALUES (:name, :email, :telefone, :password, :created_at, :updated_at)');
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':telefone' => $phone,
            ':password' => $passwordHash,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
