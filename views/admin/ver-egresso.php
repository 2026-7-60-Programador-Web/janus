<?php

session_start();

// Bloqueia quem não está logado ou não é administrador
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "administrador") {
    header("Location: ../login.php");
    exit;
}

require __DIR__ . '/../../controllers/admin/painel_processar.php';

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$emailEgresso = trim($_GET["email"] ?? "");
$egresso = $emailEgresso !== "" ? buscarEgressoPorEmail($egressosCadastrados, $emailEgresso) : null;

if ($egresso === null) {
    http_response_code(404);
}

$partesCurso = array_filter([$egresso["curso"] ?? "", $egresso["instituicao"] ?? ""]);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janus | <?php echo $egresso ? e($egresso["nome"]) : "Egresso não encontrado"; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../../assets/css/painel.css" rel="stylesheet">
    <link href="../../assets/css/menu-mobile.css" rel="stylesheet">
    <script src="../../assets/js/menu.js" defer></script>
</head>
<body>

<header class="topbar">
    <div class="topbar-esq">
        <a class="marca" href="painel.php">JANUS</a>
        <nav class="menu janus-nav" id="menu-admin">
            <a href="painel.php" class="ativo">Egressos</a>
            <a href="lista-vagas.php">Vagas</a>
            <a href="cadastro-vaga.php">Nova vaga</a>
        </nav>
    </div>
    <button type="button" class="menu-toggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="menu-admin"><span></span><span></span><span></span></button>
</header>

<main>

    <a class="link-voltar-painel" href="painel.php">&larr; Voltar ao painel de egressos</a>

    <?php if ($egresso === null): ?>

        <div class="tabela-wrap">
            <p class="vazio">Egresso não encontrado.</p>
        </div>

    <?php else: ?>

        <div class="cartao-egresso">
            <div class="cabeca">
                <div class="avatar" style="background: <?php echo corAvatar($egresso["nome"]); ?>">
                    <?php echo e(iniciaisNome($egresso["nome"])); ?>
                </div>
                <div>
                    <h1><?php echo e($egresso["nome"]); ?></h1>
                    <p class="sub"><?php echo e(implode(" — ", $partesCurso) ?: "Formação não informada"); ?></p>
                </div>
            </div>

            <div class="contato">
                <div>
                    <span>E-mail</span>
                    <a class="email-link" href="mailto:<?php echo e($egresso["email"]); ?>"><?php echo e($egresso["email"]); ?></a>
                </div>
                <div>
                    <span>Telefone</span>
                    <?php echo e(telefoneFormatado($egresso["telefone"] ?? "Não informado")); ?>
                </div>
                <div>
                    <span>Área de atuação</span>
                    <?php echo e($egresso["area"] ?: "Não informada"); ?>
                </div>
                <div>
                    <span>Formatura</span>
                    <?php echo e($egresso["ano_formatura"] ?: "Não informada"); ?>
                </div>
            </div>

            <p class="rotulo-secao">Sobre</p>
            <?php if (!empty($egresso["sobre"])): ?>
                <p class="texto"><?php echo e($egresso["sobre"]); ?></p>
            <?php else: ?>
                <p class="vazio-inline">Nenhuma descrição informada.</p>
            <?php endif; ?>

            <div class="colunas">
                <div>
                    <p class="rotulo-secao">Soft skills</p>
                    <?php if (!empty($egresso["soft_skills"])): ?>
                        <div class="chips">
                            <?php foreach ($egresso["soft_skills"] as $skill): ?>
                                <span class="chip"><?php echo e($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="vazio-inline">Nenhuma informada.</p>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="rotulo-secao">Hard skills</p>
                    <?php if (!empty($egresso["hard_skills"])): ?>
                        <div class="chips">
                            <?php foreach ($egresso["hard_skills"] as $skill): ?>
                                <span class="chip"><?php echo e($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="vazio-inline">Nenhuma informada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>

</main>

</body>
</html>
