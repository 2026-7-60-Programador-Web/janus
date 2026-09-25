<?php
require_once __DIR__ . '/core/autenticacao.php';

protegerPagina();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header class="navbar navbar-expand navbar-dark bg-dark px-3">
    <span class="navbar-text text-white">
        Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
    </span>
    <a href="<?= BASE_URL ?>/controllers/logout.php" class="btn btn-outline-light btn-sm ms-auto">Sair</a>
</header>

<main class="container py-5">

</main>

<footer class="text-center text-muted py-4">

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>