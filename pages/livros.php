<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Livros - My Library</title>
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
		<div class="container mt-4">
			<h1>Livros</h1>
			<p>Lista de livros da biblioteca.</p>

			<?php if (!empty(
					session_get_cookie_params() /* noop to keep PHP parser stable */
			)) { /* placeholder to ensure session started earlier */ } ?>

			<?php if (!empty($_SESSION['flash_success'])): ?>
				<div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
				<?php unset($_SESSION['flash_success']); ?>
			<?php endif; ?>
			<?php if (!empty($_SESSION['flash_error'])): ?>
				<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
				<?php unset($_SESSION['flash_error']); ?>
			<?php endif; ?>

			<?php
			// Reuse environment loading pattern
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
				echo '<div class="alert alert-danger">Erro ao conectar ao banco de dados.</div>';
				$pdo = null;
			}

			if ($pdo) {
				// List books with related author, category and publisher when available
				$sql = "SELECT b.id, b.title, b.year, b.isbn, a.name AS author_name, c.name AS category_name, p.name AS publisher_name, b.created_at
					FROM books b
					LEFT JOIN authors a ON b.author_id = a.id
					LEFT JOIN categories c ON b.category_id = c.id
					LEFT JOIN publishers p ON b.publisher_id = p.id
					ORDER BY b.created_at DESC";

				try {
					$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
				} catch (Exception $e) {
					$rows = [];
					echo '<div class="alert alert-warning">Erro ao recuperar livros: ' . htmlspecialchars($e->getMessage()) . '</div>';
				}

				// load favorites (simple file-based)
				$favorites = [];
				$favFile = __DIR__ . '/../storage/favorites.json';
				if (file_exists($favFile)) {
					$cf = file_get_contents($favFile);
					$decodedFav = json_decode($cf, true);
					if (is_array($decodedFav)) $favorites = $decodedFav;
				}

				if (empty($rows)) {
					echo '<div class="alert alert-info">Nenhum livro cadastrado.</div>';
				} else {
					// Render as cards grid
					echo '<div class="books-grid">';
					foreach ($rows as $r) {
						$id = (int)($r['id'] ?? 0);
						$title = htmlspecialchars($r['title'] ?? '');
						$author = htmlspecialchars($r['author_name'] ?? '');
						$year = htmlspecialchars($r['year'] ?? '');
						$genre = htmlspecialchars($r['category_name'] ?? '');
						$publisher = htmlspecialchars($r['publisher_name'] ?? '');

						echo '<article class="book-card card">';
						// thumbnail placeholder
						echo '<div class="card-body">';
						echo "<h5 class=\"card-title\">{$title}</h5>";
						echo "<p class=\"card-subtitle text-muted\">{$author}</p>";
						echo "<p class=\"card-text small\"><strong>Ano:</strong> {$year} &nbsp; <strong>Gênero:</strong> {$genre}</p>";
						echo "<p class=\"card-text small text-muted\"><strong>Editora:</strong> {$publisher}</p>";

						// favorite form: show filled heart if already favorited

								$isFav = in_array($id, $favorites, true);
								$heartClass = $isFav ? 'bi-heart-fill fav-on' : 'bi-heart fav-off';
								// button (will be handled by JS to POST via fetch)
								echo '<div class="favorite-action" style="position:absolute; top:8px; right:8px;">';
								echo '<button type="button" class="btn btn-link p-0 btn-favorite" data-book-id="' . $id . '" aria-label="Favoritar">';
								echo '<i class="bi ' . $heartClass . '" style="font-size:1.4rem; color:#e53935;"></i>';
								echo '</button>';
								echo '</div>';

						echo '</div>';
						echo '</article>';
					}
					echo '</div>';
				}
			}
			?>

		</div>
	</main>

	<?php include '../partials/footer.php'; ?>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
	<script>
	// Favorite toggle via fetch API
	(function(){
		function onClick(e){
			e.preventDefault();
			var btn = e.currentTarget;
			var bookId = btn.getAttribute('data-book-id');
			if (!bookId) return;
			var icon = btn.querySelector('.bi');
			fetch('processa_favorito.php', {
				method: 'POST',
				headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
				body: JSON.stringify({book_id: parseInt(bookId, 10)})
			}).then(function(resp){
				return resp.json().catch(function(){ return null; });
			}).then(function(data){
				if (!data) { window.location.reload(); return; }
				if (data.success && data.action){
					if (data.action === 'added'){
						icon.classList.remove('bi-heart');
						icon.classList.add('bi-heart-fill');
						icon.classList.remove('fav-off');
						icon.classList.add('fav-on');
					} else if (data.action === 'removed'){
						icon.classList.remove('bi-heart-fill');
						icon.classList.add('bi-heart');
						icon.classList.remove('fav-on');
						icon.classList.add('fav-off');
					}
				}
			}).catch(function(){
				// fallback to normal form redirect behavior
				window.location.reload();
			});
		}
		document.querySelectorAll('.btn-favorite').forEach(function(b){ b.addEventListener('click', onClick); });
	})();
	</script>
</body>

