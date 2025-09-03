<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <main>
        <div class="w-100" style="max-width: 360px;">
            <form action="processa_login.php" method="post">
                <?php if (!empty($_SESSION['flash'])): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); ?></div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>
                <h2 class="mb-4 text-center">Login</h2>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Digite seu e-mail" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" id="senha" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn btn-pink w-100">Entrar</button>

                <div class="text-center mt-3">
                    <span> Novo por aqui?</span>
                    <a href="cadastro.php" class="btn btn-outline-pink ms-2">Cadastre-se</a>
                </div>
            </form>
        </div>
    </main>

    <?php include '../partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>