<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <main>
    <div>
            
    </div>
        <?php
        $env = [];
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) continue;
                if (strpos($line, '=') === false) continue;
                [$k, $v] = explode('=', $line, 2);
                $env[trim($k)] = trim($v, " \"'");
            }
        }

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
            echo "<div class='container mt-4'><div class='alert alert-danger'>Erro ao conectar ao banco de dados.</div></div>";
            $pdo = null;
        }

        $authors = [];
        $publishers = [];
        $categories = [];
        if (!empty($pdo)) {
            try {
                $authors = $pdo->query('SELECT id, name FROM authors ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { }
            try {
                $publishers = $pdo->query('SELECT id, name FROM publishers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { }
            try {
                $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) { }
        }

        ?>

        <div class="page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">
                        <div class="mb-4 text-center">
                            <h2 class="mb-0">Adicionar novo livro</h2>
                            <hr />
                        </div>

                        <?php if (!empty($_SESSION['flash'])): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
                        <?php endif; ?>

                        <form id="add-book-form" action="processa_livro.php" method="post" class="mb-4">
                            <h2 class="visually-hidden">Formulário adicionar livro</h2>
                            <div class="mb-3">
                                <label class="form-label">Título *</label>
                                <input class="form-control" name="title" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ano</label>
                                <input class="form-control" name="year" type="number" min="0" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Autor *</label>
                                <input class="form-control" name="author" required />
                                <div class="form-text">Escreva o nome do autor (ex: João Silva)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Editora</label>
                                <input list="publishers-list" class="form-control" name="publisher" />
                                <datalist id="publishers-list">
                                    <?php foreach ($publishers as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['name']); ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="form-text">Escreva ou selecione a editora (ex: Editora XYZ)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Categoria</label>
                                <input class="form-control" name="category" />
                                <div class="form-text">Escreva a categoria (ex: Romance)</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-pink" type="submit">Salvar livro</button>
                                <a href="home.php" class="btn btn-outline-pink">Cancelar</a>
                            </div>
                        </form>
                        <?php
                        $books = [];
                        if (!empty($pdo)) {
                            try {
                                $books = $pdo->query(
                                    "SELECT b.id, b.title, b.year, b.isbn, a.name as author, p.name as publisher, c.name as category
                                     FROM books b
                                     LEFT JOIN authors a ON a.id = b.author_id
                                     LEFT JOIN publishers p ON p.id = b.publisher_id
                                     LEFT JOIN categories c ON c.id = b.category_id
                                     ORDER BY b.title"
                                )->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Exception $e) { }
                        }
                        ?>

                        <div id="books-table-wrapper" class="mb-4" style="display:none;">
                            <h3 class="mt-3">Livros cadastrados</h3>
                            <?php if (empty($books)): ?>
                                <div class="alert alert-secondary">Nenhum livro encontrado.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped books-table">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Autor</th>
                                                <th>Editora</th>
                                                <th>Categoria</th>
                                                <th>Ano</th>
                                                <th>ISBN</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($books as $b): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($b['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($b['author'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['publisher'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['category'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['year'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['isbn'] ?? ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script> 
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> 

    <button id="toggle-books" class="toggle-books-btn" aria-pressed="false" title="Mostrar/ocultar formulário">+</button>

    <script>
    (function(){
        var btn = document.getElementById('toggle-books');
        var form = document.getElementById('add-book-form');
        if(!btn || !form) return;

        function setState(show){
            form.style.display = show ? '' : 'none';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            try{ localStorage.setItem('add-book-form-visible', show ? '1' : '0'); }catch(e){}
        }

        try{
            var stored = localStorage.getItem('add-book-form-visible');
            setState(stored === '1');
        }catch(e){ setState(true); }

        btn.addEventListener('click', function(){
            var visible = form.style.display !== 'none';
            setState(!visible);
            if(form.style.display !== 'none'){
                setTimeout(function(){ form.scrollIntoView({behavior:'smooth', block:'center'}); }, 120);
            }
        });
    })();
    </script>
</body>