<?php require __DIR__ . '/includes/init.php'; ?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca Pessoal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="d-flex flex-column min-vh-100">
  
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080" id="toastContainer"></div>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="container py-4 flex-grow-1">
    <?php include __DIR__ . '/pages/home.php'; ?>
  </main>

  <?php include __DIR__ . '/partials/auth_modals.php'; ?>

  <?php include __DIR__ . '/partials/footer.php'; ?>
  <?php $act = $_POST['action'] ?? ''; ?>
  <script>
    (() => {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
        }, false);
      });
    })();

    function showToast(message, variant = 'info') {
      const container = document.getElementById('toastContainer');
      const wrapper = document.createElement('div');
      wrapper.className = `toast align-items-center text-bg-${variant} border-0`;
      wrapper.role = 'alert';
      wrapper.ariaLive = 'assertive';
      wrapper.ariaAtomic = 'true';
      wrapper.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">${message}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>`;
      container.appendChild(wrapper);
      const toast = new bootstrap.Toast(wrapper, { delay: 5000 });
      toast.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($errors)): ?>
        <?php if ($act === 'login'): ?>
          new bootstrap.Modal(document.getElementById('loginModal')).show();
        <?php elseif ($act === 'register'): ?>
          new bootstrap.Modal(document.getElementById('registerModal')).show();
        <?php endif; ?>
        <?php foreach ($errors as $e): ?>
          showToast(<?php echo json_encode($e, JSON_UNESCAPED_UNICODE); ?>, 'danger');
        <?php endforeach; ?>
  <?php elseif ($success): ?>
        new bootstrap.Modal(document.getElementById('loginModal')).show();
        showToast(<?php echo json_encode($success, JSON_UNESCAPED_UNICODE); ?>, 'success');
      <?php endif; ?>
    });
  </script>

</body>
</html>