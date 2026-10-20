<?php

session_start();

require_once __DIR__ . '/../../core/conexao.php';

// Bloqueia quem não está logado ou não é administrador
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "administrador") {
    header("Location: ../login.php");
    exit;
}

// Mensagens vindas do controller (mostradas uma vez só)
$sucesso     = $_SESSION["sucesso"] ?? null;
$erroGeral   = $_SESSION["erro_geral"] ?? null;
$errosEdicao = $_SESSION["erros_edicao"] ?? [];
$antigosEdit = $_SESSION["antigos_edicao"] ?? null;
unset($_SESSION["sucesso"], $_SESSION["erro_geral"], $_SESSION["erros_edicao"], $_SESSION["antigos_edicao"]);

// Token CSRF usado nos formulários de editar/excluir
if (empty($_SESSION["csrf"])) {
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION["csrf"];

// Lê as vagas do banco (mais recentes primeiro)
$stmt = $pdo->query("SELECT * FROM vagas ORDER BY criado_em DESC, id DESC");
$vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Áreas distintas para o filtro
$areas = array_values(array_unique(array_filter(array_map(fn($v) => $v["area"] ?? "", $vagas))));
sort($areas, SORT_NATURAL | SORT_FLAG_CASE);

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function dataBr(?string $data): string
{
    $t = $data ? strtotime($data) : false;
    return $t ? date("d/m/Y", $t) : "—";
}

function inicial(string $texto): string
{
    return mb_strtoupper(mb_substr(trim($texto), 0, 1)) ?: "?";
}

function icone(string $nome): string
{
    $icones = [
        "busca"   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        "filtro"  => '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
        "baixar"  => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        "olho"    => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        "lapis"   => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>',
        "lixo"    => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/>',
        "mais"    => '<path d="M12 5v14M5 12h14"/>',
        "sair"    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        "voltar"  => '<path d="m15 18-6-6 6-6"/>',
        "fechar"  => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($icones[$nome] ?? "") . '</svg>';
}

function erroEdicao(array $erros, string $campo): void
{
    if (isset($erros[$campo])) {
        echo '<span class="erro-campo">' . e($erros[$campo]) . '</span>';
    }
}

$totalVagas = count($vagas);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janus | Lista de Vagas</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #ffffff;
            --bg-page: #f4f6f8;
            --gold: #f2c94c;
            --gold-dark: #a77a14;
            --gold-soft: #fff8df;
            --text-dark: #1c1c1c;
            --text-muted: #697586;
            --danger: #b4453f;
            --danger-soft: #fdeeed;
            --success: #2f7d43;
            --success-soft: #f4faf5;
            --borda: #dbe2e8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            font-size: 14px;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
            font-size: inherit;
        }

        /* ---------- Barra superior ---------- */
        .topbar {
            display: flex;
            align-items: center;
            gap: 14px;
            height: 72px;
            padding: 0 32px;
            background: var(--bg);
            border-bottom: 1px solid var(--borda);
        }

        .topbar-marca {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 3px;
            color: var(--gold-dark);
            text-decoration: none;
        }

        .topbar-espaco {
            flex: 1;
        }

        .topbar-usuario {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--gold);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ---------- Menu principal ---------- */
        .menu-principal {
            display: flex;
            gap: 6px;
            margin-left: 12px;
        }

        .menu-principal a {
            color: var(--text-dark);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            border-bottom: 3px solid transparent;
            transition: background .15s, color .15s;
        }

        .menu-principal a:hover {
            background: var(--gold-soft);
            color: var(--gold-dark);
        }

        .menu-principal a.ativo {
            background: var(--gold-soft);
            color: var(--gold-dark);
            font-weight: 700;
            border-bottom-color: var(--gold);
        }

        .menu-principal a:focus-visible {
            outline: 2px solid var(--gold);
            outline-offset: 2px;
        }

        @media (max-width: 720px) {
            .menu-principal {
                order: 10;
                flex: 1 0 100%;
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 8px;
                margin: 4px 0 0;
            }

            .menu-principal a {
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                min-height: 48px;
                padding: 12px 6px;
                background: var(--bg-page);
                border: 1px solid var(--borda);
                border-bottom-width: 3px;
            }

            .menu-principal a.ativo {
                background: var(--gold);
                color: #fff;
                border-color: var(--gold-dark);
            }
        }

        .janus-nav a { display: inline-flex; align-items: center; gap: 8px; transition: background .15s, color .15s, transform .15s; }
        .janus-nav a:hover { transform: translateY(-1px); }
        .janus-nav .nav-icone { display: inline-flex; align-items: center; }
        .janus-nav .nav-icone svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        /* ---------- Botões ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 42px;
            padding: 0 18px;
            border-radius: 10px;
            border: 1px solid var(--borda);
            background: var(--bg);
            color: var(--text-dark);
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, border-color .15s;
        }

        .btn:hover {
            background: #faf9f6;
            border-color: #d6d1c3;
        }

        .btn-primario {
            background: var(--gold-dark);
            border-color: var(--gold-dark);
            color: #fff;
        }

        .btn-primario:hover {
            background: #7a5c27;
            border-color: #7a5c27;
        }

        .btn-perigo {
            background: var(--danger);
            border-color: var(--danger);
            color: #fff;
        }

        .btn-perigo:hover {
            background: #9b3a35;
            border-color: #9b3a35;
        }

        .btn-icone {
            width: 34px;
            height: 34px;
            padding: 0;
            justify-content: center;
            border-radius: 8px;
            color: #333;
        }

        .btn-icone:hover {
            background: var(--gold-soft);
            border-color: var(--gold);
            color: var(--gold-dark);
        }

        .btn-icone.excluir:hover {
            background: var(--danger-soft);
            border-color: var(--danger);
            color: var(--danger);
        }

        /* ---------- Conteúdo ---------- */
        .conteudo {
            padding: 24px 32px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .cabecalho-pagina {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .cabecalho-pagina h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }

        .cabecalho-pagina p {
            margin: 4px 0 0;
            color: var(--text-muted);
        }

        .alerta {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .alerta-sucesso {
            background: var(--success-soft);
            border: 1px solid #d3ead7;
            color: var(--success);
        }

        .alerta-erro {
            background: var(--danger-soft);
            border: 1px solid #f3cfcc;
            color: var(--danger);
        }

        .cartao {
            background: var(--bg);
            border: 1px solid var(--borda);
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .ferramentas {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 20px 22px;
            border-bottom: 1px solid var(--borda);
        }

        .ferramentas-direita {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .campo-busca {
            position: relative;
            flex: 1 1 320px;
            max-width: 400px;
        }

        .campo-busca svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .campo-busca input {
            width: 100%;
            height: 42px;
            padding: 0 14px 0 42px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: var(--bg);
            outline: none;
        }

        .campo-busca input:focus {
            border-color: var(--gold);
        }

        .filtro-area {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .filtro-area svg {
            position: absolute;
            left: 14px;
            pointer-events: none;
        }

        .filtro-area select {
            height: 42px;
            padding: 0 14px 0 40px;
            border: 1px solid var(--borda);
            border-radius: 10px;
            background: var(--bg);
            font-weight: 600;
            cursor: pointer;
            outline: none;
        }

        .filtro-area select:focus {
            border-color: var(--gold);
        }

        /* ---------- Tabela ---------- */
        .tabela-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 820px;
        }

        thead th {
            text-align: left;
            padding: 16px 22px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            color: var(--text-muted);
            background: #faf9f6;
            border-bottom: 1px solid var(--borda);
            white-space: nowrap;
        }

        tbody td {
            padding: 16px 22px;
            border-bottom: 1px solid #efece4;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fcfbf8;
        }

        .vaga-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .vaga-titulo {
            font-weight: 600;
            font-size: 15px;
        }

        .vaga-sub {
            font-size: 13px;
            color: var(--text-muted);
            max-width: 340px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vaga-id {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 12px;
        }

        .empresa-nome {
            font-weight: 500;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--gold-soft);
            color: var(--gold-dark);
            font-weight: 600;
            font-size: 12.5px;
        }

        .pill::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold);
        }

        .acoes {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        th.col-acoes {
            text-align: right;
        }

        .muted {
            color: var(--text-muted);
        }

        .vazio {
            text-align: center;
            padding: 56px 20px !important;
            color: var(--text-muted);
        }

        .rodape-tabela {
            padding: 14px 22px;
            border-top: 1px solid var(--borda);
            color: var(--text-muted);
            font-size: 13px;
            background: #faf9f6;
        }

        /* ---------- Modais (<dialog>) ---------- */
        dialog {
            border: none;
            border-radius: 16px;
            padding: 0;
            width: min(520px, calc(100vw - 32px));
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            color: var(--text-dark);
        }

        dialog::backdrop {
            background: rgba(28, 28, 28, 0.45);
        }

        .modal-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid var(--borda);
        }

        .modal-topo h2 {
            margin: 0;
            font-size: 17px;
        }

        .modal-corpo {
            padding: 22px 24px;
        }

        .modal-rodape {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--borda);
            background: #faf9f6;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            border-radius: 8px;
            border: 1px solid #dbe2e8;
            background: #f8fafc;
            outline: none;
            color: var(--text-dark);
            transition: border-color .2s, background .2s;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--gold);
            background: #fff;
        }

        .campo-invalido {
            border-color: var(--danger) !important;
        }

        .erro-campo {
            display: block;
            color: var(--danger);
            font-size: 12px;
            margin-top: 5px;
        }

        .detalhe {
            margin-bottom: 14px;
        }

        .detalhe dt {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 3px;
        }

        .detalhe dd {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* ---------- Responsivo ---------- */
        img,
        svg {
            max-width: 100%;
        }

        /* Notebook pequeno / tablet deitado */
        @media (max-width: 1100px) {
            .topbar,
            .conteudo {
                padding-left: 20px;
                padding-right: 20px;
            }
        }

        /* Tablet */
        @media (max-width: 900px) {

            .topbar,
            .conteudo {
                padding-left: 16px;
                padding-right: 16px;
            }

            .col-criador {
                display: none;
            }

            table {
                min-width: 700px;
            }

            thead th,
            tbody td {
                padding-left: 14px;
                padding-right: 14px;
            }

            .campo-busca {
                max-width: none;
            }
        }

        /* Celular */
        @media (max-width: 720px) {
            .topbar {
                height: auto;
                min-height: 60px;
                padding-top: 10px;
                padding-bottom: 10px;
                flex-wrap: wrap;
            }

            .conteudo {
                padding-top: 16px;
                padding-bottom: 28px;
            }

            .cabecalho-pagina {
                flex-direction: column;
                align-items: stretch;
            }

            .cabecalho-pagina .btn {
                width: 100%;
                justify-content: center;
            }

            .ferramentas {
                flex-direction: column;
                align-items: stretch;
            }

            .ferramentas-direita {
                width: 100%;
            }

            .campo-busca {
                flex: 1 1 auto;
                width: 100%;
            }

            .filtro-area,
            .filtro-area select {
                width: 100%;
            }

            input,
            select,
            textarea {
                font-size: 16px;
            }

            /* Tabela vira lista de cartões */
            .tabela-wrap {
                overflow: visible;
            }

            table,
            tbody {
                display: block;
                width: 100%;
                min-width: 0;
            }

            thead {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
                white-space: nowrap;
            }

            tbody tr:not([hidden]) {
                display: block;
                padding: 8px 16px;
                border-bottom: 1px solid var(--borda);
            }

            tbody tr:last-child {
                border-bottom: none;
            }

            tbody tr:not([hidden]) td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 14px;
                padding: 9px 0;
                border: none;
                text-align: right;
                overflow-wrap: anywhere;
            }

            tbody tr:not([hidden]) td::before {
                flex-shrink: 0;
                font-size: 11px;
                font-weight: 600;
                letter-spacing: .6px;
                text-transform: uppercase;
                color: var(--text-muted);
                text-align: left;
            }

            tbody tr:not([hidden]) td:nth-child(1) {
                justify-content: flex-start;
                text-align: left;
            }

            tbody tr:not([hidden]) td:nth-child(2)::before { content: "Empresa"; }
            tbody tr:not([hidden]) td:nth-child(3)::before { content: "Área"; }
            tbody tr:not([hidden]) td:nth-child(4)::before { content: "Cadastrada em"; }

            tbody tr:not([hidden]) td.col-criador {
                display: flex;
            }

            tbody tr:not([hidden]) td.col-criador::before { content: "Cadastrada por"; }

            tbody tr:not([hidden]) td:last-child {
                justify-content: flex-end;
                padding-top: 12px;
                border-top: 1px dashed var(--borda);
                margin-top: 4px;
            }

            .vaga-sub {
                max-width: 100%;
            }

            tbody tr:not([hidden]) td.vazio {
                display: block;
                text-align: center;
            }

            .rodape-tabela {
                padding: 12px 16px;
            }

            .btn-icone {
                width: 42px;
                height: 42px;
            }

            /* Modais viram painel quase em tela cheia */
            dialog {
                width: calc(100vw - 20px);
                max-height: calc(100dvh - 20px);
                overflow-y: auto;
            }

            .modal-topo,
            .modal-corpo,
            .modal-rodape {
                padding-left: 16px;
                padding-right: 16px;
            }

            .modal-rodape {
                flex-wrap: wrap;
            }

            .modal-rodape .btn {
                flex: 1 1 120px;
                justify-content: center;
            }
        }

        @media (max-width: 600px) {
            .topbar-usuario span.nome {
                display: none;
            }
        }

        @media (max-width: 380px) {
            .topbar-marca {
                font-size: 17px;
                letter-spacing: 2px;
            }

            .cabecalho-pagina h1 {
                font-size: 19px;
            }
        }
    </style>
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <script src="../../assets/js/menu.js" defer></script>
</head>

<body>

    <header class="topbar topbar-vagas">
        <a class="topbar-marca" href="painel.php">JANUS</a>
        <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-vagas"><span></span><span></span><span></span></button>
        <nav class="menu-principal janus-nav" id="menu-vagas" aria-label="Navegação principal">
            <a href="painel.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span>Egressos</span></a>
            <a href="lista-vagas.php" class="ativo" aria-current="page"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/><path d="M10 12v2h4v-2"/></svg></span><span>Vagas</span></a>
            <a href="cadastro-vaga.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span><span>Nova vaga</span></a>
        </nav>
        <div class="topbar-espaco"></div>
    </header>

    <main class="conteudo">

        <div class="cabecalho-pagina">
            <div>
                <h1>Lista de Vagas</h1>
                <p>Gerencie todas as vagas cadastradas no Janus.</p>
            </div>
            <a class="btn" href="painel.php"><?php echo icone("voltar"); ?> Painel de egressos</a>
        </div>

        <?php if ($sucesso): ?>
            <div class="alerta alerta-sucesso"><?php echo e($sucesso); ?></div>
        <?php endif; ?>
        <?php if ($erroGeral): ?>
            <div class="alerta alerta-erro"><?php echo e($erroGeral); ?></div>
        <?php endif; ?>

        <section class="cartao">

            <div class="ferramentas">
                <div class="campo-busca">
                    <?php echo icone("busca"); ?>
                    <input type="search" id="busca" placeholder="Buscar por título, empresa ou área..." autocomplete="off">
                </div>
                <div class="ferramentas-direita">
                    <label class="filtro-area">
                        <?php echo icone("filtro"); ?>
                        <select id="filtro-area" aria-label="Filtrar por área">
                            <option value="">Todas as áreas</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?php echo e(mb_strtolower($a)); ?>"><?php echo e($a); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="button" class="btn" id="btn-exportar"><?php echo icone("baixar"); ?> Exportar</button>
                </div>
            </div>

            <div class="tabela-wrap">
                <table id="tabela-vagas">
                    <thead>
                        <tr>
                            <th>Vaga</th>
                            <th>Empresa</th>
                            <th>Área</th>
                            <th>Cadastrada em</th>
                            <th class="col-criador">Cadastrada por</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vagas as $v): ?>
                            <tr data-vaga="<?php echo e(json_encode($v, JSON_UNESCAPED_UNICODE)); ?>"
                                data-busca="<?php echo e(mb_strtolower(($v["titulo"] ?? "") . " " . ($v["empresa"] ?? "") . " " . ($v["area"] ?? ""))); ?>"
                                data-area="<?php echo e(mb_strtolower($v["area"] ?? "")); ?>">
                                <td>
                                    <div class="vaga-info">
                                        <span class="avatar"><?php echo e(inicial($v["empresa"] ?? "")); ?></span>
                                        <div>
                                            <div class="vaga-titulo"><?php echo e($v["titulo"] ?? ""); ?></div>
                                            <div class="vaga-sub"><?php echo e($v["descricao"] ?? ""); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="empresa-nome"><?php echo e($v["empresa"] ?? ""); ?></td>
                                <td><span class="pill"><?php echo e($v["area"] ?? ""); ?></span></td>
                                <td><?php echo e(dataBr($v["criado_em"] ?? null)); ?></td>
                                <td class="col-criador muted"><?php echo e($v["criado_por"] ?? "—"); ?></td>
                                <td>
                                    <div class="acoes">
                                        <button type="button" class="btn btn-icone js-ver" title="Ver detalhes" aria-label="Ver detalhes"><?php echo icone("olho"); ?></button>
                                        <button type="button" class="btn btn-icone js-editar" title="Editar" aria-label="Editar"><?php echo icone("lapis"); ?></button>
                                        <button type="button" class="btn btn-icone excluir js-excluir" title="Excluir" aria-label="Excluir"><?php echo icone("lixo"); ?></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="linha-vazia" <?php echo $totalVagas > 0 ? 'hidden' : ''; ?>>
                            <td colspan="6" class="vazio">
                                <span id="texto-vazio"><?php echo $totalVagas > 0 ? "Nenhuma vaga encontrada para esse filtro." : "Nenhuma vaga cadastrada ainda."; ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rodape-tabela">
                Exibindo <strong id="contador"><?php echo $totalVagas; ?></strong> de <?php echo $totalVagas; ?> vaga(s)
            </div>
        </section>
    </main>

    <!-- ===== Modal: ver detalhes ===== -->
    <dialog id="modal-ver">
        <div class="modal-topo">
            <h2>Detalhes da vaga</h2>
            <button type="button" class="btn btn-icone js-fechar" aria-label="Fechar"><?php echo icone("fechar"); ?></button>
        </div>
        <div class="modal-corpo">
            <dl>
                <div class="detalhe">
                    <dt>Título</dt>
                    <dd id="ver-titulo"></dd>
                </div>
                <div class="detalhe">
                    <dt>Empresa</dt>
                    <dd id="ver-empresa"></dd>
                </div>
                <div class="detalhe">
                    <dt>Área</dt>
                    <dd id="ver-area"></dd>
                </div>
                <div class="detalhe">
                    <dt>Descrição</dt>
                    <dd id="ver-descricao"></dd>
                </div>
                <div class="detalhe">
                    <dt>Cadastro</dt>
                    <dd id="ver-cadastro"></dd>
                </div>
            </dl>
        </div>
        <div class="modal-rodape">
            <button type="button" class="btn js-fechar">Fechar</button>
        </div>
    </dialog>

    <!-- ===== Modal: editar ===== -->
    <dialog id="modal-editar">
        <form action="../../controllers/admin/lista-vagas_processar.php" method="POST">
            <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>">
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="id" id="editar-id">

            <div class="modal-topo">
                <h2>Editar vaga</h2>
                <button type="button" class="btn btn-icone js-fechar" aria-label="Fechar"><?php echo icone("fechar"); ?></button>
            </div>
            <div class="modal-corpo">
                <div class="form-group">
                    <label for="editar-titulo">Título da vaga</label>
                    <input type="text" id="editar-titulo" name="titulo" required maxlength="100"
                        class="<?php echo isset($errosEdicao["titulo"]) ? "campo-invalido" : ""; ?>">
                    <?php erroEdicao($errosEdicao, "titulo"); ?>
                </div>
                <div class="form-group">
                    <label for="editar-empresa">Empresa</label>
                    <input type="text" id="editar-empresa" name="empresa" required maxlength="100"
                        class="<?php echo isset($errosEdicao["empresa"]) ? "campo-invalido" : ""; ?>">
                    <?php erroEdicao($errosEdicao, "empresa"); ?>
                </div>
                <div class="form-group">
                    <label for="editar-area">Área</label>
                    <input type="text" id="editar-area" name="area" required maxlength="100"
                        class="<?php echo isset($errosEdicao["area"]) ? "campo-invalido" : ""; ?>">
                    <?php erroEdicao($errosEdicao, "area"); ?>
                </div>
                <div class="form-group">
                    <label for="editar-descricao">Descrição</label>
                    <textarea id="editar-descricao" name="descricao" required maxlength="1000"
                        class="<?php echo isset($errosEdicao["descricao"]) ? "campo-invalido" : ""; ?>"></textarea>
                    <?php erroEdicao($errosEdicao, "descricao"); ?>
                </div>
            </div>
            <div class="modal-rodape">
                <button type="button" class="btn js-fechar">Cancelar</button>
                <button type="submit" class="btn btn-primario">Salvar alterações</button>
            </div>
        </form>
    </dialog>

    <!-- ===== Modal: confirmar exclusão ===== -->
    <dialog id="modal-excluir">
        <form action="../../controllers/admin/lista-vagas_processar.php" method="POST">
            <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>">
            <input type="hidden" name="acao" value="excluir">
            <input type="hidden" name="id" id="excluir-id">

            <div class="modal-topo">
                <h2>Excluir vaga</h2>
                <button type="button" class="btn btn-icone js-fechar" aria-label="Fechar"><?php echo icone("fechar"); ?></button>
            </div>
            <div class="modal-corpo">
                <p style="margin:0">Tem certeza que deseja excluir a vaga <strong id="excluir-titulo"></strong>? Essa ação não pode ser desfeita.</p>
            </div>
            <div class="modal-rodape">
                <button type="button" class="btn js-fechar">Cancelar</button>
                <button type="submit" class="btn btn-perigo">Excluir</button>
            </div>
        </form>
    </dialog>

    <script>
        (function() {
            var linhas = Array.prototype.slice.call(document.querySelectorAll("#tabela-vagas tbody tr[data-vaga]"));
            var busca = document.getElementById("busca");
            var filtro = document.getElementById("filtro-area");
            var linhaVazia = document.getElementById("linha-vazia");
            var textoVazio = document.getElementById("texto-vazio");
            var contador = document.getElementById("contador");
            var total = linhas.length;

            function dadosDaLinha(el) {
                return JSON.parse(el.closest("tr").getAttribute("data-vaga"));
            }

            function $(id) {
                return document.getElementById(id);
            }

            // ---------- Busca + filtro ----------
            function filtrar() {
                var termo = busca.value.trim().toLowerCase();
                var area = filtro.value;
                var visiveis = 0;
                linhas.forEach(function(tr) {
                    var ok = tr.dataset.busca.indexOf(termo) !== -1 && (area === "" || tr.dataset.area === area);
                    tr.hidden = !ok;
                    if (ok) visiveis++;
                });
                contador.textContent = visiveis;
                if (total > 0) {
                    linhaVazia.hidden = visiveis > 0;
                    textoVazio.textContent = "Nenhuma vaga encontrada para esse filtro.";
                }
            }
            busca.addEventListener("input", filtrar);
            filtro.addEventListener("change", filtrar);

            // ---------- Modais ----------
            document.querySelectorAll(".js-fechar").forEach(function(b) {
                b.addEventListener("click", function() {
                    b.closest("dialog").close();
                });
            });
            document.querySelectorAll("dialog").forEach(function(d) {
                // clicar fora do modal fecha
                d.addEventListener("click", function(ev) {
                    if (ev.target === d) d.close();
                });
            });

            function formatarData(s) {
                if (!s) return "—";
                var p = s.split(" ");
                var d = p[0].split("-");
                return d[2] + "/" + d[1] + "/" + d[0] + (p[1] ? " às " + p[1].slice(0, 5) : "");
            }

            document.querySelectorAll(".js-ver").forEach(function(b) {
                b.addEventListener("click", function() {
                    var v = dadosDaLinha(b);
                    $("ver-titulo").textContent = v.titulo || "";
                    $("ver-empresa").textContent = v.empresa || "";
                    $("ver-area").textContent = v.area || "";
                    $("ver-descricao").textContent = v.descricao || "";
                    $("ver-cadastro").textContent = formatarData(v.criado_em) + (v.criado_por ? " por " + v.criado_por : "");
                    $("modal-ver").showModal();
                });
            });

            function abrirEdicao(v) {
                $("editar-id").value = v.id || "";
                $("editar-titulo").value = v.titulo || "";
                $("editar-empresa").value = v.empresa || "";
                $("editar-area").value = v.area || "";
                $("editar-descricao").value = v.descricao || "";
                $("modal-editar").showModal();
            }
            document.querySelectorAll(".js-editar").forEach(function(b) {
                b.addEventListener("click", function() {
                    abrirEdicao(dadosDaLinha(b));
                });
            });

            document.querySelectorAll(".js-excluir").forEach(function(b) {
                b.addEventListener("click", function() {
                    var v = dadosDaLinha(b);
                    $("excluir-id").value = v.id || "";
                    $("excluir-titulo").textContent = "“" + (v.titulo || "") + "”";
                    $("modal-excluir").showModal();
                });
            });

            // ---------- Exportar CSV (linhas visíveis) ----------
            $("btn-exportar").addEventListener("click", function() {
                var cab = ["Título", "Empresa", "Área", "Descrição", "Cadastrada em", "Cadastrada por"];
                var linhasCsv = [cab];
                linhas.forEach(function(tr) {
                    if (tr.hidden) return;
                    var v = JSON.parse(tr.getAttribute("data-vaga"));
                    linhasCsv.push([v.titulo, v.empresa, v.area, v.descricao, v.criado_em, v.criado_por]);
                });
                var csv = linhasCsv.map(function(l) {
                    return l.map(function(c) {
                        return '"' + String(c == null ? "" : c).replace(/"/g, '""') + '"';
                    }).join(";");
                }).join("\r\n");
                var blob = new Blob(["\ufeff" + csv], {
                    type: "text/csv;charset=utf-8;"
                });
                var a = document.createElement("a");
                a.href = URL.createObjectURL(blob);
                a.download = "vagas.csv";
                document.body.appendChild(a);
                a.click();
                a.remove();
                URL.revokeObjectURL(a.href);
            });

            <?php if ($antigosEdit): ?>
                // Erro de validação na edição: reabre o modal com o que foi digitado
                abrirEdicao(<?php echo json_encode($antigosEdit, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>);
            <?php endif; ?>
        })();
    </script>

</body>

</html>