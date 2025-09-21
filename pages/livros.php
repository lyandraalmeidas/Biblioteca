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
					$type = htmlspecialchars($r['type'] ?? 'book');
					$year = htmlspecialchars((string)($r['year'] ?? ''));
					$genre = htmlspecialchars($r['category_name'] ?? '');
					$publisher = htmlspecialchars($r['publisher_name'] ?? '');

					echo '<article class="book-card card">';
					echo '<div class="card-body">';
					echo "<h5 class=\"card-title\">{$title} <small class=\"text-muted\">(" . ($type === 'book' ? 'Livro' : ($type === 'film' ? 'Filme' : 'Série')) . ")</small></h5>";
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

					// Timer (livro/série) e Assistido (filme)
					if ($type === 'film') {
						// Filme: botão de "já assisti" + status
						echo '<div class="d-flex align-items-center gap-2 mt-3">';
						echo '<span class="film-status" data-item-id="' . $id . '"></span>';
						echo '<button type="button" class="btn btn-sm btn-outline-success btn-watched" data-item-id="' . $id . '"><i class="bi bi-eye-fill me-1"></i><span>Já assisti</span></button>';
						echo '</div>';
					} else {
						// Livro ou Série: timer local por item
						echo '<div class="d-flex align-items-center gap-2 mt-3">';
						echo '<span class="badge bg-light text-dark"><i class="bi bi-alarm me-1"></i> <span class="item-timer" data-item-id="' . $id . '">00:00:00</span></span>';
						echo '<button type="button" class="btn btn-sm btn-outline-primary btn-timer" data-item-id="' . $id . '"><i class="bi bi-play-fill me-1"></i><span>Iniciar</span></button>';
						echo '<button type="button" class="btn btn-sm btn-outline-secondary btn-reset-timer" data-item-id="' . $id . '"><i class="bi bi-arrow-counterclockwise me-1"></i>Resetar</button>';
						echo '</div>';
					}

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

	<script>
	// Timer por item (livros/séries) e status de filme assistido — persistência no localStorage
	(function(){
		const KEY_TIMERS = 'itemTimers_v1'; // { [id]: { seconds:number, running:boolean, lastStart:number } }
		const KEY_WATCHED = 'watchedFilms_v1'; // { [id]: true }
		let state = loadTimers();
		let watched = loadWatched();
		let tickInterval = null;

		function loadTimers(){ try { return JSON.parse(localStorage.getItem(KEY_TIMERS) || '{}') || {}; } catch(e){ return {}; } }
		function saveTimers(v){ try { localStorage.setItem(KEY_TIMERS, JSON.stringify(v)); } catch(e){} }
		function loadWatched(){ try { return JSON.parse(localStorage.getItem(KEY_WATCHED) || '{}') || {}; } catch(e){ return {}; } }
		function saveWatched(v){ try { localStorage.setItem(KEY_WATCHED, JSON.stringify(v)); } catch(e){} }
		function now(){ return Math.floor(Date.now()/1000); }
		function fmt(sec){ sec = Math.max(0, Math.floor(sec||0)); const h=Math.floor(sec/3600), m=Math.floor((sec%3600)/60), s=sec%60; return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0'); }

		function getSeconds(id){ const t = state[id]; if(!t) return 0; let s = t.seconds||0; if(t.running && t.lastStart){ s += now() - t.lastStart; } return s; }
		function updateTimerUI(id){ const total = getSeconds(id); document.querySelectorAll('.item-timer[data-item-id="'+id+'"]').forEach(el => el.textContent = fmt(total)); const running = !!(state[id] && state[id].running);
			document.querySelectorAll('.btn-timer[data-item-id="'+id+'"]').forEach(btn => { const icon = btn.querySelector('i'); const txt = btn.querySelector('span'); if(running){ btn.classList.remove('btn-outline-primary'); btn.classList.add('btn-outline-danger'); if(icon) icon.className = 'bi bi-pause-fill me-1'; if(txt) txt.textContent = 'Pausar'; } else { btn.classList.remove('btn-outline-danger'); btn.classList.add('btn-outline-primary'); if(icon) icon.className = 'bi bi-play-fill me-1'; if(txt) txt.textContent = 'Iniciar'; } });
		}
		function pauseAllExcept(id){ Object.keys(state).forEach(k=>{ if(k!==String(id) && state[k] && state[k].running){ const n = now(); const t = state[k]; t.seconds = (t.seconds||0) + Math.max(0, n - (t.lastStart||n)); t.running = false; t.lastStart = 0; updateTimerUI(k); } }); saveTimers(state); }
		function toggleTimer(id){ id = String(id); if(!state[id]) state[id] = {seconds:0,running:false,lastStart:0}; if(state[id].running){ const n=now(); state[id].seconds = (state[id].seconds||0) + Math.max(0, n - (state[id].lastStart||n)); state[id].running = false; state[id].lastStart = 0; } else { pauseAllExcept(id); state[id].running = true; state[id].lastStart = now(); }
			saveTimers(state); updateTimerUI(id); ensureTick(); }
		function resetTimer(id){ id=String(id); const wasRunning = !!(state[id] && state[id].running); state[id] = {seconds:0,running:false,lastStart:0}; saveTimers(state); updateTimerUI(id); if(wasRunning) ensureTick(); }
		function ensureTick(){ if(tickInterval) return; tickInterval = setInterval(()=>{ let any=false; Object.keys(state).forEach(id=>{ if(state[id] && state[id].running){ any=true; updateTimerUI(id); } }); if(!any){ clearInterval(tickInterval); tickInterval=null; } }, 1000); }

		// Bind timer buttons
		document.querySelectorAll('.btn-timer').forEach(btn=>{ btn.addEventListener('click', function(){ const id = this.getAttribute('data-item-id'); toggleTimer(id); }); });
		document.querySelectorAll('.btn-reset-timer').forEach(btn=>{ btn.addEventListener('click', function(){ const id = this.getAttribute('data-item-id'); if(confirm('Zerar o timer deste item?')) resetTimer(id); }); });
		document.querySelectorAll('.item-timer').forEach(el=>{ updateTimerUI(el.getAttribute('data-item-id')); });

		// Filmes: Assistido
		function renderWatched(id){ id=String(id); const isW = !!watched[id]; document.querySelectorAll('.btn-watched[data-item-id="'+id+'"]').forEach(btn=>{ const span = btn.querySelector('span'); if(isW){ btn.classList.add('btn-success'); btn.classList.remove('btn-outline-success'); if(span) span.textContent='Assistido'; } else { btn.classList.remove('btn-success'); btn.classList.add('btn-outline-success'); if(span) span.textContent='Já assisti'; } }); document.querySelectorAll('.film-status[data-item-id="'+id+'"]').forEach(el=>{ el.innerHTML = isW ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Assistido</span>' : '<span class="badge bg-secondary"><i class="bi bi-circle me-1"></i>Não assistido</span>'; }); }
		function toggleWatched(id){ id=String(id); watched[id] = !watched[id]; saveWatched(watched); renderWatched(id); }
		document.querySelectorAll('.btn-watched').forEach(btn=>{ btn.addEventListener('click', function(){ const id = this.getAttribute('data-item-id'); toggleWatched(id); }); });
		document.querySelectorAll('.film-status').forEach(el=>{ renderWatched(el.getAttribute('data-item-id')); });
	})();
	</script>
</body>

