<?php
session_start();

// Load .env fallback (same pattern used in other pages)
$env = [];
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \"'");
    }
}

// Determine DB connection
$dbConnection = getenv('DB_CONNECTION') ?: ($env['DB_CONNECTION'] ?? 'sqlite');

try {
    if (strtolower($dbConnection) === 'mysql') {
        $dbHost = getenv('DB_HOST') ?: ($env['DB_HOST'] ?? '127.0.0.1');
        $dbPort = getenv('DB_PORT') ?: ($env['DB_PORT'] ?? '3306');
        $dbName = getenv('DB_DATABASE') ?: ($env['DB_DATABASE'] ?? '');
        $dbUser = getenv('DB_USERNAME') ?: ($env['DB_USERNAME'] ?? 'root');
        $dbPass = getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        $dbPath = __DIR__ . '/../database/database.sqlite';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo 'Erro de conexão com o banco de dados.';
    exit;
}

function resolve_redirect(string $to): string {
    if (preg_match('#^https?://#i', $to) || strpos($to, '/') === 0) {
        return $to;
    }
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
    return $base . '/' . ltrim($to, '\\/');
}

function flash_and_redirect($msg, $to = 'home.php') {
    $_SESSION['flash'] = $msg;
    header('Location: ' . resolve_redirect($to));
    exit;
}

function slugify(string $text): string
{
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\\d]+~u', '-', $text);

    // transliterate
    $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;

    // remove unwanted characters
    $text = preg_replace('~[^-\\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // lowercase
    $text = strtolower($text);

    if ($text === '') {
        // fallback to a short unique token
        $text = 'category-' . substr(md5(uniqid('', true)), 0, 8);
    }

    return $text;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$year = trim($_POST['year'] ?? null);
$author_name = trim($_POST['author'] ?? '');
$category_name = trim($_POST['category'] ?? null);
$publisher_name = trim($_POST['publisher'] ?? null);

if ($title === '' || $author_name === '') {
    flash_and_redirect('Título e autor são obrigatórios.');
}

// Normalize nullable values
$year = $year === '' ? null : (int)$year;
$author_name = $author_name === '' ? null : $author_name;
$category_name = $category_name === '' ? null : $category_name;

try {
    $now = (new DateTime())->format('Y-m-d H:i:s');
    // Find or create author
    $author_id = null;
    if (!empty($author_name)) {
        $stmt = $pdo->prepare('SELECT id FROM authors WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $author_name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $author_id = (int)$row['id'];
        } else {
            $ins = $pdo->prepare('INSERT INTO authors (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)');
            $ins->execute([':name' => $author_name, ':created_at' => $now, ':updated_at' => $now]);
            $author_id = (int)$pdo->lastInsertId();
        }
    }

    // Find or create category (if provided)
    $category_id = null;
    if (!empty($category_name)) {
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $category_name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $category_id = (int)$row['id'];
        } else {
            // generate slug and ensure it's unique
            $baseSlug = slugify($category_name);
            $slug = $baseSlug;
            $suffix = 1;
            // check uniqueness
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = :slug LIMIT 1');
            while (true) {
                $check->execute([':slug' => $slug]);
                $exists = $check->fetch(PDO::FETCH_ASSOC);
                if (!$exists) {
                    break;
                }
                $suffix++;
                $slug = $baseSlug . '-' . $suffix;
            }

            $ins = $pdo->prepare('INSERT INTO categories (name, slug, created_at, updated_at) VALUES (:name, :slug, :created_at, :updated_at)');
            $ins->execute([':name' => $category_name, ':slug' => $slug, ':created_at' => $now, ':updated_at' => $now]);
            $category_id = (int)$pdo->lastInsertId();
        }
    }

    // Find or create publisher (if provided)
    $publisher_id = null;
    if (!empty($publisher_name)) {
        $stmt = $pdo->prepare('SELECT id FROM publishers WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $publisher_name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $publisher_id = (int)$row['id'];
        } else {
            $ins = $pdo->prepare('INSERT INTO publishers (name, created_at, updated_at) VALUES (:name, :created_at, :updated_at)');
            $ins->execute([':name' => $publisher_name, ':created_at' => $now, ':updated_at' => $now]);
            $publisher_id = (int)$pdo->lastInsertId();
        }
    }

    // Insert book (isbn and publisher omitted per request)
    $stmt = $pdo->prepare('INSERT INTO books (title, year, author_id, publisher_id, category_id, created_at, updated_at) VALUES (:title, :year, :author_id, :publisher_id, :category_id, :created_at, :updated_at)');
    $stmt->execute([
        ':title' => $title,
        ':year' => $year,
        ':author_id' => $author_id,
        ':publisher_id' => $publisher_id,
        ':category_id' => $category_id,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);

    $_SESSION['flash'] = 'Livro adicionado com sucesso.';
    header('Location: ' . resolve_redirect('home.php'));
    exit;
} catch (PDOException $e) {
    $_SESSION['flash'] = 'Erro ao salvar livro: ' . $e->getMessage();
    header('Location: ' . resolve_redirect('home.php'));
    exit;
}
