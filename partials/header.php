<?php
// Header/navbar parcial
?>
<header class="border-bottom">
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="/bibliotecapessoal/">
        <i class="bi bi-journal-bookmark"></i>
        Biblioteca
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link active" aria-current="page" href="/bibliotecapessoal/">Home</a>
            </li>
          </ul>

          <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-2">
            <?php if (!isset($_SESSION['user'])): ?>
              <li class="nav-item">
                <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#loginModal">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
              </li>
              <li class="nav-item">
                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#registerModal">
                  <i class="bi bi-person-plus me-1"></i> Cadastrar
                </button>
              </li>
            <?php else: ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person-circle"></i>
                  <span><?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><span class="dropdown-item-text small text-muted"><?php echo htmlspecialchars($_SESSION['user']['email']); ?></span></li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form method="post" class="px-3 py-1">
                      <input type="hidden" name="action" value="logout">
                      <button type="submit" class="btn btn-link dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right me-1"></i> Sair
                      </button>
                    </form>
                  </li>
                </ul>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</header>