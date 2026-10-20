<?php

session_start();

require_once __DIR__ . "/../../core/egresso_repositorio.php";

// Só egresso logado altera o próprio perfil
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "egresso") {
    header("Location: ../../views/login.php");
    exit;
}

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../views/egresso/perfil.php");
    exit;
}

// Volta pro perfil (já no modo edição) mostrando os erros e mantendo o que foi digitado
function voltarComErros(array $erros, array $antigos): void
{
    $_SESSION["perfil_erros"]   = $erros;
    $_SESSION["perfil_antigos"] = $antigos;
    header("Location: ../../views/egresso/perfil.php");
    exit;
}

// Limpa uma lista de habilidades: tira vazios e repetidos, limita tamanho e quantidade
function limparHabilidades($lista): array
{
    if (!is_array($lista)) {
        return [];
    }

    $resultado = [];
    $vistas    = [];

    foreach ($lista as $item) {
        if (!is_string($item)) {
            continue;
        }

        $nome  = mb_substr(trim($item), 0, 60);
        $chave = mb_strtolower($nome);

        if ($nome === "" || isset($vistas[$chave])) {
            continue;
        }

        $vistas[$chave] = true;
        $resultado[]    = $nome;
    }

    return array_slice($resultado, 0, 30);
}

$email    = strtolower(trim($_POST["email"] ?? ""));
$telefone = trim($_POST["telefone"] ?? "");
$bio      = trim($_POST["bio"] ?? "");
$soft     = limparHabilidades($_POST["soft_skills"] ?? []);
$hard     = limparHabilidades($_POST["hard_skills"] ?? []);

// Formações chegam em três listas paralelas (curso, instituição, ano)
$cursos       = is_array($_POST["formacao_curso"] ?? null) ? array_values($_POST["formacao_curso"]) : [];
$instituicoes = is_array($_POST["formacao_instituicao"] ?? null) ? array_values($_POST["formacao_instituicao"]) : [];
$anos         = is_array($_POST["formacao_ano"] ?? null) ? array_values($_POST["formacao_ano"]) : [];

$formacoes = [];
$erros     = [];
$anoMaximo = (int) date("Y") + 6;

foreach ($cursos as $i => $cursoBruto) {
    $curso       = mb_substr(trim((string) $cursoBruto), 0, 120);
    $instituicao = mb_substr(trim((string) ($instituicoes[$i] ?? "")), 0, 120);
    $ano         = trim((string) ($anos[$i] ?? ""));

    if ($curso === "" && $instituicao === "" && $ano === "") {
        continue;
    }

    $numero = count($formacoes) + 1;

    if ($curso === "" || $instituicao === "") {
        $erros[] = "Formação $numero: informe o curso e a instituição.";
    }

    if ($ano !== "" && (!preg_match("/^\d{4}$/", $ano) || (int) $ano < 1950 || (int) $ano > $anoMaximo)) {
        $erros[] = "Formação $numero: ano inválido.";
    }

    $formacoes[] = ["curso" => $curso, "instituicao" => $instituicao, "ano" => $ano];
}

$formacoes = array_slice($formacoes, 0, 10);

$antigos = [
    "email"     => $email,
    "telefone"  => $telefone,
    "bio"       => $bio,
    "soft"      => $soft,
    "hard"      => $hard,
    "formacoes" => $formacoes,
];

// E-mail
if ($email === "") {
    $erros[] = "Informe o e-mail.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Formato de e-mail inválido.";
} elseif (mb_strlen($email) > 150) {
    $erros[] = "O e-mail pode ter no máximo 150 caracteres.";
}

// Telefone (aceita máscara, valida só os dígitos: DDD + 8 ou 9 dígitos)
$telefoneDigitos = preg_replace("/\D/", "", $telefone);
if ($telefone === "") {
    $erros[] = "Informe o telefone.";
} elseif (strlen($telefoneDigitos) < 10 || strlen($telefoneDigitos) > 11) {
    $erros[] = "Telefone inválido. Use DDD + número.";
}

