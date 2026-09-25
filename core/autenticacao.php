<?php
session_start();

require_once __DIR__ . '/../config/config.php';

function estaLogado()
{
    return isset($_SESSION['usuario_id']);
}

function protegerPagina()
{
    if (!estaLogado()) {
        header('Location: ' . BASE_URL . '/views/login.php');
        exit;
    }
}

function fazerLogin($usuario)
{
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
}

function fazerLogout()
{
    $_SESSION = [];
    session_destroy();
}
