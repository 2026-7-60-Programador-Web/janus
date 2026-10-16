<?php

session_start();

require_once __DIR__ . '/../core/conexao.php';

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../views/login.php");
    exit;
}

function voltarComErro(string $mensagem): void
{
    $_SESSION["mensagem"] = $mensagem;
    header("Location: ../views/login.php");
    exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro("Erro ao entrar: e-mail inválido!");
}

$stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario["senha"] !== $senha) {
    voltarComErro("Erro ao entrar: e-mail e/ou senha inválidos!");
}

// Novo ID de sessão a cada login (evita fixação de sessão)
session_regenerate_id(true);

$_SESSION["usuario_id"] = $usuario["id"];
$_SESSION["email"]      = $usuario["email"];
$_SESSION["nome"]       = $usuario["nome"];
$_SESSION["tipo"]       = $usuario["tipo"];

if ($usuario["tipo"] === "administrador") {
    header("Location: ../views/admin/painel.php");
} else {
    header("Location: ../views/egresso/painel.php");
}
exit;