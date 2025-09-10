<?php
//header/navbar parcial
?>
<header class="border-bottom">
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center gap-2" href="/bibliotecapessoal/">
        <i class="bi bi-journal-bookmark"></i>
        Biblioteca
      </a>
      <!-- Usage clock: mostra tempo de uso acumulado no navegador -->
      <div class="d-none d-lg-flex ms-3 align-items-center" id="usage-clock-container" title="Tempo de uso neste navegador">
        <i class="bi bi-clock-fill me-2"></i>
        <span id="usage-clock" style="font-weight:600;color:var(--accent-color);">00:00:00</span>
      </div>
      <!-- Sidebar toggle for small screens -->
      <button class="btn d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
        <i class="bi bi-list"></i>
      </button>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Alternar navegação">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
          <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-2">
            <?php if (!isset($_SESSION['user'])): ?>
              <li class="nav-item">
                <a href="index.php" class="btn btn-outline-pink w-100">
                  <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
              </li>
              <li class="nav-item">
                <a href="cadastro.php" class="btn btn-pink w-100">
                  <i class="bi bi-person-plus me-1"></i> Cadastrar
                </a>
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
                    <form method="post" action="logout.php" class="px-3 py-1">
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
      <!-- Offcanvas sidebar for small screens -->
      <?php if (empty($hideSidebar)): ?>
      <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Navegação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
          <?php include __DIR__ . '/sidebar.php'; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </nav>
</header>
<?php
// Em telas grandes, mostramos a sidebar fixa fora do header
?>
<?php if (empty($hideSidebar)): ?>
  <?php include __DIR__ . '/sidebar.php'; ?>
<?php endif; ?>

<!-- Botão hamburguer fixo no canto superior esquerdo para mostrar/ocultar a sidebar -->
<?php if (empty($hideSidebar)): ?>
<button id="sidebar-toggle" class="btn d-none d-lg-inline-flex" aria-controls="sidebar" aria-expanded="true" title="Mostrar/ocultar menu">
  <i class="bi bi-list" aria-hidden="true"></i>
  <span class="visually-hidden">Alternar menu</span>
</button>
<?php endif; ?>

<style>
/* Minimal styles for the sidebar toggle placed in the header so it loads once */
#sidebar-toggle {
  position: fixed;
  top: 12px;
  left: 12px;
  z-index: 1050;
  background: #ffffff;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 8px 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.12);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
#sidebar-toggle .bi { font-size: 1.2rem; }

/* Ensure the sidebar has a transition (if not defined elsewhere) */
.sidebar { transition: transform .22s ease-in-out, visibility .22s ease-in-out; }
body.sidebar-collapsed .sidebar { transform: translateX(-100%); visibility: hidden; }

/* Hide the toggle on small screens (offcanvas already available) */
@media(max-width:991.98px){
  #sidebar-toggle{ display:none !important; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var btn = document.getElementById('sidebar-toggle');
  var body = document.body;
  if(!btn) return;

  // initialize from localStorage
  try{
    var stored = localStorage.getItem('sidebar-collapsed');
    if(stored === '1'){
      body.classList.add('sidebar-collapsed');
      btn.setAttribute('aria-expanded','false');
    } else {
      btn.setAttribute('aria-expanded','true');
    }
  }catch(e){}

  btn.addEventListener('click', function(){
    var collapsed = body.classList.toggle('sidebar-collapsed');
    btn.setAttribute('aria-expanded', String(!collapsed));
    try{ localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0'); }catch(e){}
  });

  btn.addEventListener('keydown', function(e){
    if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); btn.click(); }
  });
});
</script>
<script>
// Usage timer: tracks seconds in localStorage and updates the #usage-clock element
(function(){
  var KEY = 'usage_seconds_v1';
  var display = document.getElementById('usage-clock');
  if(!display) return;

  // load stored seconds (number)
  var seconds = 0;
  try{ seconds = parseInt(localStorage.getItem(KEY) || '0', 10) || 0; }catch(e){ seconds = 0; }

  function fmt(s){
    var h = Math.floor(s/3600); s = s%3600; var m = Math.floor(s/60); var sec = s%60;
    return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0');
  }

  // update display initially
  display.textContent = fmt(seconds);

  // increment every second
  var interval = setInterval(function(){
    seconds += 1;
    display.textContent = fmt(seconds);
    try{ localStorage.setItem(KEY, String(seconds)); }catch(e){}
  }, 1000);

  // clear timer when user logs out (detect logout forms/buttons)
  document.addEventListener('submit', function(e){
    var form = e.target;
    if(!form || !form.action) return;
    if(form.action.indexOf('logout') !== -1){
      try{ localStorage.removeItem(KEY); }catch(e){}
    }
  }, true);

  // Optional: expose a small API for other scripts
  window.__usageTimer = { getSeconds: function(){ return seconds; }, reset: function(){ seconds = 0; display.textContent = fmt(0); try{ localStorage.setItem(KEY,'0'); }catch(e){} } };
})();
</script>