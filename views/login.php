<?php

session_start();

$mensagem = $_SESSION["mensagem"] ?? null;
$sucesso  = $_SESSION["sucesso"] ?? null;
unset($_SESSION["mensagem"], $_SESSION["sucesso"]);

// Se já tiver sessão ativa, manda direto pra área certa
if (isset($_SESSION["email"])) {
    if ($_SESSION["tipo"] === "administrador") {
        header("Location: admin/painel.php");
    } else {
        header("Location: egresso/painel.php");
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janus | Entrar</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/login.css" rel="stylesheet">
</head>
<body>

<div class="login-card">
    <div class="logo-wrap">
        <svg width="88" height="88" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="47" fill="none" stroke="#f2c94c" stroke-width="2"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#f2c94c" stroke-width="1" opacity="0.4"/>
            <!-- Face voltada para a esquerda (cliente) -->
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
            <!-- Face voltada para a direita (administrador) -->
            <path d="M50 30
                     C 60 30, 67 38, 67 50
                     C 67 60, 62 66, 56 68
                     L 56 74
                     L 61 74
                     L 61 70
                     C 70 66, 76 58, 76 50
                     C 76 34, 64 22, 50 22
                     Z"
                  fill="#f2c94c"/>
            <circle cx="62" cy="46" r="2.2" fill="#ffffff"/>
            <!-- Linha central dividindo as duas faces -->
            <line x1="50" y1="22" x2="50" y2="78" stroke="#ffffff" stroke-width="1.2" opacity="0.6"/>
        </svg>
    </div>

    <h1 class="brand-title">JANUS</h1>
    <p class="brand-sub">Portal de Acesso</p>

    <?php if ($sucesso): ?>
        <div class="alert-sucesso">
            <?php echo htmlspecialchars($sucesso); ?>
        </div>
    <?php endif; ?>

    <?php if ($mensagem): ?>
        <div class="alert-erro">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <form action="../controllers/login_processar.php" method="POST">
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com" required autofocus>
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <div class="campo-senha">
                <input type="password" id="senha" name="senha" placeholder="••••••••" required>
                <button type="button" id="toggleSenha" class="toggle-senha" aria-label="Mostrar senha">Mostrar</button>
            </div>
        </div>
        <button type="submit" class="btn-entrar">Entrar</button>
    </form>

    <p class="footer-note">Não tem cadastro? <a href="egresso/cadastro.php">Cadastre-se</a></p>
</div>

<script src="../assets/js/login.js"></script>
</body>
</html>