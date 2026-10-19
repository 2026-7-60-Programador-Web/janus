<?php

session_start();

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../views/egresso/cadastro.php");
    exit;
}

$nome       = trim($_POST["nome"] ?? "");
$emailBruto = strtolower(trim($_POST["email"] ?? ""));
$curso      = trim($_POST["curso"] ?? "");
$area       = trim($_POST["area_atuacao"] ?? "");
$habilidades = trim($_POST["habilidades"] ?? "");
$telefone   = trim($_POST["telefone"] ?? "");
$senha      = $_POST["senha"] ?? "";
$confirmar  = $_POST["confirmar_senha"] ?? "";

$erros = [];

// Nome
if ($nome === "") {
    $erros["nome"] = "Informe o nome completo.";
} elseif (mb_strlen($nome) < 3) {
    $erros["nome"] = "O nome deve ter pelo menos 3 caracteres.";
}

// E-mail
if ($emailBruto === "") {
    $erros["email"] = "Informe o e-mail.";
} elseif (!filter_var($emailBruto, FILTER_VALIDATE_EMAIL)) {
    $erros["email"] = "Formato de e-mail inválido.";
}

// Curso
if ($curso === "") {
    $erros["curso"] = "Informe o curso.";
}

// Área de atuação
if ($area === "") {
    $erros["area_atuacao"] = "Informe a área de atuação.";
}

// Habilidades
if ($habilidades === "") {
    $erros["habilidades"] = "Informe ao menos uma habilidade.";
} elseif (mb_strlen($habilidades) > 300) {
    $erros["habilidades"] = "Máximo de 300 caracteres.";
}

// Telefone (aceita máscara, valida só os dígitos: DDD + 8 ou 9 dígitos)
$telefoneDigitos = preg_replace("/\D/", "", $telefone);
if ($telefone === "") {
    $erros["telefone"] = "Informe o telefone.";
} elseif (strlen($telefoneDigitos) < 10 || strlen($telefoneDigitos) > 11) {
    $erros["telefone"] = "Telefone inválido. Use DDD + número.";
}

// Senha
if ($senha === "") {
    $erros["senha"] = "Informe a senha.";
} elseif (strlen($senha) < 8) {
    $erros["senha"] = "A senha deve ter pelo menos 8 caracteres.";
}

// Confirmação
if ($confirmar === "") {
    $erros["confirmar_senha"] = "Confirme a senha.";
} elseif ($senha !== $confirmar) {
    $erros["confirmar_senha"] = "As senhas não coincidem.";
}

// Volta pro formulário mantendo o que foi digitado (menos as senhas)
function voltarComErros(array $erros, array $antigos): void
{
    $_SESSION["erros"] = $erros;
    $_SESSION["antigos"] = $antigos;
    header("Location: ../../views/egresso/cadastro.php");
    exit;
}

$antigos = [
    "nome"         => $nome,
    "email"        => $emailBruto,
    "curso"        => $curso,
    "area_atuacao" => $area,
    "habilidades"  => $habilidades,
    "telefone"     => $telefone,
];

if (!empty($erros)) {
    voltarComErros($erros, $antigos);
}

// ---- Persistência no banco de dados ----
require_once __DIR__ . "/../../core/egresso_repositorio.php";

try {
    $pdo = egressoConexao();

    // E-mail não pode repetir
    $stmt = $pdo->prepare("SELECT id FROM egressos WHERE email = :email");
    $stmt->execute([":email" => $emailBruto]);
    if ($stmt->fetch()) {
        voltarComErros(["email" => "Este e-mail já está cadastrado."], $antigos);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO egressos (nome, email, senha_hash, telefone, curso, area_atuacao)
         VALUES (:nome, :email, :senha_hash, :telefone, :curso, :area_atuacao)"
    );
    $stmt->execute([
        ":nome"         => $nome,
        ":email"        => $emailBruto,
        ":senha_hash"   => password_hash($senha, PASSWORD_DEFAULT),
        ":telefone"     => $telefoneDigitos,
        ":curso"        => $curso,
        ":area_atuacao" => $area,
    ]);
    $egressoId = (int) $pdo->lastInsertId();

    // As habilidades do cadastro (texto separado por vírgula) viram hard skills
    $stmt  = $pdo->prepare("INSERT INTO egresso_habilidades (egresso_id, tipo, nome) VALUES (:id, 'hard', :nome)");
    $vistas = [];
    foreach (explode(",", $habilidades) as $habilidade) {
        $habilidade = mb_substr(trim($habilidade), 0, 80);
        $chave      = mb_strtolower($habilidade);

        if ($habilidade === "" || isset($vistas[$chave])) {
            continue;
        }

        $vistas[$chave] = true;
        $stmt->execute([":id" => $egressoId, ":nome" => $habilidade]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // 23000 = violação de chave única (e-mail repetido)
    if ($e->getCode() === "23000") {
        voltarComErros(["email" => "Este e-mail já está cadastrado."], $antigos);
    }

    voltarComErros(["geral" => "Não foi possível salvar o cadastro. Tente novamente."], $antigos);
}

$_SESSION["sucesso"] = "Cadastro realizado com sucesso! Faça login para entrar.";
header("Location: ../../views/login.php");
exit;