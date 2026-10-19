<?php

// Conexão PDO com o MySQL do phpMyAdmin (XAMPP/WAMP/Laragon)
function conectar(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host  = "localhost";
    $banco = "janus";
    $user  = "root";   // padrão do XAMPP
    $pass  = "";       // padrão do XAMPP é vazio

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$banco;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $ex) {
        http_response_code(500);
        exit("Erro ao conectar ao banco de dados.");
    }

    return $pdo;
}