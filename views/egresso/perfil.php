<?php

session_start();

// Só egresso logado acessa o próprio perfil
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") === "administrador") {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . "/../../core/egresso_repositorio.php";

// ---- Dados do egresso (banco de dados) ----
$egresso    = null;
$erroBanco  = null;

try {
    $egresso = egressoBuscarPorEmail($_SESSION["email"]);
} catch (PDOException $e) {
    $erroBanco = "Não foi possível carregar o perfil agora. Detalhe do banco: " . $e->getMessage();
}

if ($egresso !== null) {
    $egresso["area"] = $egresso["area_atuacao"];
    $egresso["bio"]  = (string) ($egresso["bio"] ?? "");
}

// ---- Mensagens e dados devolvidos pelo controller ----
$sucesso      = $_SESSION["perfil_sucesso"] ?? null;
$errosPerfil  = $_SESSION["perfil_erros"] ?? [];
$antigos      = $_SESSION["perfil_antigos"] ?? null;
unset($_SESSION["perfil_sucesso"], $_SESSION["perfil_erros"], $_SESSION["perfil_antigos"]);

// Valores do formulário de edição: o que o banco tem, ou o que a pessoa digitou se deu erro
$form = [
    "email"     => $egresso["email"] ?? "",
    "telefone"  => $egresso["telefone"] ?? "",
    "bio"       => $egresso["bio"] ?? "",
    "soft"      => $egresso["soft_skills"] ?? [],
    "hard"      => $egresso["hard_skills"] ?? [],
    "formacoes" => $egresso["formacoes"] ?? [],
];
if (is_array($antigos)) {
    $form = array_merge($form, $antigos);
}
$abrirEdicao = is_array($antigos) && $egresso !== null;

$estadoJs = json_encode(
    ["soft" => array_values($form["soft"]), "hard" => array_values($form["hard"]), "formacoes" => array_values($form["formacoes"])],
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);

// ---- Helpers ----
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, "UTF-8");
}