// Bio
if ($bio === "") {
    $erros[] = "Preencha o campo \"Sobre você\".";
} elseif (mb_strlen($bio) > 1000) {
    $erros[] = "O texto \"Sobre você\" pode ter no máximo 1000 caracteres.";
}

// Listas obrigatórias
if (empty($soft)) {
    $erros[] = "Adicione pelo menos uma soft skill.";
}
if (empty($hard)) {
    $erros[] = "Adicione pelo menos uma hard skill.";
}
if (empty($formacoes)) {
    $erros[] = "Adicione pelo menos uma formação.";
}

if (!empty($erros)) {
    voltarComErros($erros, $antigos);
}

// ---- Persistência no banco de dados ----
try {
    $pdo = egressoConexao();

    $stmt = $pdo->prepare("SELECT id FROM egressos WHERE email = :email");
    $stmt->execute([":email" => $_SESSION["email"]]);
    $egressoId = $stmt->fetchColumn();

    if ($egressoId === false) {
        voltarComErros(["Seu usuário não tem perfil salvo no banco. Cadastre-se como egresso para poder editar."], $antigos);
    }

    // O novo e-mail não pode ser de outro egresso
    $stmt = $pdo->prepare("SELECT id FROM egressos WHERE email = :email AND id <> :id");
    $stmt->execute([":email" => $email, ":id" => $egressoId]);
    if ($stmt->fetch()) {
        voltarComErros(["Este e-mail já está cadastrado em outra conta."], $antigos);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE egressos SET email = :email, telefone = :telefone WHERE id = :id");
    $stmt->execute([
        ":email"    => $email,
        ":telefone" => $telefoneDigitos,
        ":id"       => $egressoId,
    ]);

    // Bio: cria a linha do perfil na primeira vez e atualiza nas próximas
    $bioSalvar = $bio !== "" ? $bio : null;
    $stmt = $pdo->prepare(
        "INSERT INTO perfis_egresso (egresso_id, bio) VALUES (:id, :bio)
         ON DUPLICATE KEY UPDATE bio = :bio_novo"
    );
    $stmt->execute([":id" => $egressoId, ":bio" => $bioSalvar, ":bio_novo" => $bioSalvar]);

    // Habilidades e formações: apaga as antigas e grava a lista nova
    $pdo->prepare("DELETE FROM egresso_habilidades WHERE egresso_id = :id")->execute([":id" => $egressoId]);
    $stmt = $pdo->prepare("INSERT INTO egresso_habilidades (egresso_id, tipo, nome) VALUES (:id, :tipo, :nome)");
    foreach ($soft as $nome) {
        $stmt->execute([":id" => $egressoId, ":tipo" => "soft", ":nome" => $nome]);
    }
    foreach ($hard as $nome) {
        $stmt->execute([":id" => $egressoId, ":tipo" => "hard", ":nome" => $nome]);
    }

    $pdo->prepare("DELETE FROM egresso_formacoes WHERE egresso_id = :id")->execute([":id" => $egressoId]);
    $stmt = $pdo->prepare("INSERT INTO egresso_formacoes (egresso_id, curso, instituicao, ano) VALUES (:id, :curso, :instituicao, :ano)");
    foreach ($formacoes as $f) {
        $stmt->execute([
            ":id"          => $egressoId,
            ":curso"       => $f["curso"],
            ":instituicao" => $f["instituicao"],
            ":ano"         => $f["ano"] !== "" ? $f["ano"] : null,
        ]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // 23000 = violação de chave única (e-mail repetido)
    if ($e->getCode() === "23000") {
        voltarComErros(["Este e-mail já está cadastrado em outra conta."], $antigos);
    }

    voltarComErros(["Não foi possível salvar o perfil. Tente novamente."], $antigos);
}

// O e-mail é o que identifica o usuário na sessão: mantém em dia se ele mudou
$_SESSION["email"]          = $email;
$_SESSION["perfil_sucesso"] = "Perfil atualizado com sucesso!";

header("Location: ../../views/egresso/perfil.php");
exit;
