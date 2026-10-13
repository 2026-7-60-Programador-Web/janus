<?php

session_start();

$listaUrl = "../../views/admin/lista-vagas.php";

// Só administrador logado pode editar ou excluir vagas
if (!isset($_SESSION["email"]) || ($_SESSION["tipo"] ?? "") !== "administrador") {
    header("Location: ../../views/login.php");
    exit;
}

// Só aceita envio de formulário (POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . $listaUrl);
    exit;
}

// Proteção contra CSRF (o token é gerado em lista-vagas.php)
$token = $_POST["csrf"] ?? "";
if (empty($_SESSION["csrf"]) || !hash_equals($_SESSION["csrf"], $token)) {
    $_SESSION["erro_geral"] = "Requisição inválida. Recarregue a página e tente novamente.";
    header("Location: " . $listaUrl);
    exit;
}

$acao = $_POST["acao"] ?? "";
$id   = trim($_POST["id"] ?? "");

if ($id === "" || !in_array($acao, ["editar", "excluir"], true)) {
    $_SESSION["erro_geral"] = "Ação inválida.";
    header("Location: " . $listaUrl);
    exit;
}

// Mesmas regras do cadastro-vaga_processar.php
function validarVaga(string $titulo, string $empresa, string $area, string $descricao): array
{
    $erros = [];

    if ($titulo === "") {
        $erros["titulo"] = "Informe o título da vaga.";
    } elseif (mb_strlen($titulo) < 3) {
        $erros["titulo"] = "O título deve ter pelo menos 3 caracteres.";
    } elseif (mb_strlen($titulo) > 100) {
        $erros["titulo"] = "O título deve ter no máximo 100 caracteres.";
    }

    if ($empresa === "") {
        $erros["empresa"] = "Informe a empresa.";
    } elseif (mb_strlen($empresa) > 100) {
        $erros["empresa"] = "O nome da empresa deve ter no máximo 100 caracteres.";
    }

    if ($area === "") {
        $erros["area"] = "Informe a área da vaga.";
    } elseif (mb_strlen($area) > 100) {
        $erros["area"] = "A área deve ter no máximo 100 caracteres.";
    }

    if ($descricao === "") {
        $erros["descricao"] = "Informe a descrição da vaga.";
    } elseif (mb_strlen($descricao) < 10) {
        $erros["descricao"] = "A descrição deve ter pelo menos 10 caracteres.";
    } elseif (mb_strlen($descricao) > 1000) {
        $erros["descricao"] = "A descrição deve ter no máximo 1000 caracteres.";
    }

    return $erros;
}

// Dados da edição (só usados quando $acao === "editar")
$titulo    = trim($_POST["titulo"] ?? "");
$empresa   = trim($_POST["empresa"] ?? "");
$area      = trim($_POST["area"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");

if ($acao === "editar") {
    $erros = validarVaga($titulo, $empresa, $area, $descricao);

    if (!empty($erros)) {
        // Volta pra lista reabrindo o modal de edição com o que foi digitado
        $_SESSION["erros_edicao"] = $erros;
        $_SESSION["antigos_edicao"] = [
            "id"        => $id,
            "titulo"    => $titulo,
            "empresa"   => $empresa,
            "area"      => $area,
            "descricao" => $descricao,
        ];
        header("Location: " . $listaUrl);
        exit;
    }
}

// ---- Persistência em arquivo JSON (troque por banco quando tiver um) ----
$arquivo = __DIR__ . "/../../data/vagas.json";

$fp = fopen($arquivo, "c+");
if ($fp === false || !flock($fp, LOCK_EX)) {
    $_SESSION["erro_geral"] = "Não foi possível acessar as vagas. Tente novamente.";
    header("Location: " . $listaUrl);
    exit;
}

$conteudo = stream_get_contents($fp);
$vagas = $conteudo !== "" ? json_decode($conteudo, true) : [];
if (!is_array($vagas)) {
    $vagas = [];
}

$encontrada = false;

if ($acao === "excluir") {
    $restantes = array_values(array_filter($vagas, function ($v) use ($id, &$encontrada) {
        if (($v["id"] ?? "") === $id) {
            $encontrada = true;
            return false;
        }
        return true;
    }));
    $vagas = $restantes;
} else {
    foreach ($vagas as &$v) {
        if (($v["id"] ?? "") === $id) {
            $v["titulo"]     = $titulo;
            $v["empresa"]    = $empresa;
            $v["area"]       = $area;
            $v["descricao"]  = $descricao;
            $v["editado_por"] = $_SESSION["email"];
            $v["editado_em"]  = date("Y-m-d H:i:s");
            $encontrada = true;
            break;
        }
    }
    unset($v);
}

if ($encontrada) {
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($vagas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    fflush($fp);
}

flock($fp, LOCK_UN);
fclose($fp);

if (!$encontrada) {
    $_SESSION["erro_geral"] = "Vaga não encontrada. Ela pode já ter sido excluída.";
} elseif ($acao === "excluir") {
    $_SESSION["sucesso"] = "Vaga excluída com sucesso!";
} else {
    $_SESSION["sucesso"] = "Vaga atualizada com sucesso!";
}

header("Location: " . $listaUrl);
exit;