<?php

session_start();

// Quem já está logado não precisa se cadastrar
if (isset($_SESSION["email"])) {
    if ($_SESSION["tipo"] === "administrador") {
        header("Location: ../admin/painel.php");
    } else {
        header("Location: painel.php");
    }
    exit;
}

$erros   = $_SESSION["erros"] ?? [];
$antigos = $_SESSION["antigos"] ?? [];
unset($_SESSION["erros"], $_SESSION["antigos"]);

function valor(array $antigos, string $campo): string
{
    return htmlspecialchars($antigos[$campo] ?? "", ENT_QUOTES, "UTF-8");
}

function erroCampo(array $erros, string $campo): void
{
    if (isset($erros[$campo])) {
        echo '<span class="erro-campo">' . htmlspecialchars($erros[$campo]) . '</span>';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janus | Cadastro de Egresso</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../../assets/css/login.css" rel="stylesheet">
    <style>
        /* Ajustes só desta página: card mais largo e estilos de erro por campo */
        .cadastro-card { max-width: 520px; }
        .cadastro-card .brand-sub { margin-bottom: 24px; }
        .linha-dupla { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 520px) { .linha-dupla { grid-template-columns: 1fr; } }
        textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #dbe2e8;
            background: #f8fafc;
            color: var(--text-dark);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            outline: none;
            resize: vertical;
            min-height: 84px;
            transition: border-color 0.2s, background 0.2s;
        }
        textarea:focus { border-color: var(--gold); background: #ffffff; }
        .campo-invalido { border-color: var(--danger) !important; }
        .erro-campo { display: block; color: var(--danger); font-size: 12px; margin-top: 5px; }
        .dica { display: block; color: var(--text-muted); font-size: 12px; margin-top: 5px; }
        .link-voltar { text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 20px; }
        .link-voltar a { color: var(--gold-dark); font-weight: 600; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }

        /* ===== Responsivo ===== */
        @media (max-width: 600px) {
            .cadastro-card { max-width: 100%; }
            textarea { font-size: 16px; }
        }
    </style>
</head>
<body>

<div class="login-card cadastro-card">

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
    <p class="brand-sub">Cadastro de Egresso</p>

    <?php if (isset($erros["geral"])): ?>
        <div class="alert-erro"><?php echo htmlspecialchars($erros["geral"]); ?></div>
    <?php elseif (!empty($erros)): ?>
        <div class="alert-erro">Corrija os campos destacados e tente novamente.</div>
    <?php endif; ?>

    <form action="../../controllers/egresso/cadastro_processar.php" method="POST">
        <div class="form-group">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" placeholder="Seu nome" required minlength="3"
                   class="<?php echo isset($erros["nome"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "nome"); ?>">
            <?php erroCampo($erros, "nome"); ?>
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" placeholder="seu@email.com" required
                   class="<?php echo isset($erros["email"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "email"); ?>">
            <?php erroCampo($erros, "email"); ?>
        </div>

        <div class="linha-dupla">
            <div class="form-group">
                <label for="curso">Curso</label>
                <input type="text" id="curso" name="curso" placeholder="Ex.: Desenvolvimento de Sistemas" required
                       class="<?php echo isset($erros["curso"]) ? "campo-invalido" : ""; ?>"
                       value="<?php echo valor($antigos, "curso"); ?>">
                <?php erroCampo($erros, "curso"); ?>
            </div>

            <div class="form-group">
                <label for="area_atuacao">Área de atuação</label>
                <input type="text" id="area_atuacao" name="area_atuacao" placeholder="Ex.: Desenvolvimento Web" required
                       class="<?php echo isset($erros["area_atuacao"]) ? "campo-invalido" : ""; ?>"
                       value="<?php echo valor($antigos, "area_atuacao"); ?>">
                <?php erroCampo($erros, "area_atuacao"); ?>
            </div>
        </div>

        <div class="form-group">
            <label for="habilidades">Habilidades</label>
            <textarea id="habilidades" name="habilidades" maxlength="300" required
                      placeholder="Ex.: PHP, HTML, CSS, JavaScript, trabalho em equipe"
                      class="<?php echo isset($erros["habilidades"]) ? "campo-invalido" : ""; ?>"><?php echo valor($antigos, "habilidades"); ?></textarea>
            <span class="dica">Separe as habilidades por vírgula.</span>
            <?php erroCampo($erros, "habilidades"); ?>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" placeholder="(99) 99999-9999" required
                   class="<?php echo isset($erros["telefone"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "telefone"); ?>">
            <?php erroCampo($erros, "telefone"); ?>
        </div>

        <div class="linha-dupla">
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres" required minlength="8"
                       class="<?php echo isset($erros["senha"]) ? "campo-invalido" : ""; ?>">
                <?php erroCampo($erros, "senha"); ?>
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required minlength="8"
                       class="<?php echo isset($erros["confirmar_senha"]) ? "campo-invalido" : ""; ?>">
                <?php erroCampo($erros, "confirmar_senha"); ?>
            </div>
        </div>

        <button type="submit" class="btn-entrar">Cadastrar</button>
    </form>

    <p class="link-voltar">Já tem cadastro? <a href="../login.php">Entrar</a></p>
</div>

</body>
</html>