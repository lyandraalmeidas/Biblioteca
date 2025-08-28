<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - My Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <main>
        <div class="w-100" style="max-width: 420px; margin: 32px auto;">
            <form action="processa_cadastro.php" method="post">
                <h2 class="mb-4 text-center">Cadastro</h2>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input type="text" name="nome" class="form-control" id="nome" placeholder="Digite seu nome completo" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Digite seu e-mail" required>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" name="telefone" class="form-control" id="telefone" placeholder="(xx) xxxx-xxxx">
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" name="senha" class="form-control" id="senha" placeholder="Crie uma senha" required>
                </div>

                <div class="mb-3">
                    <label for="senha_conf" class="form-label">Confirme a senha</label>
                    <input type="password" name="senha_conf" class="form-control" id="senha_conf" placeholder="Repita a senha" required>
                </div>

                <button type="submit" class="btn btn-pink w-100">Cadastrar</button>

                <div class="text-center mt-3">
                    <span> Já tem uma conta?</span>
                    <a href="index.php" class="btn btn-outline-pink ms-2">Entrar</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

</body>
</html>
