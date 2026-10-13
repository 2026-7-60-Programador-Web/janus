<?php

session_start();

// Só administrador logado pode cadastrar vagas
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "administrador") {
    header("Location: ../../views/login.php");
    exit;
}

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../views/admin/cadastro-vaga.php");
    exit;
}

$titulo    = trim($_POST["titulo"] ?? "");
$empresa   = trim($_POST["empresa"] ?? "");
$area      = trim($_POST["area"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");

$antigos = [
    "titulo"    => $titulo,
    "empresa"   => $empresa,
    "area"      => $area,
    "descricao" => $descricao,
];

// Volta pro formulário mantendo o que foi digitado
function voltarComErros(array $erros, array $antigos): void
{
    $_SESSION["erros"] = $erros;
    $_SESSION["antigos"] = $antigos;
    header("Location: ../../views/admin/cadastro-vaga.php");
    exit;
}

$erros = [];

// Título
if ($titulo === "") {
    $erros["titulo"] = "Informe o título da vaga.";
} elseif (mb_strlen($titulo) < 3) {
    $erros["titulo"] = "O título deve ter pelo menos 3 caracteres.";
} elseif (mb_strlen($titulo) > 100) {
    $erros["titulo"] = "O título deve ter no máximo 100 caracteres.";
}

// Empresa
if ($empresa === "") {
    $erros["empresa"] = "Informe a empresa.";
} elseif (mb_strlen($empresa) > 100) {
    $erros["empresa"] = "O nome da empresa deve ter no máximo 100 caracteres.";
}

// Área
if ($area === "") {
    $erros["area"] = "Informe a área da vaga.";
} elseif (mb_strlen($area) > 100) {
    $erros["area"] = "A área deve ter no máximo 100 caracteres.";
}

// Descrição
if ($descricao === "") {
    $erros["descricao"] = "Informe a descrição da vaga.";
} elseif (mb_strlen($descricao) < 10) {
    $erros["descricao"] = "A descrição deve ter pelo menos 10 caracteres.";
} elseif (mb_strlen($descricao) > 1000) {
    $erros["descricao"] = "A descrição deve ter no máximo 1000 caracteres.";
}

if (!empty($erros)) {
    voltarComErros($erros, $antigos);
}

// ---- Persistência em arquivo JSON (troque por banco quando tiver um) ----
$pasta   = __DIR__ . "/../../data";
$arquivo = $pasta . "/vagas.json";

if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
    voltarComErros(["geral" => "Não foi possível salvar a vaga. Tente novamente."], $antigos);
}

$fp = fopen($arquivo, "c+");
if ($fp === false || !flock($fp, LOCK_EX)) {
    voltarComErros(["geral" => "Não foi possível salvar a vaga. Tente novamente."], $antigos);
}

$conteudo = stream_get_contents($fp);
$vagas = $conteudo !== "" ? json_decode($conteudo, true) : [];
if (!is_array($vagas)) {
    $vagas = [];
}

$vagas[] = [
    "id"         => uniqid("vaga_", true),
    "titulo"     => $titulo,
    "empresa"    => $empresa,
    "area"       => $area,
    "descricao"  => $descricao,
    "criado_por" => $_SESSION["email"],
    "criado_em"  => date("Y-m-d H:i:s"),
];

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, json_encode($vagas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

$_SESSION["sucesso"] = "Vaga cadastrada com sucesso!";
header("Location: ../../views/admin/cadastro-vaga.php");
exit;