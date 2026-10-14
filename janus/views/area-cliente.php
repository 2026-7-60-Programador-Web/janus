<?php

session_start();

// Bloqueia quem não está logado ou não é cliente
if (!isset($_SESSION["email"]) || $_SESSION["tipo"] !== "cliente") {
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<header class="navbar navbar-expand navbar-dark bg-primary px-3">
    <span class="navbar-text text-white">
        Bem-vindo(a), <?php echo htmlspecialchars($_SESSION["nome"]); ?>!
    </span>
    <a href="../controllers/logout.php" class="btn btn-outline-light btn-sm ms-auto">Sair</a>
</header>

<main class="container py-5">
    <h1 class="h4">Área do Cliente</h1>
    <p>Esta página só deve aparecer para usuários do tipo <strong>cliente</strong>.</p>
</main>

</body>
</html>
