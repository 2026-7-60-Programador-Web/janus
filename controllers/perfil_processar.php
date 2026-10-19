<?php

session_start();
require_once __DIR__ . "/../../config/conexao.php";

$voltar = "../../views/egresso/perfil.php";

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
    || !isset($_SESSION["email"])
    || ($_SESSION["tipo"] ?? "") === "administrador"
) {
    header("Location: ../../views/login.php");
    exit;
}

function limparLista($valor, int $max = 60): array
{
    $lista = [];
    foreach ((array) $valor as $item) {
        $item = trim((string) $item);
        if ($item !== "" && mb_strlen($item) <= $max && !in_array($item, $lista, true)) {
            $lista[] = $item;
        }
    }
    return $lista;
}

$telefone = preg_replace("/\D/", "", $_POST["telefone"] ?? "");
$bio      = trim($_POST["bio"] ?? "");
$soft     = limparLista($_POST["soft_skills"] ?? []);
$hard     = limparLista($_POST["hard_skills"] ?? []);

$formacoes = [];
foreach ((array) ($_POST["formacoes"] ?? []) as $f) {
    $curso = trim((string) ($f["curso"] ?? ""));
    $inst  = trim((string) ($f["instituicao"] ?? ""));
    $ano   = preg_replace("/\D/", "", (string) ($f["ano"] ?? ""));
    if ($curso !== "" && $inst !== "") {
        $formacoes[] = [$curso, $inst, substr($ano, 0, 4)];
    }
}

if ($telefone !== "" && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
    $_SESSION["perfil_erro"] = "Telefone inválido. Use DDD + número.";
    header("Location: $voltar");
    exit;
}
if (mb_strlen($bio) > 1000) {
    $_SESSION["perfil_erro"] = "A descrição pode ter no máximo 1000 caracteres.";
    header("Location: $voltar");
    exit;
}

try {
    $pdo = conectar();
    $pdo->beginTransaction();

    // Descobre o id do egresso logado (cria a linha se for um usuário de teste)
    $st = $pdo->prepare("SELECT id FROM egressos WHERE email = :email LIMIT 1");
    $st->execute([":email" => $_SESSION["email"]]);
    $id = $st->fetchColumn();

    if ($id === false) {
        $ins = $pdo->prepare("INSERT INTO egressos (nome, email, tipo) VALUES (:nome, :email, 'egresso')");
        $ins->execute([":nome" => $_SESSION["nome"] ?? "Egresso", ":email" => $_SESSION["email"]]);
        $id = (int) $pdo->lastInsertId();
    }

    $up = $pdo->prepare("UPDATE egressos SET telefone = :tel, bio = :bio WHERE id = :id");
    $up->execute([":tel" => $telefone, ":bio" => $bio, ":id" => $id]);

    // Recria habilidades e formações
    $pdo->prepare("DELETE FROM egresso_habilidades WHERE egresso_id = :id")->execute([":id" => $id]);
    $pdo->prepare("DELETE FROM egresso_formacoes WHERE egresso_id = :id")->execute([":id" => $id]);

    $insH = $pdo->prepare("INSERT INTO egresso_habilidades (egresso_id, tipo, nome) VALUES (:id, :tipo, :nome)");
    foreach ($soft as $nome) {
        $insH->execute([":id" => $id, ":tipo" => "soft", ":nome" => $nome]);
    }
    foreach ($hard as $nome) {
        $insH->execute([":id" => $id, ":tipo" => "hard", ":nome" => $nome]);
    }

    $insF = $pdo->prepare("INSERT INTO egresso_formacoes (egresso_id, curso, instituicao, ano) VALUES (:id, :c, :i, :a)");
    foreach ($formacoes as [$c, $i, $a]) {
        $insF->execute([":id" => $id, ":c" => $c, ":i" => $i, ":a" => $a]);
    }

    $pdo->commit();
    $_SESSION["perfil_ok"] = "Perfil atualizado com sucesso!";
} catch (Throwable $ex) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION["perfil_erro"] = "Não foi possível salvar o perfil. Tente novamente.";
}

header("Location: $voltar");
exit;