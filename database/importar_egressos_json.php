<?php

// Rode UMA vez para levar os egressos do data/egressos.json para o banco.
// Abra no navegador: http://localhost/.../database/importar_egressos_json.php
// Só funciona na própria máquina (localhost).

header("Content-Type: text/plain; charset=utf-8");

$ip = $_SERVER["REMOTE_ADDR"] ?? "cli";
if (PHP_SAPI !== "cli" && !in_array($ip, ["127.0.0.1", "::1"], true)) {
    http_response_code(403);
    exit("Acesso negado.");
}

require_once __DIR__ . "/../core/conexao.php";

$arquivo = __DIR__ . "/../data/egressos.json";

if (!is_file($arquivo)) {
    exit("Arquivo data/egressos.json não encontrado. Nada para importar.");
}

$egressos = json_decode((string) file_get_contents($arquivo), true);

if (!is_array($egressos) || count($egressos) === 0) {
    exit("O egressos.json está vazio. Nada para importar.");
}

$existe = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$insere = $pdo->prepare(
    "INSERT INTO usuarios (nome, email, senha, tipo, curso, area_atuacao, habilidades, telefone, criado_em)
     VALUES (?, ?, ?, 'egresso', ?, ?, ?, ?, ?)"
);

$importados = 0;
$ignorados  = 0;

foreach ($egressos as $e) {
    $email = strtolower(trim($e["email"] ?? ""));

    if ($email === "" || empty($e["senha_hash"])) {
        $ignorados++;
        continue;
    }

    $existe->execute([$email]);
    if ($existe->fetch()) {
        $ignorados++;
        continue;
    }

    // A senha segue com o hash original: o login reconhece e confere com password_verify
    $insere->execute([
        $e["nome"] ?? "Egresso",
        $email,
        $e["senha_hash"],
        $e["curso"] ?? null,
        $e["area_atuacao"] ?? null,
        $e["habilidades"] ?? null,
        $e["telefone"] ?? null,
        $e["criado_em"] ?? date("Y-m-d H:i:s"),
    ]);
    $importados++;
}

echo "Importação concluída.\n";
echo "Importados: $importados\n";
echo "Ignorados (já existiam ou sem senha): $ignorados\n";