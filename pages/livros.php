<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';

use App\Repositories\BookRepository;
use App\Services\FavoriteService; ?>
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
			$repo = new BookRepository();
			$rows = $repo->listWithRelations();
			$favorites = [];
			if (!empty($_SESSION['user']['id'])) {
				$favorites = (new FavoriteService())->all((int)$_SESSION['user']['id']);
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
					$year = htmlspecialchars((string)($r['year'] ?? ''));
					$genre = htmlspecialchars($r['category_name'] ?? '');
					$publisher = htmlspecialchars($r['publisher_name'] ?? '');

					echo '<article class="book-card card">';
					echo '<div class="card-body">';
					echo "<h5 class=\"card-title\">{$title}</h5>";
					echo "<p class=\"card-subtitle text-muted\">{$author}</p>";
					echo "<p class=\"card-text small\"><strong>Ano:</strong> {$year} &nbsp; <strong>Gênero:</strong> {$genre}</p>";
					echo "<p class=\"card-text small text-muted\"><strong>Editora:</strong> {$publisher}</p>";

					$isFav = in_array($id, $favorites, true);
					$heartClass = $isFav ? 'bi-heart-fill fav-on' : 'bi-heart fav-off';
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

