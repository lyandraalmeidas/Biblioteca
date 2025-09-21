<?php

namespace App\Repositories;

use App\Support\Database;
use App\Support\Str;
use PDO;
use PDOException;

class BookRepository
{
    private PDO $pdo;
    /** @var array<string,bool> */
    private array $columnExistsCache = [];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::pdo();
    }

    /**
     * Delete a book/media by id. Also removes dependent rows in favorites and reading_stats.
     */
    public function deleteById(int $id): bool
    {
        if ($id <= 0) return false;
        try {
            $this->pdo->beginTransaction();
            // Remove dependent rows (best-effort)
            try {
                $stmt = $this->pdo->prepare('DELETE FROM favorites WHERE book_id = :id');
                $stmt->execute([':id' => $id]);
            } catch (\Throwable $e) { /* ignore if table missing */ }
            try {
                $stmt = $this->pdo->prepare('DELETE FROM reading_stats WHERE book_id = :id');
                $stmt->execute([':id' => $id]);
            } catch (\Throwable $e) { /* ignore if table missing */ }

            $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $ok = $stmt->rowCount() > 0;
            $this->pdo->commit();
            return $ok;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listWithRelations(): array
    {
        $hasType = $this->columnExists('books', 'type');
        $typeSelect = $hasType ? 'b.type' : "'book' AS type";
        $sql = "SELECT b.id, b.title, {$typeSelect}, b.year, b.isbn, a.name AS author_name, c.name AS category_name, p.name AS publisher_name, b.created_at
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
     * Create a new book/media record.
     *
     * @param string $type One of: 'book', 'film', 'series'
     */
    public function create(string $title, ?int $year, ?int $authorId, ?int $publisherId, ?int $categoryId, string $type = 'book'): void
    {
        $now = date('Y-m-d H:i:s');
        // Normalize/validate type defensively at repository level too
        $type = in_array($type, ['book','film','series'], true) ? $type : 'book';

        $hasType = $this->columnExists('books', 'type');
        if ($hasType) {
            $stmt = $this->pdo->prepare('INSERT INTO books (title, type, year, author_id, publisher_id, category_id, created_at, updated_at) VALUES (:title, :type, :year, :author_id, :publisher_id, :category_id, :created_at, :updated_at)');
            $stmt->execute([
                ':title' => $title,
                ':type' => $type,
                ':year' => $year,
                ':author_id' => $authorId,
                ':publisher_id' => $publisherId,
                ':category_id' => $categoryId,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        } else {
            // Fallback for DBs that haven't run the migration yet
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

    /**
     * Check if a column exists on a table for the current PDO driver.
     */
    private function columnExists(string $table, string $column): bool
    {
        $key = strtolower($table . '.' . $column);
        if (array_key_exists($key, $this->columnExistsCache)) {
            return $this->columnExistsCache[$key];
        }
        try {
            $driver = strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
            if ($driver === 'mysql' || $driver === 'mariadb') {
                $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE :col");
                $stmt->execute([':col' => $column]);
                $exists = (bool) $stmt->fetch();
            } elseif ($driver === 'sqlite') {
                $stmt = $this->pdo->prepare("PRAGMA table_info(`{$table}`)");
                $stmt->execute();
                $exists = false;
                while ($row = $stmt->fetch()) {
                    if (isset($row['name']) && strtolower((string)$row['name']) === strtolower($column)) {
                        $exists = true;
                        break;
                    }
                }
            } else {
                // Fallback generic attempt
                $stmt = $this->pdo->query("SELECT * FROM `{$table}` LIMIT 0");
                $exists = false;
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    $meta = $stmt->getColumnMeta($i);
                    if (isset($meta['name']) && strtolower((string)$meta['name']) === strtolower($column)) {
                        $exists = true;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            $exists = false;
        }
        $this->columnExistsCache[$key] = $exists;
        return $exists;
    }
}
