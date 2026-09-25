<?php
require_once __DIR__ . '/../core/autenticacao.php';
require_once __DIR__ . '/../core/conexao.php';

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    $_SESSION['login_erro'] = 'Preencha e-mail e senha.';
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email');
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    $_SESSION['login_erro'] = 'E-mail ou senha inválidos.';
    header('Location: ' . BASE_URL . '/views/login.php');
    exit;
}

fazerLogin($usuario);
header('Location: ' . BASE_URL . '/index.php');
exit;