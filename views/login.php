<?php
require_once __DIR__ . '/../core/autenticacao.php';

if (estaLogado()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$erro = $_SESSION['login_erro'] ?? '';
$sucesso = $_SESSION['login_sucesso'] ?? '';
unset($_SESSION['login_erro'], $_SESSION['login_sucesso']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
            <h1 class="h3 mb-4 text-center">Entrar</h1>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/login_processar.php">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Entrar</button>

                <p class="text-center mt-3 mb-0">
                    <a href="cadastro.php">Criar uma conta</a>
                </p>
            </form>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>