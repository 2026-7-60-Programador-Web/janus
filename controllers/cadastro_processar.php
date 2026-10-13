<?php
session_start();
require_once __DIR__ . '/../core/conexao.php';
require_once __DIR__ . '/../config/config.php';

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

$_SESSION['cadastro_dados'] = ['nome' => $nome, 'email' => $email];

if ($nome === '' || $email === '' || $senha === '' || $confirmarSenha === '') {
    $_SESSION['cadastro_erro'] = 'Preencha todos os campos.';
    header('Location: ' . BASE_URL . '/views/cadastro.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['cadastro_erro'] = 'Digite um e-mail válido.';
    header('Location: ' . BASE_URL . '/views/cadastro.php');
    exit;
}

if (strlen($senha) < 6) {
    $_SESSION['cadastro_erro'] = 'A senha deve ter pelo menos 6 caracteres.';
    header('Location: ' . BASE_URL . '/views/cadastro.php');
    exit;
}

if ($senha !== $confirmarSenha) {
    $_SESSION['cadastro_erro'] = 'As senhas não coincidem.';
    header('Location: ' . BASE_URL . '/views/cadastro.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
$stmt->execute(['email' => $email]);

if ($stmt->fetch()) {
    $_SESSION['cadastro_erro'] = 'Já existe um usuário cadastrado com este e-mail.';
    header('Location: ' . BASE_URL . '/views/cadastro.php');
    exit;
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
$stmt->execute(['nome' => $nome, 'email' => $email, 'senha' => $senhaHash]);

unset($_SESSION['cadastro_dados']);
$_SESSION['login_sucesso'] = 'Cadastro realizado com sucesso! Você já pode fazer login.';
header('Location: ' . BASE_URL . '/views/login.php');
exit;