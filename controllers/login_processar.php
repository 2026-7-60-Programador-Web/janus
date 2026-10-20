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

// Senhas novas ficam em texto puro; as importadas do egressos.json já vêm com hash ($2y$...)
function senhaConfere(string $digitada, string $guardada): bool
{
    if (str_starts_with($guardada, '$2y$')) {
        return password_verify($digitada, $guardada);
    }
    return hash_equals($guardada, $digitada);
}

$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComErro("Erro ao entrar: e-mail inválido!");
}

// 1) Banco de dados
$stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$usuarioEncontrado = null;

if ($usuario && senhaConfere($senha, $usuario["senha"])) {
    $usuarioEncontrado = $usuario;
}

// 2) Egressos antigos que ainda estão só no data/egressos.json (senha com hash)
if ($usuarioEncontrado === null && !$usuario) {
    $arquivo = __DIR__ . "/../data/egressos.json";

    if (is_file($arquivo)) {
        $egressos = json_decode((string) file_get_contents($arquivo), true);

        foreach ((array) $egressos as $egresso) {
            if (
                ($egresso["email"] ?? "") === $email
                && password_verify($senha, $egresso["senha_hash"] ?? "")
            ) {
                $usuarioEncontrado = [
                    "id"    => null,
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

$_SESSION["usuario_id"] = $usuarioEncontrado["id"];
$_SESSION["email"]      = $usuarioEncontrado["email"];
$_SESSION["nome"]       = $usuarioEncontrado["nome"];
$_SESSION["tipo"]       = $usuarioEncontrado["tipo"];

if ($usuarioEncontrado["tipo"] === "administrador") {
    header("Location: ../views/admin/painel.php");
} else {
    header("Location: ../views/egresso/painel.php");
}
exit;