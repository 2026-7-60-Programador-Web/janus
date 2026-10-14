<?php

session_start();

$usuariosCadastrados = [
    [
        "nome" => "Pedro Leandro",
        "email" => "pedro@email.com",
        "senha" => "12345abc",
        "tipo" => "cliente"
    ],
    [
        "nome" => "Emily Vitória",
        "email" => "emily@email.com",
        "senha" => "12345abc",
        "tipo" => "cliente"
    ],
    [
        "nome" => "Hellison Ferreira",
        "email" => "enzo@email.com",
        "senha" => "12345abc",
        "tipo" => "cliente"
    ],
    [
        "nome" => "Amaury Damasceno",
        "email" => "amaury@email.com",
        "senha" => "12345abc",
        "tipo" => "administrador"
    ],
    [
        "nome" => "Walyson Pinheiro",
        "email" => "walyson@email.com",
        "senha" => "12345abc",
        "tipo" => "administrador"
    ]
];

$mensagem = "";

$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
$senha = $_POST["senha"] ?? "";

// Se o e-mail já vier inválido, nem vale a pena procurar no array
if (!$email) {
    $_SESSION["mensagem"] = "<p>Erro ao entrar: e-mail inválido!</p>";
    header("Location: ../views/login.php");
    exit;
}

$usuarioEncontrado = null;

foreach ($usuariosCadastrados as $usuario) {
    if ($usuario["email"] === $email && $usuario["senha"] === $senha) {
        $usuarioEncontrado = $usuario;
        break;
    }
}

if ($usuarioEncontrado === null) {
    $_SESSION["mensagem"] = "<p>Erro ao entrar: e-mail e/ou senha inválidos!</p>";
    header("Location: ../views/login.php");
    exit;
}

$_SESSION["email"] = $usuarioEncontrado["email"];
$_SESSION["nome"] = $usuarioEncontrado["nome"];
$_SESSION["tipo"] = $usuarioEncontrado["tipo"];

if ($usuarioEncontrado["tipo"] === "administrador") {
    header("Location: ../views/area-admin.php");
} else {
    header("Location: ../views/area-cliente.php");
}
exit;
