<?php

namespace App\Repositories;

use App\Support\Database;
use App\Support\Str;
use PDO;
use PDOException;

class BookRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listWithRelations(): array
    {
        $sql = "SELECT b.id, b.title, b.year, b.isbn, a.name AS author_name, c.name AS category_name, p.name AS publisher_name, b.created_at
                FROM books b
                LEFT JOIN authors a ON b.author_id = a.id
                LEFT JOIN categories c ON b.category_id = c.id
                LEFT JOIN publishers p ON b.publisher_id = p.id
                ORDER BY b.created_at DESC";
        try {
            return $this->pdo->query($sql)->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function findOrCreateAuthor(?string $name): ?int
    {
        if ($name === null || $name === '') return null;
        $stmt = $this->pdo->prepare('SELECT id FROM authors WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['id'];
        $now = date('Y-m-d H:i:s');
        $ins = $this->pdo->prepare('INSERT INTO authors (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)');
        $ins->execute([':name' => $name, ':created_at' => $now, ':updated_at' => $now]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findOrCreateCategory(?string $name): ?int
    {
        if ($name === null || $name === '') return null;
        $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['id'];

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $check = $this->pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
        $i = 1;
        while (true) {
            $check->execute([':slug' => $slug]);
            $exists = $check->fetch();
            if (!$exists) break;
            $i++;
            $slug = $baseSlug . '-' . $i;
        }
        $now = date('Y-m-d H:i:s');
        $ins = $this->pdo->prepare('INSERT INTO categories (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)');
        $ins->execute([':name' => $name, ':slug' => $slug, ':created_at' => $now, ':updated_at' => $now]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findOrCreatePublisher(?string $name): ?int
    {
        if ($name === null || $name === '') return null;
        $stmt = $this->pdo->prepare('SELECT id FROM publishers WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        $row = $stmt->fetch();
        if ($row) return (int)$row['id'];
        $now = date('Y-m-d H:i:s');
        $ins = $this->pdo->prepare('INSERT INTO publishers (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)');
        $ins->execute([':name' => $name, ':created_at' => $now, ':updated_at' => $now]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Create a new book record.
     */
    public function create(string $title, ?int $year, ?int $authorId, ?int $publisherId, ?int $categoryId): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('INSERT INTO books (title, year, author_id, publisher_id, category_id, created_at, updated_at) VALUES (:title, :year, :author_id, :publisher_id, :category_id, :created_at, :updated_at)');
        $stmt->execute([
            ':title' => $title,
            ':year' => $year,
            ':author_id' => $authorId,
            ':publisher_id' => $publisherId,
            ':category_id' => $categoryId,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}
