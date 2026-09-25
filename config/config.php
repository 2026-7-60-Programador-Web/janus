<?php

$caminhoAbsolutoDoProjeto = dirname(__DIR__);

$baseUrl = str_replace(
    realpath($_SERVER['DOCUMENT_ROOT']),
    '',
    realpath($caminhoAbsolutoDoProjeto)
);

define('BASE_URL', str_replace('\\', '/', $baseUrl));

