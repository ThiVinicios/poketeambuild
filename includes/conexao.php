<?php
// Cria a conexão com o MySQL usando PDO.
// Usamos PDO com "prepared statements" em todo o projeto para evitar SQL Injection.

function config(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $c = config();
    $dsn = "mysql:host={$c['db_host']};port={$c['db_porta']};dbname={$c['db_nome']};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $c['db_user'], $c['db_senha'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // prepared statements de verdade
        ]);
    } catch (PDOException $e) {
        // Não mostramos o erro técnico na tela para não vazar dados do banco.
        http_response_code(500);
        exit('Não foi possível conectar ao banco de dados. Confira o arquivo config/config.php e se o MySQL está ligado.');
    }
    return $pdo;
}
