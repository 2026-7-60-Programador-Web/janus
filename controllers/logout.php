<?php
require_once __DIR__ . '/../core/autenticacao.php';

fazerLogout();
header('Location: ' . BASE_URL . '/views/login.php');
exit;