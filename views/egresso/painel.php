<?php

session_start();

// Bloqueia quem não está logado ou não é egresso
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "egresso") {
    header("Location: ../login.php");
    exit;
}

$nome         = $_SESSION["nome"] ?? "Egresso";
$email        = $_SESSION["email"];
$primeiroNome = explode(" ", trim($nome))[0];

// Escapa texto antes de imprimir no HTML
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

// Lê um arquivo JSON e sempre devolve um array
function carregarJson(string $caminho): array
{
    if (!is_file($caminho)) {
        return [];
    }

    $dados = json_decode((string) file_get_contents($caminho), true);

    return is_array($dados) ? $dados : [];
}

// Corta a descrição para caber no card
function resumir(string $texto, int $limite = 160): string
{
    return mb_strimwidth($texto, 0, $limite, "…", "UTF-8");
}

// Aceita requisitos como array ou texto (separado por linha ou ";")
function listaRequisitos(array $vaga): array
{
    $bruto = $vaga["requisitos"] ?? [];

    if (is_string($bruto)) {
        $bruto = preg_split("/[\r\n;]+/", $bruto);
    }

    if (!is_array($bruto)) {
        return [];
    }

    $bruto = array_map(fn($item) => trim((string) $item), $bruto);

    return array_values(array_filter($bruto, fn($item) => $item !== ""));
}

// Vagas (mais recentes primeiro)
$vagas = array_reverse(carregarJson(__DIR__ . "/../../data/vagas.json"));

