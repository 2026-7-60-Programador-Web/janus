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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janus | Criar conta</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
</head>
<body>

<div class="login-card">
    <div class="logo-wrap">
        <svg width="88" height="88" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="47" fill="none" stroke="#b4893f" stroke-width="2"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#b4893f" stroke-width="1" opacity="0.4"/>
            <path d="M50 30
                     C 40 30, 33 38, 33 50
                     C 33 60, 38 66, 44 68
                     L 44 74
                     L 39 74
                     L 39 70
                     C 30 66, 24 58, 24 50
                     C 24 34, 36 22, 50 22
                     Z"
                  fill="#dcc182"/>
            <circle cx="38" cy="46" r="2.2" fill="#ffffff"/>
            <path d="M50 30
                     C 60 30, 67 38, 67 50
                     C 67 60, 62 66, 56 68
                     L 56 74
                     L 61 74
                     L 61 70
                     C 70 66, 76 58, 76 50
                     C 76 34, 64 22, 50 22
                     Z"
                  fill="#b4893f"/>
            <circle cx="62" cy="46" r="2.2" fill="#ffffff"/>
            <line x1="50" y1="22" x2="50" y2="78" stroke="#ffffff" stroke-width="1.2" opacity="0.6"/>
        </svg>
    </div>

    <h1 class="brand-title">JANUS</h1>
    <p class="brand-sub">Criar conta</p>

    <?php if ($erro): ?>
        <div class="alert-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form method="POST" action="../controllers/cadastro_processar.php">
        <div class="form-group">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" placeholder="Seu nome"
                   value="<?= htmlspecialchars($dadosAntigos['nome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com"
                   value="<?= htmlspecialchars($dadosAntigos['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha</label>
            <div class="campo-senha">
                <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                <button type="button" id="toggleSenha" class="toggle-senha" aria-label="Mostrar senha">Mostrar</button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmar_senha">Confirmar senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-entrar">Cadastrar</button>
    </form>

    <p class="footer-note">Já tem cadastro? <a href="login.php">Entrar</a></p>
</div>

<script src="../assets/js/login.js"></script>
</body>
</html>