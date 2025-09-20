<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\BookRepository;
?>
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
        $authors = $publishers = $categories = [];
        try {
            $repo = new BookRepository();
            // Reutilizamos as tabelas diretamente via simples consultas do repositório (vamos expor métodos mínimos):
            // Para evitar estender demais agora, usamos PDO do repositório para buscar as listas.
            $ref = new \ReflectionClass($repo);
            $prop = $ref->getProperty("pdo");
            $prop->setAccessible(true);
            /** @var PDO $pdo */
            $pdo = $prop->getValue($repo);
            $authors = $pdo->query('SELECT id, name FROM authors ORDER BY name')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $publishers = $pdo->query('SELECT id, name FROM publishers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            echo "<div class='container mt-4'><div class='alert alert-danger'>Erro ao carregar dados.</div></div>";
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
                        try {
                            $books = (new BookRepository())->listWithRelations();
                        } catch (\Throwable $e) {
                            $books = [];
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