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
                            <h2 class="mb-0" id="add-form-title">Adicionar novo item</h2>
                            <hr />
                        </div>

                        <?php if (!empty($_SESSION['flash'])): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
                        <?php endif; ?>

                        <form id="add-book-form" action="processa_livro.php" method="post" class="mb-4">
                            <h2 class="visually-hidden">Formulário adicionar item</h2>
                            <div class="mb-3">
                                <label class="form-label" for="field-type">Tipo</label>
                                <select id="field-type" name="type" class="form-select">
                                    <option value="book">Livro</option>
                                    <option value="film">Filme</option>
                                    <option value="series">Série</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="field-title" id="label-title">Título *</label>
                                <input id="field-title" class="form-control" name="title" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="field-year" id="label-year">Ano</label>
                                <input id="field-year" class="form-control" name="year" type="number" min="0" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="field-author" id="label-author">Autor *</label>
                                <input id="field-author" class="form-control" name="author" required />
                                <div class="form-text" id="help-author">Escreva o nome do autor (ex: João Silva)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="field-publisher" id="label-publisher">Editora</label>
                                <input id="field-publisher" list="publishers-list" class="form-control" name="publisher" />
                                <datalist id="publishers-list">
                                    <?php foreach ($publishers as $p): ?>
                                        <option value="<?php echo htmlspecialchars($p['name']); ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                                <div class="form-text" id="help-publisher">Escreva ou selecione a editora (ex: Editora XYZ)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="field-category" id="label-category">Categoria</label>
                                <input id="field-category" class="form-control" name="category" />
                                <div class="form-text" id="help-category">Escreva a categoria (ex: Romance)</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button id="btn-submit" class="btn btn-pink" type="submit">Salvar item</button>
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
                                                <th>Tipo</th>
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
                                                    <td><?php echo htmlspecialchars($b['type'] ?? 'book'); ?></td>
                                                    <td><?php echo htmlspecialchars($b['author_name'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['publisher_name'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($b['category_name'] ?? ''); ?></td>
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
    <script>
    // Dynamic labels/placeholders per selected type
    (function(){
        var typeSel = document.getElementById('field-type');
        if (!typeSel) return;

        var h2 = document.getElementById('add-form-title');
        var lblTitle = document.getElementById('label-title');
        var lblYear = document.getElementById('label-year');
        var lblAuthor = document.getElementById('label-author');
        var lblPublisher = document.getElementById('label-publisher');
        var lblCategory = document.getElementById('label-category');
        var helpAuthor = document.getElementById('help-author');
        var helpPublisher = document.getElementById('help-publisher');
        var helpCategory = document.getElementById('help-category');
        var inTitle = document.getElementById('field-title');
        var inAuthor = document.getElementById('field-author');
        var inPublisher = document.getElementById('field-publisher');
        var inCategory = document.getElementById('field-category');
        var btnSubmit = document.getElementById('btn-submit');

        function setLabels(type){
            // Defaults for Livro
            var map = {
                title: 'Título *',
                year: 'Ano',
                author: 'Autor *',
                publisher: 'Editora',
                category: 'Categoria',
                helpAuthor: 'Escreva o nome do autor (ex: João Silva)',
                helpPublisher: 'Escreva ou selecione a editora (ex: Editora XYZ)',
                helpCategory: 'Escreva a categoria (ex: Romance)',
                heading: 'Adicionar novo Livro',
                submit: 'Salvar livro',
                placeholders: {
                    title: 'Ex: Dom Casmurro',
                    author: 'Ex: Machado de Assis',
                    publisher: 'Ex: Editora XYZ',
                    category: 'Ex: Romance'
                }
            };

            if (type === 'film'){
                map.author = 'Diretor *';
                map.publisher = 'Produtora/Estúdio';
                map.category = 'Gênero';
                map.helpAuthor = 'Escreva o nome do diretor (ex: Christopher Nolan)';
                map.helpPublisher = 'Escreva a produtora ou estúdio (ex: Warner Bros.)';
                map.helpCategory = 'Escreva o gênero (ex: Ação)';
                map.heading = 'Adicionar novo Filme';
                map.submit = 'Salvar filme';
                map.placeholders = {
                    title: 'Ex: O Senhor dos Anéis',
                    author: 'Ex: Peter Jackson',
                    publisher: 'Ex: New Line Cinema',
                    category: 'Ex: Fantasia'
                };
            } else if (type === 'series'){
                map.author = 'Criador *';
                map.publisher = 'Produtora/Estúdio';
                map.category = 'Gênero';
                map.helpAuthor = 'Escreva o nome do criador (ex: Vince Gilligan)';
                map.helpPublisher = 'Escreva a produtora ou estúdio (ex: AMC Studios)';
                map.helpCategory = 'Escreva o gênero (ex: Drama)';
                map.heading = 'Adicionar nova Série';
                map.submit = 'Salvar série';
                map.placeholders = {
                    title: 'Ex: Breaking Bad',
                    author: 'Ex: Vince Gilligan',
                    publisher: 'Ex: AMC Studios',
                    category: 'Ex: Drama'
                };
            }

            if (h2) h2.textContent = map.heading;
            if (lblTitle) lblTitle.textContent = map.title;
            if (lblYear) lblYear.textContent = map.year;
            if (lblAuthor) lblAuthor.textContent = map.author;
            if (lblPublisher) lblPublisher.textContent = map.publisher;
            if (lblCategory) lblCategory.textContent = map.category;
            if (helpAuthor) helpAuthor.textContent = map.helpAuthor;
            if (helpPublisher) helpPublisher.textContent = map.helpPublisher;
            if (helpCategory) helpCategory.textContent = map.helpCategory;
            if (btnSubmit) btnSubmit.textContent = map.submit;

            if (inTitle) inTitle.placeholder = map.placeholders.title;
            if (inAuthor) inAuthor.placeholder = map.placeholders.author;
            if (inPublisher) inPublisher.placeholder = map.placeholders.publisher;
            if (inCategory) inCategory.placeholder = map.placeholders.category;
        }

        // Initialize on load
        try { setLabels(typeSel.value); } catch(e) {}

        // Update on change
        typeSel.addEventListener('change', function(){ setLabels(typeSel.value); });
    })();
    </script>
</body>