$areas = [];
foreach ($vagas as $vaga) {
    $area = trim($vaga["area"] ?? "");
    if ($area !== "") {
        $areas[$area] = true;
    }
}
$areas = array_keys($areas);
sort($areas, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Egresso | Janus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #f2c94c;
            --gold-dark: #a77a14;
            --text-dark: #172033;
            --text-muted: #697586;
            --bg: #f4f6f8;
            --border: #dbe2e8;
            --teal-bg: #e3f3f0;   --teal-fg: #1f6f63;
            --info-bg: #e6eefb;   --info-fg: #274b8f;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-dark); min-height: 100vh; }
        .wrap { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

        /* Cabeçalho */
        .app-header { background: #fff; border-bottom: 1px solid var(--border); padding: 14px 0; }
        .app-header .wrap { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: 'Cinzel', serif; font-weight: 700; font-size: 22px; letter-spacing: 3px; color: var(--gold-dark); text-decoration: none; }
        .app-header-meta { font-size: 12px; color: var(--text-muted); margin: 2px 0 0; }
        .app-header-right { display: flex; align-items: center; gap: 18px; font-size: 14px; }
        .app-header-right a.link { color: var(--gold-dark); font-weight: 600; text-decoration: none; }
        .app-header-right a.link:hover { text-decoration: underline; }

        .app-main { padding: 32px 0 48px; }

        /* Cards */
        .app-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.04); }
        @media (max-width: 520px) { .app-card { padding: 20px 16px; } }

        /* Boas-vindas */
        .welcome h1 { font-size: 24px; line-height: 1.2; margin: 0; }
        .welcome p { color: var(--text-muted); font-size: 14px; margin: 4px 0 0; }
        .stat { text-align: center; padding: 14px 10px; }
        .stat span { display: block; font-size: 12px; color: var(--text-muted); }
        .stat strong { display: block; font-size: 24px; font-weight: 600; color: var(--gold-dark); }

        .section-label { font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--gold-dark); margin: 28px 0 12px; }

        /* Filtros */
        .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .field .form-control, .field .form-select {
            padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border);
            background-color: #f8fafc; font-size: 15px; font-family: 'Inter', sans-serif;
            box-shadow: none; transition: border-color 0.2s, background 0.2s;
        }
        .field .form-control:focus, .field .form-select:focus { border-color: var(--gold); background-color: #fff; box-shadow: none; }
        .contador { color: var(--text-muted); font-size: 13px; margin: 16px 0 12px; }

        /* Cards de vaga */
        .card-vaga { display: flex; flex-direction: column; height: 100%; }
        .card-vaga h3 { font-size: 17px; font-weight: 600; margin: 0 0 4px; }
        .card-vaga .empresa { color: var(--text-muted); font-size: 14px; margin: 0 0 10px; }
        .card-vaga .descricao { font-size: 14px; line-height: 1.6; margin: 12px 0; }
        .card-vaga h4 { font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--gold-dark); margin: 4px 0 6px; }
        .requisitos ul { font-size: 14px; padding-left: 18px; margin-bottom: 16px; }
        .requisitos li { margin-bottom: 2px; }
        .vazio { color: var(--text-muted); font-size: 14px; }

        .chip { display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 13px; font-weight: 500; }
        .chip-info { background: var(--info-bg); color: var(--info-fg); }
        .chip-teal { background: var(--teal-bg); color: var(--teal-fg); }

        /* Botões */
        .button { display: block; width: 100%; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; border: 1px solid transparent; text-align: center; text-decoration: none; }
        .button-primary { background: var(--gold); color: #fff; }
        .button-primary:hover { background: var(--gold-dark); color: #fff; }
        .button-secondary { background: #fff; color: var(--gold-dark); border-color: var(--gold); }
        .button-secondary:hover { background: #fff8df; color: var(--gold-dark); }
        .button-enviada, .button-enviada:disabled { background: var(--teal-bg); color: var(--teal-fg); border-color: var(--teal-bg); cursor: default; opacity: 1; }
        .btn-pequeno { display: inline-block; width: auto; padding: 10px 16px; font-size: 14px; }
        .btn-candidatar { margin-top: auto; }

        /* Estados vazios */
        .estado-vazio { text-align: center; color: var(--text-muted); font-size: 14px; }
        .estado-vazio strong { color: var(--text-dark); }

        .rodape { text-align: center; color: var(--text-muted); font-size: 13px; padding: 0 0 32px; }

        /* Aviso */
        .toast-janus { background: var(--teal-bg); color: var(--teal-fg); border: 1px solid var(--teal-fg); border-radius: 10px; font-size: 14px; font-weight: 500; }
        [hidden] { display: none !important; }

        /* ===== Responsivo ===== */
        img, svg { max-width: 100%; height: auto; }
        .card-vaga h3, .card-vaga .empresa, .card-vaga .descricao, .requisitos li { overflow-wrap: anywhere; }

        @media (max-width: 900px) {
            .wrap { padding: 0 16px; }
            .app-main { padding: 24px 0 36px; }
            .welcome h1 { font-size: 22px; }
        }

        @media (max-width: 600px) {
            .app-header .wrap { flex-wrap: wrap; gap: 10px; }
            .app-header-right { gap: 12px; margin-left: auto; }
            .logo { font-size: 20px; letter-spacing: 2px; }
            .app-main { padding: 18px 0 28px; }
            .welcome h1 { font-size: 20px; }
            .section-label { margin: 22px 0 10px; }
            .field .form-control, .field .form-select { font-size: 16px; }
            .button { padding: 14px; }
            .toast-janus { max-width: calc(100vw - 24px); }
        }

        @media (max-width: 380px) {
            .app-header .wrap { justify-content: center; text-align: center; }
            .app-header-right { margin: 0 auto; }
            .stat strong { font-size: 20px; }
        }
    </style>
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <script src="../../assets/js/menu.js" defer></script>
</head>
<body data-email="<?php echo e($email); ?>">

<header class="app-header">
    <div class="wrap">
        <div>
            <a class="logo" href="painel.php">JANUS</a>
        </div>
        <nav class="app-header-right janus-nav egresso-nav" id="menu-egresso" aria-label="Navegação principal">
            <a href="painel.php" class="link ativo"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-7h6v7"/></svg></span><span>Início</span></a>
            <a href="perfil.php" class="button button-secondary btn-pequeno"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span><span>Meu perfil</span></a>
            <a href="../sobre.php" class="link"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg></span><span>Sobre o Janus</span></a>
            <a href="../../controllers/logout.php" class="link sair-link"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/></svg></span><span>Sair</span></a>
        </nav>
        <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-egresso"><span></span><span></span><span></span></button>
    </div>
</header>

<main class="app-main">
    <div class="wrap">

        <section class="row align-items-center g-3" aria-labelledby="titulo-boas-vindas">
            <div class="col-md-7 welcome">
                <h1 id="titulo-boas-vindas">Olá, <?php echo e($primeiroNome); ?>!</h1>
                <p>Bem-vindo(a), <?php echo e($nome); ?>. Encontre oportunidades e candidate-se às vagas disponíveis.</p>
            </div>
            <div class="col-md-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="app-card stat">
                            <span>Vagas abertas</span>
                            <strong><?php echo count($vagas); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="app-card stat">
                            <span>Candidaturas</span>
                            <strong id="total-candidaturas">0</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section aria-labelledby="titulo-vagas">
            <p class="section-label" id="titulo-vagas">Vagas disponíveis</p>

            <?php if (empty($vagas)): ?>
                <div class="app-card estado-vazio">
                    <p class="mb-1"><strong>Nenhuma vaga cadastrada ainda.</strong></p>
                    <p class="mb-0">Assim que uma empresa publicar uma oportunidade, ela aparece aqui.</p>
                </div>
            <?php else: ?>

                <div class="app-card">
                    <div class="row g-3">
                        <div class="col-md-8 field">
                            <label for="filtro-busca">Buscar vaga</label>
                            <input type="search" id="filtro-busca" class="form-control" placeholder="Título, empresa ou palavra-chave">
                        </div>
                        <div class="col-md-4 field">
                            <label for="filtro-area">Área</label>
                            <select id="filtro-area" class="form-select">
                                <option value="">Todas as áreas</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo e(mb_strtolower($area)); ?>"><?php echo e($area); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <p class="contador" id="contador-vagas" aria-live="polite"></p>

                <div class="row g-3" id="grade-vagas">
                    <?php foreach ($vagas as $vaga):
                        $titulo     = trim($vaga["titulo"] ?? "Vaga sem título");
                        $empresa    = trim($vaga["empresa"] ?? "Empresa não informada");
                        $area       = trim($vaga["area"] ?? "");
                        $descricao  = trim($vaga["descricao"] ?? "");
                        $requisitos = listaRequisitos($vaga);
                        $id         = (string) ($vaga["id"] ?? md5($titulo . $empresa));
                        $busca      = mb_strtolower($titulo . " " . $empresa . " " . $area . " " . $descricao . " " . implode(" ", $requisitos));
                    ?>
                        <div class="col-md-6 col-lg-4 item-vaga"
                             data-id="<?php echo e($id); ?>"
                             data-area="<?php echo e(mb_strtolower($area)); ?>"
                             data-busca="<?php echo e($busca); ?>">

                            <article class="app-card card-vaga">
                                <h3><?php echo e($titulo); ?></h3>
                                <p class="empresa"><?php echo e($empresa); ?></p>

                                <?php if ($area !== ""): ?>
                                    <div><span class="chip chip-info"><?php echo e($area); ?></span></div>
                                <?php endif; ?>

                                <p class="descricao">
                                    <?php echo $descricao !== "" ? e(resumir($descricao)) : '<span class="vazio">Sem descrição.</span>'; ?>
                                </p>

                                <div class="requisitos">
                                    <h4>Requisitos</h4>
                                    <?php if (!empty($requisitos)): ?>
                                        <ul>
                                            <?php foreach ($requisitos as $requisito): ?>
                                                <li><?php echo e($requisito); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="vazio">Não informados pela empresa.</p>
                                    <?php endif; ?>
                                </div>

                                <button type="button" class="button button-primary btn-candidatar">Candidatar-se</button>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="app-card estado-vazio mt-3" id="sem-resultados" hidden>
                    <p class="mb-1"><strong>Nenhuma vaga encontrada.</strong></p>
                    <p class="mb-0">Tente outra palavra ou escolha "Todas as áreas".</p>
                </div>

            <?php endif; ?>
        </section>

    </div>
</main>

<footer class="rodape">
    Janus — Banco de Talentos de Egressos
</footer>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toast" class="toast toast-janus" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-body" id="toast-texto"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var email = document.body.dataset.email;
    var chave = "janus_candidaturas_" + email;
    var itens = Array.prototype.slice.call(document.querySelectorAll(".item-vaga"));
    var busca = document.getElementById("filtro-busca");
    var area = document.getElementById("filtro-area");
    var contador = document.getElementById("contador-vagas");
    var semResultados = document.getElementById("sem-resultados");
    var total = document.getElementById("total-candidaturas");
    var toastEl = document.getElementById("toast");
    var toastTexto = document.getElementById("toast-texto");

    function lerCandidaturas() {
        try {
            var dados = JSON.parse(localStorage.getItem(chave));
            return Array.isArray(dados) ? dados : [];
        } catch (erro) {
            return [];
        }
    }

    function salvarCandidaturas(lista) {
        try {
            localStorage.setItem(chave, JSON.stringify(lista));
        } catch (erro) {}
    }

    function marcarEnviada(botao) {
        botao.textContent = "Candidatura enviada";
        botao.classList.remove("button-primary");
        botao.classList.add("button-enviada");
        botao.disabled = true;
    }

    function mostrarToast(texto) {
        toastTexto.textContent = texto;
        bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2500 }).show();
    }

    function filtrar() {
        if (!busca) return;
        var termo = busca.value.trim().toLowerCase();
        var areaSel = area.value;
        var visiveis = 0;

        itens.forEach(function (item) {
            var ok = (!termo || item.dataset.busca.indexOf(termo) !== -1) &&
                     (!areaSel || item.dataset.area === areaSel);
            item.hidden = !ok;
            if (ok) visiveis++;
        });

        contador.textContent = visiveis + " de " + itens.length + " vaga(s)";
        semResultados.hidden = visiveis !== 0;
    }

    var enviadas = lerCandidaturas();
    total.textContent = enviadas.length;

    itens.forEach(function (item) {
        var botao = item.querySelector(".btn-candidatar");
        if (enviadas.indexOf(item.dataset.id) !== -1) {
            marcarEnviada(botao);
        }

        botao.addEventListener("click", function () {
            var lista = lerCandidaturas();
            if (lista.indexOf(item.dataset.id) === -1) {
                lista.push(item.dataset.id);
                salvarCandidaturas(lista);
            }
            total.textContent = lista.length;
            marcarEnviada(botao);
            mostrarToast("Candidatura enviada com sucesso!");
        });
    });

    if (busca) {
        busca.addEventListener("input", filtrar);
        area.addEventListener("change", filtrar);
        filtrar();
    }
})();
</script>
</body>
</html>