function formatarTelefone(string $telefone): string
{
    $d = preg_replace("/\D/", "", $telefone);
    if (strlen($d) === 11) {
        return sprintf("(%s) %s-%s", substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
    }
    if (strlen($d) === 10) {
        return sprintf("(%s) %s-%s", substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
    }
    return $telefone;
}

function iniciais(string $nome): string
{
    $partes = preg_split("/\s+/", trim($nome));
    $ini = mb_substr($partes[0] ?? "", 0, 1);
    if (count($partes) > 1) {
        $ini .= mb_substr(end($partes), 0, 1);
    }
    return mb_strtoupper($ini);
}

$nomeExibicao = $egresso["nome"] ?? ($_SESSION["nome"] ?? "Egresso");

$formacaoPrincipal = $egresso["formacoes"][0] ?? null;
$subtitulo = $formacaoPrincipal
    ? $formacaoPrincipal["curso"] . " — " . $formacaoPrincipal["instituicao"]
    : (($egresso["curso"] ?? "") ?: "Formação não informada");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil | Janus</title>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-dark); min-height: 100vh; }
        .wrap { max-width: 760px; margin: 0 auto; padding: 0 20px; }

        /* Cabeçalho */
        .app-header { background: #fff; border-bottom: 1px solid var(--border); padding: 14px 0; }
        .app-header .wrap { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: 'Cinzel', serif; font-weight: 700; font-size: 22px; letter-spacing: 3px; color: var(--gold-dark); text-decoration: none; }
        .app-header-meta { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
        .app-header-right { display: flex; gap: 18px; font-size: 14px; }
        .app-header-right a { color: var(--gold-dark); font-weight: 600; text-decoration: none; }
        .app-header-right a:hover { text-decoration: underline; }

        /* Card */
        .app-main { padding: 32px 0 48px; }
        .app-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 32px; box-shadow: 0 4px 18px rgba(0,0,0,0.04); }
        @media (max-width: 520px) { .app-card { padding: 22px 18px; } }

        /* Topo do perfil */
        .profile-head { display: flex; align-items: center; gap: 18px; margin-bottom: 20px; }
        .avatar { width: 72px; height: 72px; border-radius: 50%; background: var(--gold); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 24px; flex-shrink: 0; }
        .profile-head h1 { font-size: 24px; line-height: 1.2; }
        .profile-head p { color: var(--text-muted); font-size: 14px; margin-top: 4px; }
        .profile-contact { display: flex; flex-wrap: wrap; gap: 8px 24px; font-size: 14px; margin-bottom: 8px; }
        .profile-contact span { color: var(--text-muted); }
        .profile-contact strong { color: var(--text-dark); font-weight: 500; }

        .section-label { font-size: 12px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: var(--gold-dark); margin: 24px 0 8px; }
        .profile-bio { font-size: 15px; line-height: 1.6; }
        .skills-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
        @media (max-width: 520px) { .skills-columns { grid-template-columns: 1fr; } }

        .chip-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { padding: 5px 12px; border-radius: 999px; font-size: 13px; font-weight: 500; }
        .chip-teal { background: var(--teal-bg); color: var(--teal-fg); }
        .chip-info { background: var(--info-bg); color: var(--info-fg); }
        .vazio { color: var(--text-muted); font-size: 14px; }

        .formacoes-view { list-style: none; }
        .formacoes-view li { padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
        .formacoes-view li:last-child { border-bottom: none; }
        .formacoes-view small { color: var(--text-muted); display: block; margin-top: 2px; }

        /* Botões */
        .button { display: block; width: 100%; padding: 13px; border-radius: 8px; font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; border: 1px solid transparent; }
        .button-primary { background: var(--gold); color: #fff; }
        .button-primary:hover { background: var(--gold-dark); }
        .button-secondary { background: #fff; color: var(--gold-dark); border-color: var(--gold); }
        .button-secondary:hover { background: #fff8df; }
        .btn-pequeno { display: inline-block; width: auto; padding: 10px 16px; font-size: 14px; }

        /* Formulário de edição */
        .field { margin-bottom: 18px; }
        .field label, .field .field-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .field input[type="text"], .field input[type="email"], .field input[type="tel"], .field textarea {
            width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border);
            background: #f8fafc; font-size: 15px; font-family: 'Inter', sans-serif; outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .field textarea { resize: vertical; }
        .field input:focus, .field textarea:focus { border-color: var(--gold); background: #fff; }
        .field-inline { display: flex; gap: 10px; margin-bottom: 10px; }
        .formacao-inputs { display: grid; grid-template-columns: 2fr 2fr 1fr; gap: 10px; margin-bottom: 10px; }
        @media (max-width: 520px) { .formacao-inputs { grid-template-columns: 1fr; } }
        .profile-photo-upload { display: flex; align-items: center; gap: 16px; }
        .upload-button { display: inline-block; padding: 10px 16px; border: 1px dashed var(--gold); border-radius: 8px; color: var(--gold-dark); font-size: 14px; font-weight: 600; cursor: pointer; }
        .upload-button input { display: none; }
        .formacao-list { list-style: none; margin-top: 10px; font-size: 14px; }
        .formacao-list li { padding: 8px 0; border-bottom: 1px solid var(--border); }
        /* Alertas e botões de remover */
        .alerta { padding: 12px 14px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
        .alerta ul { margin: 0; padding-left: 18px; }
        .alerta-sucesso { background: #e3f3f0; color: #1f6f63; border: 1px solid #b9e0d9; }
        .alerta-erro { background: #fdecea; color: #9b2c2c; border: 1px solid #f5c6c2; }
        .chip-remover { border: none; background: transparent; color: inherit; cursor: pointer; font-size: 15px; line-height: 1; padding: 0 0 0 4px; font-family: inherit; }
        .formacao-list li { display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .formacao-remover { border: none; background: transparent; color: #9b2c2c; cursor: pointer; font-size: 13px; font-weight: 600; font-family: inherit; }
        [hidden] { display: none !important; }

        /* ===== Responsivo ===== */
        img, svg { max-width: 100%; height: auto; }
        .profile-head h1, .profile-head p, .profile-contact strong, .profile-bio, .formacoes-view li { overflow-wrap: anywhere; }

        @media (max-width: 900px) {
            .wrap { padding: 0 16px; }
            .app-main { padding: 24px 0 36px; }
        }

        @media (max-width: 600px) {
            .app-header .wrap { flex-wrap: wrap; gap: 10px; }
            .app-header-right { gap: 14px; margin-left: auto; }
            .logo { font-size: 20px; letter-spacing: 2px; }
            .app-main { padding: 18px 0 28px; }
            .profile-head { flex-direction: column; text-align: center; }
            .profile-head h1 { font-size: 21px; }
            .avatar { width: 64px; height: 64px; font-size: 21px; }
            .profile-contact { flex-direction: column; gap: 6px; }
            .field input[type="text"], .field input[type="email"], .field input[type="tel"], .field textarea { font-size: 16px; }
            .field-inline { flex-wrap: wrap; }
            .field-inline > input { flex: 1 1 100%; min-width: 0; }
            .profile-photo-upload { flex-direction: column; align-items: flex-start; }
            .button { padding: 14px; }
            .btn-pequeno { width: 100%; text-align: center; }
        }

        @media (max-width: 380px) {
            .app-header .wrap { justify-content: center; text-align: center; }
            .app-header-right { margin: 0 auto; }
        }
    </style>
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <script src="../../assets/js/menu.js" defer></script>
</head>
<body>

<header class="app-header">
    <div class="wrap">
        <div>
            <a class="logo" href="painel.php">JANUS</a>
        </div>
        <nav class="app-header-right janus-nav egresso-nav" id="menu-egresso" aria-label="Navegação principal">
            <a href="painel.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-7h6v7"/></svg></span><span>Início</span></a>
            <a href="perfil.php" class="ativo"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span><span>Meu perfil</span></a>
            <a href="../sobre.php"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/></svg></span><span>Sobre o Janus</span></a>
            <a href="../../controllers/logout.php" class="sair-link"><span class="nav-icone" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/></svg></span><span>Sair</span></a>
        </nav>
        <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-egresso"><span></span><span></span><span></span></button>
    </div>
</header>

<main class="app-main">
    <div class="wrap">
        <div class="app-card">

            <?php if ($sucesso): ?>
                <div class="alerta alerta-sucesso"><?php echo e($sucesso); ?></div>
            <?php endif; ?>

            <?php if (!empty($errosPerfil)): ?>
                <div class="alerta alerta-erro">
                    <ul>
                        <?php foreach ($errosPerfil as $erro): ?>
                            <li><?php echo e($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($egresso === null): ?>
                <div class="profile-head">
                    <span class="avatar"><?php echo e(iniciais($nomeExibicao)); ?></span>
                    <div>
                        <h1><?php echo e($nomeExibicao); ?></h1>
                        <p><?php echo e($_SESSION["email"]); ?></p>
                    </div>
                </div>
                <p class="vazio">
                    <?php echo $erroBanco !== null
                        ? e($erroBanco)
                        : "Este é um usuário de teste e não tem perfil salvo no banco. Cadastre-se como egresso para ter um perfil editável."; ?>
                </p>
            <?php else: ?>

            <!-- ===== MODO VISUALIZAÇÃO ===== -->
            <div id="perfil-visualizacao">
                <div class="profile-head">
                    <span class="avatar"><?php echo e(iniciais($egresso["nome"])); ?></span>
                    <div>
                        <h1><?php echo e($egresso["nome"]); ?></h1>
                        <p><?php echo e($subtitulo); ?></p>
                    </div>
                </div>

                <div class="profile-contact">
                    <div><span>E-mail:</span> <strong><?php echo e($egresso["email"]); ?></strong></div>
                    <div><span>Telefone:</span> <strong><?php echo $egresso["telefone"] !== "" ? e(formatarTelefone($egresso["telefone"])) : "Não informado"; ?></strong></div>
                    <?php if ($egresso["area"] !== ""): ?>
                        <div><span>Área de atuação:</span> <strong><?php echo e($egresso["area"]); ?></strong></div>
                    <?php endif; ?>
                </div>

                <p class="section-label">Sobre</p>
                <p class="profile-bio"><?php echo $egresso["bio"] !== "" ? e($egresso["bio"]) : '<span class="vazio">Nenhuma descrição adicionada ainda.</span>'; ?></p>

                <div class="skills-columns">
                    <div>
                        <p class="section-label">Soft skills</p>
                        <div class="chip-list">
                            <?php foreach ($egresso["soft_skills"] as $skill): ?>
                                <span class="chip chip-teal"><?php echo e($skill); ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($egresso["soft_skills"])): ?><span class="vazio">Nenhuma informada.</span><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <p class="section-label">Hard skills</p>
                        <div class="chip-list">
                            <?php foreach ($egresso["hard_skills"] as $skill): ?>
                                <span class="chip chip-info"><?php echo e($skill); ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($egresso["hard_skills"])): ?><span class="vazio">Nenhuma informada.</span><?php endif; ?>
                        </div>
                    </div>
                </div>

                <p class="section-label">Formação</p>
                <ul class="formacoes-view">
                    <?php foreach ($egresso["formacoes"] as $f): ?>
                        <li>
                            <strong><?php echo e($f["curso"]); ?></strong>
                            <small><?php echo e($f["instituicao"]); ?><?php echo !empty($f["ano"]) ? " · " . e($f["ano"]) : ""; ?></small>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($egresso["formacoes"])): ?><li class="vazio">Nenhuma formação informada.</li><?php endif; ?>
                </ul>

                <button type="button" class="button button-primary" id="editar-perfil-btn" style="margin-top: 24px;">Editar meu perfil</button>
            </div>

            <!-- ===== MODO EDIÇÃO ===== -->
            <form id="perfil-edicao" action="../../controllers/egresso/perfil_processar.php" method="POST" <?php echo $abrirEdicao ? "" : "hidden"; ?> novalidate>
                <div class="field profile-photo-upload">
                    <span class="avatar"><?php echo e(iniciais($nomeExibicao)); ?></span>
                    <label class="upload-button">
                        <input type="file" accept="image/*">
                        <span>Escolher foto (opcional)</span>
                    </label>
                </div>

                <div class="field">
                    <label for="perfil-email-input">E-mail</label>
                    <input type="email" id="perfil-email-input" name="email" required value="<?php echo e($form["email"]); ?>" placeholder="seu.nome@exemplo.com">
                </div>

                <div class="field">
                    <label for="perfil-telefone-input">Telefone (com DDD)</label>
                    <input type="tel" id="perfil-telefone-input" name="telefone" required value="<?php echo e(formatarTelefone((string) $form["telefone"])); ?>" placeholder="(99) 99999-9999">
                </div>

                <div class="field">
                    <label for="perfil-bio-input">Sobre você</label>
                    <textarea id="perfil-bio-input" name="bio" rows="4" maxlength="1000" required placeholder="Conte um pouco sobre sua trajetória e o que procura"><?php echo e($form["bio"]); ?></textarea>
                </div>

                <div class="field">
                    <label for="perfil-soft-input">Soft skills</label>
                    <div class="field-inline">
                        <input type="text" id="perfil-soft-input" maxlength="60" placeholder="Ex: Organização">
                        <button type="button" class="button button-secondary btn-pequeno" id="add-soft-btn">Adicionar</button>
                    </div>
                    <div class="chip-list" id="lista-soft"></div>
                </div>

                <div class="field">
                    <label for="perfil-hard-input">Hard skills</label>
                    <div class="field-inline">
                        <input type="text" id="perfil-hard-input" maxlength="60" placeholder="Ex: JavaScript">
                        <button type="button" class="button button-secondary btn-pequeno" id="add-hard-btn">Adicionar</button>
                    </div>
                    <div class="chip-list" id="lista-hard"></div>
                </div>

                <div class="field">
                    <span class="field-label">Formação</span>
                    <div class="formacao-inputs">
                        <input type="text" id="formacao-curso" maxlength="120" placeholder="Curso">
                        <input type="text" id="formacao-instituicao" maxlength="120" placeholder="Instituição">
                        <input type="text" id="formacao-ano" maxlength="4" inputmode="numeric" placeholder="Ano">
                    </div>
                    <button type="button" class="button button-secondary btn-pequeno" id="add-formacao-btn">Adicionar formação</button>
                    <ul class="formacao-list" id="lista-formacoes"></ul>
                </div>

                <!-- O JavaScript preenche estes campos escondidos na hora de salvar -->
                <div id="campos-ocultos"></div>

                <button type="submit" class="button button-primary">Salvar perfil</button>
                <button type="button" class="button button-secondary" id="cancelar-edicao-btn" style="margin-top: 10px;">Cancelar</button>
            </form>

            <?php endif; ?>

        </div>
    </div>
</main>

<?php if ($egresso !== null): ?>
<script src="../../assets/js/validacao-form.js"></script>
<script>
    const visualizacao = document.getElementById("perfil-visualizacao");
    const edicao = document.getElementById("perfil-edicao");

    // Listas que o formulário envia: soft skills, hard skills e formações
    const estado = <?php echo $estadoJs; ?>;

    // Quais listas são obrigatórias (true = precisa ter pelo menos 1 item). Mude para false para deixar opcional.
    const OBRIGATORIOS = { soft: true, hard: true, formacao: true };
    const NOMES_SKILL = { soft: "soft skill", hard: "hard skill" };
    const ANO_MAXIMO = new Date().getFullYear() + 6;

    // ---- Alternar entre visualizar e editar ----
    document.getElementById("editar-perfil-btn").addEventListener("click", () => {
        visualizacao.hidden = true;
        edicao.hidden = false;
    });
    // Cancelar recarrega a página: descarta o que não foi salvo
    document.getElementById("cancelar-edicao-btn").addEventListener("click", () => {
        window.location.href = "perfil.php";
    });
    <?php if ($abrirEdicao): ?>
    visualizacao.hidden = true;
    <?php endif; ?>

    // ---- Avisos de erro nos campos que não são validados pelo validacao-form.js ----
    function limparErroLista(campo) {
        campo.classList.remove("campo-invalido");
        document.querySelectorAll('.erro-campo[data-para="' + campo.id + '"]').forEach((aviso) => aviso.remove());
    }

    function mostrarErroLista(campo, mensagem) {
        limparErroLista(campo);
        campo.classList.add("campo-invalido");

        const aviso = document.createElement("span");
        aviso.className = "erro-campo";
        aviso.dataset.para = campo.id;
        aviso.textContent = mensagem;
        campo.closest(".field").appendChild(aviso);
    }

    // ---- Skills ----
    function desenharSkills(tipo) {
        const lista = document.getElementById("lista-" + tipo);
        lista.innerHTML = "";

        estado[tipo].forEach((nome, i) => {
            const chip = document.createElement("span");
            chip.className = "chip " + (tipo === "soft" ? "chip-teal" : "chip-info");
            chip.textContent = nome;

            const remover = document.createElement("button");
            remover.type = "button";
            remover.className = "chip-remover";
            remover.textContent = "×";
            remover.setAttribute("aria-label", "Remover " + nome);
            remover.addEventListener("click", () => {
                estado[tipo].splice(i, 1);
                desenharSkills(tipo);
            });

            chip.appendChild(remover);
            lista.appendChild(chip);
        });
    }

    // avisar = true quando a pessoa clicou em "Adicionar" (mostra o motivo se não der)
    function adicionarSkill(tipo, avisar) {
        const campo = document.getElementById("perfil-" + tipo + "-input");
        const nome = campo.value.trim();
        limparErroLista(campo);

        if (nome === "") {
            if (avisar) mostrarErroLista(campo, "Digite uma " + NOMES_SKILL[tipo] + " antes de adicionar.");
            return false;
        }
        if (estado[tipo].some((s) => s.toLowerCase() === nome.toLowerCase())) {
            if (avisar) mostrarErroLista(campo, "Essa " + NOMES_SKILL[tipo] + " já foi adicionada.");
            return false;
        }
        if (estado[tipo].length >= 30) {
            if (avisar) mostrarErroLista(campo, "Limite de 30 itens atingido.");
            return false;
        }

        estado[tipo].push(nome);
        desenharSkills(tipo);
        campo.value = "";
        campo.focus();
        return true;
    }

    ["soft", "hard"].forEach((tipo) => {
        document.getElementById("add-" + tipo + "-btn").addEventListener("click", () => adicionarSkill(tipo, true));
        // Enter no campo adiciona a skill em vez de enviar o formulário
        document.getElementById("perfil-" + tipo + "-input").addEventListener("keydown", (ev) => {
            if (ev.key === "Enter") {
                ev.preventDefault();
                adicionarSkill(tipo, true);
            }
        });
        desenharSkills(tipo);
    });

    // ---- Formações ----
    const campoCurso = document.getElementById("formacao-curso");
    const campoInstituicao = document.getElementById("formacao-instituicao");
    const campoAno = document.getElementById("formacao-ano");

    function desenharFormacoes() {
        const lista = document.getElementById("lista-formacoes");
        lista.innerHTML = "";

        estado.formacoes.forEach((f, i) => {
            const item = document.createElement("li");

            const texto = document.createElement("span");
            texto.textContent = f.curso + " — " + f.instituicao + (f.ano ? " (" + f.ano + ")" : "");

            const remover = document.createElement("button");
            remover.type = "button";
            remover.className = "formacao-remover";
            remover.textContent = "Remover";
            remover.addEventListener("click", () => {
                estado.formacoes.splice(i, 1);
                desenharFormacoes();
            });

            item.appendChild(texto);
            item.appendChild(remover);
            lista.appendChild(item);
        });
    }

    // Confere o que está digitado nos três campos da formação.
    // Devolve null (tudo certo), { vazio: true } (nada digitado) ou { campo, msg } (erro).
    function erroDaFormacaoDigitada() {
        const curso = campoCurso.value.trim();
        const instituicao = campoInstituicao.value.trim();
        const ano = campoAno.value.trim();

        if (curso === "" && instituicao === "" && ano === "") return { vazio: true };
        if (curso === "") return { campo: campoCurso, msg: "Informe o curso da formação." };
        if (instituicao === "") return { campo: campoInstituicao, msg: "Informe a instituição da formação." };
        if (ano !== "" && (!/^\d{4}$/.test(ano) || Number(ano) < 1950 || Number(ano) > ANO_MAXIMO)) {
            return { campo: campoAno, msg: "Ano inválido. Use 4 dígitos, por exemplo 2024." };
        }
        return null;
    }

    function limparErrosFormacao() {
        [campoCurso, campoInstituicao, campoAno].forEach(limparErroLista);
    }

    function adicionarFormacao(avisar) {
        limparErrosFormacao();
        const erro = erroDaFormacaoDigitada();

        if (erro && erro.vazio) {
            if (avisar) mostrarErroLista(campoCurso, "Preencha o curso e a instituição para adicionar.");
            return false;
        }
        if (erro) {
            mostrarErroLista(erro.campo, erro.msg);
            return false;
        }
        if (estado.formacoes.length >= 10) {
            mostrarErroLista(campoCurso, "Limite de 10 formações atingido.");
            return false;
        }

        estado.formacoes.push({
            curso: campoCurso.value.trim(),
            instituicao: campoInstituicao.value.trim(),
            ano: campoAno.value.trim(),
        });
        desenharFormacoes();
        campoCurso.value = "";
        campoInstituicao.value = "";
        campoAno.value = "";
        campoCurso.focus();
        return true;
    }

    document.getElementById("add-formacao-btn").addEventListener("click", () => adicionarFormacao(true));
    desenharFormacoes();

    // ---- Salvar ----
    // Registrado depois do carregamento para rodar DEPOIS da validação dos campos comuns (validacao-form.js)
    window.addEventListener("load", () => {
        edicao.addEventListener("submit", (ev) => {
            // O que ficou digitado e não foi adicionado entra na lista (ou mostra o erro)
            ["soft", "hard"].forEach((tipo) => {
                adicionarSkill(tipo, false);

                if (OBRIGATORIOS[tipo] && estado[tipo].length === 0) {
                    mostrarErroLista(
                        document.getElementById("perfil-" + tipo + "-input"),
                        "Adicione pelo menos uma " + NOMES_SKILL[tipo] + "."
                    );
                }
            });

            limparErrosFormacao();
            const erroFormacao = erroDaFormacaoDigitada();
            if (erroFormacao === null) {
                adicionarFormacao(false);
            } else if (!erroFormacao.vazio) {
                mostrarErroLista(erroFormacao.campo, erroFormacao.msg);
            }
            if (OBRIGATORIOS.formacao && estado.formacoes.length === 0 && erroFormacao !== null && erroFormacao.vazio) {
                mostrarErroLista(campoCurso, "Adicione pelo menos uma formação.");
            }

            // Algum campo com erro: bloqueia o envio e leva o foco até o primeiro
            const primeiroComErro = edicao.querySelector(".campo-invalido");
            if (primeiroComErro) {
                ev.preventDefault();
                primeiroComErro.focus();
                return;
            }
            if (ev.defaultPrevented) return;

            // Tudo certo: monta os campos escondidos que vão pro PHP
            const area = document.getElementById("campos-ocultos");
            area.innerHTML = "";

            const criar = (nome, valor) => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = nome;
                input.value = valor;
                area.appendChild(input);
            };

            estado.soft.forEach((nome) => criar("soft_skills[]", nome));
            estado.hard.forEach((nome) => criar("hard_skills[]", nome));
            estado.formacoes.forEach((f) => {
                criar("formacao_curso[]", f.curso);
                criar("formacao_instituicao[]", f.instituicao);
                criar("formacao_ano[]", f.ano || "");
            });
        });
    });
</script>
<?php endif; ?>

</body>
</html>