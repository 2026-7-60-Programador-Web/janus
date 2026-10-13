<?php

session_start();

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../views/login.php");
    exit;
}

// Usuários fixos de teste (administradores e egressos de exemplo)
$usuariosCadastrados = [
    [
        "nome" => "Pedro Leandro",
        "email" => "pedro@email.com",
        "senha" => "12345abc",
        "tipo" => "egresso"
    ],
    [
        "nome" => "Emily Vitória",
        "email" => "emily@email.com",
        "senha" => "12345abc",
        "tipo" => "egresso"
    ],
    [
        "nome" => "Walyson Pinheiro",
        "email" => "waly@email.com",
        "senha" => "12345abc",
        "tipo" => "egresso"
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

function voltarComErro(string $mensagem): void
{
    $_SESSION["mensagem"] = $mensagem;
    header("Location: ../views/login.php");
    exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";

// Se o e-mail já vier inválido, nem vale a pena procurar
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro("Erro ao entrar: e-mail inválido!");
}

$usuarioEncontrado = null;

// 1) Usuários fixos de teste
foreach ($usuariosCadastrados as $usuario) {
    if ($usuario["email"] === $email && $usuario["senha"] === $senha) {
        $usuarioEncontrado = $usuario;
        break;
    }
}

// 2) Egressos que se cadastraram pelo formulário (data/egressos.json, senha com hash)
if ($usuarioEncontrado === null) {
    $arquivo = __DIR__ . "/../data/egressos.json";

    if (is_file($arquivo)) {
        $egressos = json_decode((string) file_get_contents($arquivo), true);

        foreach ((array) $egressos as $egresso) {
            if (
                ($egresso["email"] ?? "") === $email
                && password_verify($senha, $egresso["senha_hash"] ?? "")
            ) {
                $usuarioEncontrado = [
                    "nome"  => $egresso["nome"] ?? "Egresso",
                    "email" => $egresso["email"],
                    "tipo"  => "egresso",
                ];
                break;
            }
        }
    }
}

if ($usuarioEncontrado === null) {
    voltarComErro("Erro ao entrar: e-mail e/ou senha inválidos!");
}

// Novo ID de sessão a cada login (evita fixação de sessão)
session_regenerate_id(true);

$_SESSION["email"] = $usuarioEncontrado["email"];
$_SESSION["nome"]  = $usuarioEncontrado["nome"];
$_SESSION["tipo"]  = $usuarioEncontrado["tipo"];

if ($usuarioEncontrado["tipo"] === "administrador") {
    header("Location: ../views/admin/painel.php");
} else {
    header("Location: ../views/egresso/painel.php");
}
exit;
