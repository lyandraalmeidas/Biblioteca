<?php
// Modais de autenticação (login e cadastro)
?>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="loginModalLabel">Entrar</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" class="needs-validation" novalidate>
        <div class="modal-body">
            <input type="hidden" name="action" value="login">
            <div class="form-floating mb-3">
              <input type="email" name="email" class="form-control" id="loginEmail" placeholder="nome@exemplo.com" required>
              <label for="loginEmail">E-mail</label>
              <div class="invalid-feedback">Informe um e-mail válido.</div>
            </div>
            <div class="form-floating mb-1">
              <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Senha" required>
              <label for="loginPassword">Senha</label>
              <div class="invalid-feedback">Informe sua senha.</div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="registerModalLabel">Criar conta</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" class="needs-validation" novalidate>
        <div class="modal-body">
            <input type="hidden" name="action" value="register">
            <div class="form-floating mb-3">
              <input type="text" name="name" class="form-control" id="registerName" placeholder="Seu nome" required>
              <label for="registerName">Nome</label>
              <div class="invalid-feedback">Informe seu nome.</div>
            </div>
            <div class="form-floating mb-3">
              <input type="email" name="email" class="form-control" id="registerEmail" placeholder="nome@exemplo.com" required>
              <label for="registerEmail">E-mail</label>
              <div class="invalid-feedback">Informe um e-mail válido.</div>
            </div>
            <div class="form-floating mb-1">
              <input type="password" name="password" class="form-control" id="registerPassword" placeholder="Senha" minlength="6" required>
              <label for="registerPassword">Senha</label>
              <div class="invalid-feedback">A senha deve ter pelo menos 6 caracteres.</div>
            </div>
            <div class="form-text">Mínimo de 6 caracteres.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Cadastrar</button>
        </div>
      </form>
    </div>
  </div>
</div>
