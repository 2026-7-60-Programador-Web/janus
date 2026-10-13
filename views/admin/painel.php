<?php

session_start();

// Bloqueia quem não está logado ou não é administrador
if (!isset($_SESSION["email"]) || $_SESSION["tipo"] !== "administrador") {
    header("Location: ../login.php");
    exit;
}

require __DIR__ . '/../../controllers/admin/painel_processar.php';

// --- Parâmetros vindos da URL ---
$areaSelecionada = $_GET["area"] ?? "todas";
$buscaNome       = trim($_GET["busca"] ?? "");
$campoOrdenacao  = $_GET["campo"] ?? "nome";
$direcaoAtual    = ($_GET["direcao"] ?? "asc") === "desc" ? "desc" : "asc";
$paginaAtual     = (int) ($_GET["pagina"] ?? 1);
$itensPorPagina  = 10;

// --- Processamento: filtra, ordena e pagina ---
$egressosFiltrados = filtrarEgressos($egressosCadastrados, $areaSelecionada, $buscaNome);
$egressosFiltrados = ordenarEgressos($egressosFiltrados, $campoOrdenacao, $direcaoAtual);
$paginacao          = paginarEgressos($egressosFiltrados, $paginaAtual, $itensPorPagina);

$areasDisponiveis = array_unique(array_column($egressosCadastrados, "area"));
sort($areasDisponiveis);

// Monta a query string preservando os filtros atuais, trocando apenas os campos passados em $sobrescreve
function montarLink(array $sobrescreve): string
{
    $params = array_merge($_GET, $sobrescreve);
    return "?" . http_build_query($params);
}

// Alterna asc/desc quando o clique é na mesma coluna já ordenada
function linkOrdenacao(string $campo, string $campoAtual, string $direcaoAtual): string
{
    $novaDirecao = ($campo === $campoAtual && $direcaoAtual === "asc") ? "desc" : "asc";
    return montarLink(["campo" => $campo, "direcao" => $novaDirecao, "pagina" => 1]);
}

function setaOrdenacao(string $campo, string $campoAtual, string $direcaoAtual): string
{
    if ($campo !== $campoAtual) {
        return "⇅";
    }
    return $direcaoAtual === "asc" ? "↑" : "↓";
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Egressos</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../../assets/css/painel.css" rel="stylesheet">
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <script src="../../assets/js/menu.js" defer></script>
</head>
<body>

<header class="topbar">
    <div class="topbar-esq">
        <a class="marca" href="painel.php">JANUS</a>
        <nav class="menu janus-nav" id="menu-admin" aria-label="Navegação principal">
            <a href="painel.php" class="ativo"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span>Egressos</span></a>
            <a href="lista-vagas.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/><path d="M10 12v2h4v-2"/></svg></span><span>Vagas</span></a>
            <a href="cadastro-vaga.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span><span>Nova vaga</span></a>
        </nav>
    </div>
    <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-admin"><span></span><span></span><span></span></button>
</header>

<main>

    <div class="cabecalho-painel">
        <h1>Painel de Egressos</h1>
    </div>

    <form method="GET" class="filtros">
        <select name="area" onchange="this.form.submit()">
            <option value="todas" <?php echo $areaSelecionada === "todas" ? "selected" : ""; ?>>Todas as áreas</option>
            <?php foreach ($areasDisponiveis as $area): ?>
                <option value="<?php echo htmlspecialchars($area); ?>" <?php echo $areaSelecionada === $area ? "selected" : ""; ?>>
                    <?php echo htmlspecialchars($area); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="busca" placeholder="Buscar por nome" value="<?php echo htmlspecialchars($buscaNome); ?>">

        <button type="submit">Filtrar</button>
    </form>

    <div class="tabela-wrap">

        <?php if (empty($paginacao["itens"])): ?>

            <p class="vazio">Nenhum egresso encontrado para esse filtro.</p>

        <?php else: ?>

            <table>
                <thead>
                    <tr>
                        <th><a href="<?php echo linkOrdenacao('nome', $campoOrdenacao, $direcaoAtual); ?>">Egresso <?php echo setaOrdenacao('nome', $campoOrdenacao, $direcaoAtual); ?></a></th>
                        <th><a href="<?php echo linkOrdenacao('area', $campoOrdenacao, $direcaoAtual); ?>">Área <?php echo setaOrdenacao('area', $campoOrdenacao, $direcaoAtual); ?></a></th>
                        <th><a href="<?php echo linkOrdenacao('curso', $campoOrdenacao, $direcaoAtual); ?>">Curso <?php echo setaOrdenacao('curso', $campoOrdenacao, $direcaoAtual); ?></a></th>
                        <th><a href="<?php echo linkOrdenacao('ano_formatura', $campoOrdenacao, $direcaoAtual); ?>">Formatura <?php echo setaOrdenacao('ano_formatura', $campoOrdenacao, $direcaoAtual); ?></a></th>
                        <th>E-mail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginacao["itens"] as $egresso): ?>
                        <tr>
                            <td>
                                <div class="col-nome">
                                    <div class="avatar" style="background: <?php echo corAvatar($egresso['nome']); ?>">
                                        <?php echo htmlspecialchars(iniciaisNome($egresso['nome'])); ?>
                                    </div>
                                    <a href="ver-egresso.php?email=<?php echo urlencode($egresso['email']); ?>" style="color: var(--text); text-decoration: none; font-weight: 500;"><?php echo htmlspecialchars($egresso["nome"]); ?></a>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($egresso["area"]); ?></td>
                            <td><?php echo htmlspecialchars($egresso["curso"]); ?></td>
                            <td><?php echo htmlspecialchars((string) ($egresso["ano_formatura"] ?: "—")); ?></td>
                            <td><a class="email-link" href="mailto:<?php echo htmlspecialchars($egresso['email']); ?>"><?php echo htmlspecialchars($egresso["email"]); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="rodape-tabela">
                <span><?php echo $paginacao["inicio"]; ?> a <?php echo $paginacao["fim"]; ?> de <?php echo $paginacao["total"]; ?> egressos</span>

                <div class="paginacao">
                    <a class="<?php echo $paginacao['pagina_atual'] <= 1 ? 'desabilitada' : ''; ?>" href="<?php echo montarLink(['pagina' => $paginacao['pagina_atual'] - 1]); ?>">‹</a>

                    <?php for ($p = 1; $p <= $paginacao["total_paginas"]; $p++): ?>
                        <a class="<?php echo $p === $paginacao['pagina_atual'] ? 'ativa' : ''; ?>" href="<?php echo montarLink(['pagina' => $p]); ?>"><?php echo $p; ?></a>
                    <?php endfor; ?>

                    <a class="<?php echo $paginacao['pagina_atual'] >= $paginacao['total_paginas'] ? 'desabilitada' : ''; ?>" href="<?php echo montarLink(['pagina' => $paginacao['pagina_atual'] + 1]); ?>">›</a>
                </div>
            </div>

        <?php endif; ?>

    </div>

</main>

</body>
</html>