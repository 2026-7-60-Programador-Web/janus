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

// ---- Persistência em arquivo JSON (troque por banco quando tiver um) ----
$pasta   = __DIR__ . "/../../data";
$arquivo = $pasta . "/egressos.json";

if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
    voltarComErros(["geral" => "Não foi possível salvar o cadastro. Tente novamente."], $antigos);
}

$fp = fopen($arquivo, "c+");
if ($fp === false || !flock($fp, LOCK_EX)) {
    voltarComErros(["geral" => "Não foi possível salvar o cadastro. Tente novamente."], $antigos);
}

$conteudo = stream_get_contents($fp);
$egressos = $conteudo !== "" ? json_decode($conteudo, true) : [];
if (!is_array($egressos)) {
    $egressos = [];
}

// E-mail não pode repetir
foreach ($egressos as $egresso) {
    if (($egresso["email"] ?? "") === $emailBruto) {
        flock($fp, LOCK_UN);
        fclose($fp);
        voltarComErros(["email" => "Este e-mail já está cadastrado."], $antigos);
    }
}

$egressos[] = [
    "id"           => uniqid("egr_", true),
    "nome"         => $nome,
    "email"        => $emailBruto,
    "curso"        => $curso,
    "area_atuacao" => $area,
    "habilidades"  => $habilidades,
    "telefone"     => $telefoneDigitos,
    "senha_hash"   => password_hash($senha, PASSWORD_DEFAULT),
    "tipo"         => "egresso",
    "criado_em"    => date("Y-m-d H:i:s"),
];

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($egressos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

$_SESSION["sucesso"] = "Cadastro realizado com sucesso! Faça login para entrar.";
header("Location: ../../views/login.php");
exit;