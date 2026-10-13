<?php

session_start();

// Bloqueia quem não está logado ou não é administrador
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "administrador") {
    header("Location: ../login.php");
    exit;
}

$erros   = $_SESSION["erros"] ?? [];
$antigos = $_SESSION["antigos"] ?? [];
$sucesso = $_SESSION["sucesso"] ?? null;
unset($_SESSION["erros"], $_SESSION["antigos"], $_SESSION["sucesso"]);

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
    <title>Janus | Cadastro de Vaga</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../../assets/css/login.css" rel="stylesheet">
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <style>
        .topbar-cadastro {
            display:flex; align-items:center; gap:14px; min-height:72px; padding:0 32px;
            background:#fff; border-bottom:1px solid #dbe2e8; margin-bottom:28px;
        }
        .topbar-cadastro .topbar-esq,.topbar-cadastro .topbar-dir { display:flex; align-items:center; gap:20px; }
        .topbar-cadastro .marca { font-family:'Cinzel',serif; font-weight:700; font-size:20px; letter-spacing:3px; color:#a77a14; text-decoration:none; }
        .topbar-cadastro .menu { display:flex; gap:4px; }
        .topbar-cadastro .menu a { color:#697586; text-decoration:none; font-size:14px; padding:9px 12px; border-radius:8px; }
        .topbar-cadastro .menu a:hover,.topbar-cadastro .menu a.ativo { background:#eef2f5; color:#a77a14; }
        .topbar-cadastro .topbar-dir { margin-left:auto; color:#697586; font-size:13px; }
        .topbar-cadastro .sair { color:#b84b4b; text-decoration:none; border:1px solid #dbe2e8; padding:7px 12px; border-radius:8px; }
        .topbar-cadastro .janus-nav a { display:inline-flex; align-items:center; gap:8px; }
        .topbar-cadastro .nav-icone { display:inline-flex; }
        .topbar-cadastro .nav-icone svg { width:17px; height:17px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        @media (max-width:720px) {
            .topbar-cadastro { position:relative; flex-wrap:wrap; min-height:60px; padding:10px 16px; margin-bottom:18px; }
            .topbar-cadastro .topbar-esq { display:contents; }
            .topbar-cadastro .marca { order:1; margin-right:auto; }
            .topbar-cadastro .menu-toggle { order:2; }
            .topbar-cadastro nav.menu,.topbar-cadastro .topbar-dir { display:none; }
            .topbar-cadastro.aberto nav.menu { order:3; flex:1 0 100%; display:flex; flex-direction:column; gap:8px; margin-top:12px; }
            .topbar-cadastro.aberto nav.menu a { display:flex; justify-content:flex-start; align-items:center; gap:10px; min-height:50px; padding:12px 16px; background:#f4f6f8; border:1px solid #dbe2e8; border-radius:8px; }
            .topbar-cadastro.aberto nav.menu a.ativo { background:#eef2f5; color:#a77a14; border-left:4px solid #f2c94c; }
            .topbar-cadastro.aberto .topbar-dir { order:4; flex:1 0 100%; display:flex; justify-content:space-between; margin-top:12px; padding-top:12px; border-top:1px solid #dbe2e8; }
        }


        /* Nesta tela, a marca do menu lateral fica oculta para evitar o JANUS
           solto ao lado do formulário. A logo oficial permanece dentro do card. */
        /* Nesta tela a navegação já é fornecida pela sidebar. O cabeçalho antigo
           continha outro JANUS e criava a marca solta/desalinhada ao lado do card. */
        body.pagina-cadastro-vaga .topbar-cadastro { display: none !important; }
        body.pagina-cadastro-vaga .janus-sidebar-logo { display: none !important; }
        body.pagina-cadastro-vaga .janus-sidebar-links { margin-top: 0; }

        /* Ajustes só desta página: card mais largo e estilos de erro por campo */
        .cadastro-card { max-width: 560px; padding-top: 32px; }
        .form-titulo { margin: 0 0 24px; text-align: center; font-family: 'Inter', sans-serif; font-size: 21px; font-weight: 700; color: var(--text-dark); }
        .cadastro-card form { width: 100%; }
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
            min-height: 120px;
            transition: border-color 0.2s, background 0.2s;
        }
        textarea:focus { border-color: var(--gold); background: #ffffff; }
        .campo-invalido { border-color: var(--danger) !important; }
        .erro-campo { display: block; color: var(--danger); font-size: 12px; margin-top: 5px; }
        .alert-sucesso {
            background: #f4faf5;
            border: 1px solid #d3ead7;
            color: #2f7d43;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .link-voltar { text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 20px; }
        .link-voltar a { color: var(--gold-dark); font-weight: 600; text-decoration: none; }
        .link-voltar a:hover { text-decoration: underline; }

        /* ===== Responsivo ===== */
        @media (max-width: 760px) {
            .topbar-cadastro { margin-bottom: 14px; }
        }
        @media (max-width: 600px) {
            .cadastro-card { max-width: 100%; padding: 24px 18px 22px; border-radius: 10px; }
            .form-titulo { font-size: 19px; margin-bottom: 20px; }
            .form-group { margin-bottom: 16px; }
            textarea { font-size: 16px; min-height: 140px; }
            .link-voltar { line-height: 1.6; }
        }
        @media (max-width: 380px) {
            body { padding-left: 8px; padding-right: 8px; }
            .cadastro-card { padding-left: 14px; padding-right: 14px; }
            .topbar-cadastro { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body class="pagina-cadastro-vaga">

<header class="topbar topbar-cadastro">
    <div class="topbar-esq">
        <a class="marca" href="painel.php">JANUS</a>
        <nav class="menu janus-nav" id="menu-cadastro" aria-label="Navegação principal">
            <a href="painel.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span>Egressos</span></a>
            <a href="lista-vagas.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/><path d="M10 12v2h4v-2"/></svg></span><span>Vagas</span></a>
            <a href="cadastro-vaga.php" class="ativo"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span><span>Nova vaga</span></a>
        </nav>
    </div>
    <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-cadastro"><span></span><span></span><span></span></button>
</header>
<script src="../../assets/js/menu.js" defer></script>

<div class="login-card cadastro-card">

    <div class="logo-wrap">
        <svg width="88" height="88" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" aria-label="Logo Janus">
            <circle cx="50" cy="50" r="47" fill="none" stroke="#f2c94c" stroke-width="2"/>
            <circle cx="50" cy="50" r="40" fill="none" stroke="#f2c94c" stroke-width="1" opacity="0.4"/>
            <path d="M50 30 C 40 30, 33 38, 33 50 C 33 60, 38 66, 44 68 L 44 74 L 39 74 L 39 70 C 30 66, 24 58, 24 50 C 24 34, 36 22, 50 22 Z" fill="#dcc182"/>
            <circle cx="38" cy="46" r="2.2" fill="#ffffff"/>
            <path d="M50 30 C 60 30, 67 38, 67 50 C 67 60, 62 66, 56 68 L 56 74 L 61 74 L 61 70 C 70 66, 76 58, 76 50 C 76 34, 64 22, 50 22 Z" fill="#f2c94c"/>
            <circle cx="62" cy="46" r="2.2" fill="#ffffff"/>
            <line x1="50" y1="22" x2="50" y2="78" stroke="#ffffff" stroke-width="1.2" opacity="0.6"/>
        </svg>
    </div>

    <h1 class="brand-title">JANUS</h1>
    <p class="brand-sub">Cadastro de Vaga</p>

    <?php if ($sucesso): ?>
        <div class="alert-sucesso"><?php echo htmlspecialchars($sucesso); ?></div>
    <?php endif; ?>

    <?php if (isset($erros["geral"])): ?>
        <div class="alert-erro"><?php echo htmlspecialchars($erros["geral"]); ?></div>
    <?php elseif (!empty($erros)): ?>
        <div class="alert-erro">Corrija os campos destacados e tente novamente.</div>
    <?php endif; ?>

    <form action="../../controllers/admin/cadastro-vaga_processar.php" method="POST">
        <div class="form-group">
            <label for="titulo">Título da vaga</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ex.: Desenvolvedor Web Júnior" required maxlength="100"
                   class="<?php echo isset($erros["titulo"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "titulo"); ?>">
            <?php erroCampo($erros, "titulo"); ?>
        </div>

        <div class="form-group">
            <label for="empresa">Empresa</label>
            <input type="text" id="empresa" name="empresa" placeholder="Nome da empresa" required maxlength="100"
                   class="<?php echo isset($erros["empresa"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "empresa"); ?>">
            <?php erroCampo($erros, "empresa"); ?>
        </div>

        <div class="form-group">
            <label for="area">Área</label>
            <input type="text" id="area" name="area" placeholder="Ex.: Tecnologia da Informação" required maxlength="100"
                   class="<?php echo isset($erros["area"]) ? "campo-invalido" : ""; ?>"
                   value="<?php echo valor($antigos, "area"); ?>">
            <?php erroCampo($erros, "area"); ?>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" maxlength="1000" required
                      placeholder="Descreva as atividades, requisitos e benefícios da vaga"
                      class="<?php echo isset($erros["descricao"]) ? "campo-invalido" : ""; ?>"><?php echo valor($antigos, "descricao"); ?></textarea>
            <?php erroCampo($erros, "descricao"); ?>
        </div>

        <button type="submit" class="btn-entrar">Cadastrar vaga</button>
    </form>

    <p class="link-voltar"><a href="painel.php">Voltar ao painel</a> &nbsp;·&nbsp; <a href="lista-vagas.php">Ver lista de vagas</a></p>
</div>

</body>
</html>