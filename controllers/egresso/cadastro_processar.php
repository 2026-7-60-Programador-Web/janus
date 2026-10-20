<?php

session_start();

require_once __DIR__ . "/../../core/conexao.php";
require_once __DIR__ . "/../../config/config.php";

$voltarUrl = BASE_URL . "/views/egresso/cadastro.php";
$loginUrl  = BASE_URL . "/views/login.php";

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $voltarUrl);
    exit;
}

// Volta pro formulário mantendo o que foi digitado (menos as senhas)
function voltarComErros(array $erros, array $antigos, string $url): void
{
    $_SESSION["erros"] = $erros;
    $_SESSION["antigos"] = $antigos;
    header("Location: " . $url);
    exit;
}

// Confere se o e-mail ainda existe no egressos.json antigo (só leitura)
function emailNoJson(string $email): bool
{
    $arquivo = __DIR__ . "/../../data/egressos.json";

    if (!is_file($arquivo)) {
        return false;
    }

    $egressos = json_decode((string) file_get_contents($arquivo), true);

    foreach ((array) $egressos as $egresso) {
        if (($egresso["email"] ?? "") === $email) {
            return true;
        }
    }
    return false;
}

$nome        = trim($_POST["nome"] ?? "");
$emailBruto  = strtolower(trim($_POST["email"] ?? ""));
$curso       = trim($_POST["curso"] ?? "");
$area        = trim($_POST["area_atuacao"] ?? "");
$habilidades = trim($_POST["habilidades"] ?? "");
$telefone    = trim($_POST["telefone"] ?? "");
$senha       = $_POST["senha"] ?? "";
$confirmar   = $_POST["confirmar_senha"] ?? "";

$antigos = [
    "nome"         => $nome,
    "email"        => $emailBruto,
    "curso"        => $curso,
    "area_atuacao" => $area,
    "habilidades"  => $habilidades,
    "telefone"     => $telefone,
];

$erros = [];

// Nome
if ($nome === "") {
    $erros["nome"] = "Informe o nome completo.";
} elseif (mb_strlen($nome) < 3) {
    $erros["nome"] = "O nome deve ter pelo menos 3 caracteres.";
} elseif (mb_strlen($nome) > 120) {
    $erros["nome"] = "O nome deve ter no máximo 120 caracteres.";
}

// E-mail
if ($emailBruto === "") {
    $erros["email"] = "Informe o e-mail.";
} elseif (!filter_var($emailBruto, FILTER_VALIDATE_EMAIL)) {
    $erros["email"] = "Formato de e-mail inválido.";
} elseif (mb_strlen($emailBruto) > 150) {
    $erros["email"] = "O e-mail deve ter no máximo 150 caracteres.";
}

// Curso
if ($curso === "") {
    $erros["curso"] = "Informe o curso.";
} elseif (mb_strlen($curso) > 150) {
    $erros["curso"] = "O curso deve ter no máximo 150 caracteres.";
}

// Área de atuação
if ($area === "") {
    $erros["area_atuacao"] = "Informe a área de atuação.";
} elseif (mb_strlen($area) > 100) {
    $erros["area_atuacao"] = "A área deve ter no máximo 100 caracteres.";
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

if (!empty($erros)) {
    voltarComErros($erros, $antigos, $voltarUrl);
}

// ---- Persistência no banco de dados ----

// E-mail não pode repetir (banco e egressos.json antigo)
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$emailBruto]);

if ($stmt->fetch() || emailNoJson($emailBruto)) {
    voltarComErros(["email" => "Este e-mail já está cadastrado."], $antigos, $voltarUrl);
}

try {
    // A senha é salva como foi digitada (sem hash) e o tipo é sempre "egresso"
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha, tipo, curso, area_atuacao, habilidades, telefone)
         VALUES (?, ?, ?, 'egresso', ?, ?, ?, ?)"
    );
    $stmt->execute([$nome, $emailBruto, $senha, $curso, $area, $habilidades, $telefoneDigitos]);
} catch (PDOException $e) {
    // 23000 = e-mail duplicado (cadastros ao mesmo tempo)
    if ($e->getCode() === "23000") {
        voltarComErros(["email" => "Este e-mail já está cadastrado."], $antigos, $voltarUrl);
    }
    voltarComErros(["geral" => "Não foi possível salvar o cadastro. Tente novamente."], $antigos, $voltarUrl);
}

$_SESSION["sucesso"] = "Cadastro realizado com sucesso! Faça login para entrar.";
header("Location: " . $loginUrl);
exit;