<?php
session_start();

require_once __DIR__ . '/../config/config.php';

$erro = $_SESSION['cadastro_erro'] ?? '';
$dadosAntigos = $_SESSION['cadastro_dados'] ?? [];

unset($_SESSION['cadastro_erro'], $_SESSION['cadastro_dados']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar conta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh;">
        <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
            <h1 class="h3 mb-4 text-center">Criar conta</h1>

            <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/cadastro_processar.php">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-control"
                           value="<?= htmlspecialchars($dadosAntigos['nome'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($dadosAntigos['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Cadastrar</button>

                <p class="text-center mt-3 mb-0">
                    <a href="login.php">Já tenho uma conta</a>
                </p>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>