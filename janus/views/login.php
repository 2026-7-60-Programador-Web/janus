<?php

session_start();

$mensagem = $_SESSION["mensagem"] ?? null;
unset($_SESSION["mensagem"]);

// Se já tiver sessão ativa, manda direto pra área certa
if (isset($_SESSION["email"])) {
    if ($_SESSION["tipo"] === "administrador") {
        header("Location: area-admin.php");
    } else {
        header("Location: area-cliente.php");
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
    <style>
        :root {
            --bg: #ffffff;
            --bg-page: #f7f6f3;
            --gold: #b4893f;
            --gold-dark: #8f6c2f;
            --text-dark: #1c1c1c;
            --text-muted: #7a7a7a;
            --danger: #b4453f;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            padding: 24px;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            background: var(--bg);
            border: 1px solid #e6e2d8;
            border-radius: 16px;
            padding: 40px 32px 32px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .brand-title {
            font-family: 'Cinzel', serif;
            text-align: center;
            font-size: 26px;
            letter-spacing: 4px;
            color: var(--text-dark);
            margin: 0;
        }

        .brand-sub {
            text-align: center;
            font-size: 12px;
            letter-spacing: 2px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin: 4px 0 28px;
        }

        .alert-erro {
            background: #fdf6f4;
            border: 1px solid #f0d9d5;
            color: var(--danger);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #e0ddd4;
            background: #fdfcfa;
            color: var(--text-dark);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        input:focus {
            border-color: var(--gold);
            background: #ffffff;
        }

        input::placeholder { color: #b3b0a6; }

        .btn-entrar {
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: none;
            border-radius: 8px;
            background: var(--gold);
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-entrar:hover {
            background: var(--gold-dark);
        }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #b3b0a6;
            margin-top: 24px;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-wrap">
        <svg width="88" height="88" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="47" fill="none" stroke="#b4893f" stroke-width="2"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#b4893f" stroke-width="1" opacity="0.4"/>
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
                  fill="#b4893f"/>
            <circle cx="62" cy="46" r="2.2" fill="#ffffff"/>
            <!-- Linha central dividindo as duas faces -->
            <line x1="50" y1="22" x2="50" y2="78" stroke="#ffffff" stroke-width="1.2" opacity="0.6"/>
        </svg>
    </div>

    <h1 class="brand-title">JANUS</h1>
    <p class="brand-sub">Portal de Acesso</p>

    <?php if ($mensagem): ?>
        <div class="alert-erro">
            <?php echo htmlspecialchars($mensagem); ?>
        </div>
    <?php endif; ?>

    <form action="../controllers/login_processar.php" method="POST">
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com" required>
        </div>
        <div class="form-group">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-entrar">Entrar</button>
    </form>

    <p class="footer-note">JANUS &middot; Duas portas, um só acesso</p>
</div>

</body>
</html>
