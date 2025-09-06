<?php
// Sidebar lateral esquerda
// Incluído a partir de `partials/header.php`
?>
<aside class="sidebar" aria-label="Navegação principal">
	<nav>
		<ul class="nav flex-column">
			<li class="nav-item">
				<a class="nav-link" href="home.php">
					<i class="bi bi-house-door-fill me-2"></i>
					<span>Home</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="livros.php">
					<i class="bi bi-book-fill me-2"></i>
					<span>Livros</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="tarefas.php">
					<i class="bi bi-list-task me-2"></i>
					<span>Tarefas</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="rotina.php">
					<i class="bi bi-calendar-check-fill me-2"></i>
					<span>Rotina</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="tempo_uso.php">
					<i class="bi bi-clock-fill me-2"></i>
					<span>Tempo de Uso</span>
				</a>
			</li>
		</ul>
	</nav>
</aside>
<!-- toggle moved to header to avoid duplicates when sidebar is included inside offcanvas + fixed layout -->
<!-- ...existing code... -->
