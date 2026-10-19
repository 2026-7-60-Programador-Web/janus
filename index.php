<?php

// Ponto de entrada do sistema: sempre abre na tela de login.
session_start();

// Encerra qualquer sessão que tenha ficado ativa, para o login não ser
// pulado e o usuário cair direto no painel (egresso ou admin).
$_SESSION = [];
session_destroy();

header("Location: views/login.php");
exit;
