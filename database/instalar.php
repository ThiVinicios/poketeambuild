<?php
// Instalador do banco: roda o arquivo teambuild.sql no seu MySQL.
// Uso (no terminal, dentro da pasta do projeto):  php database/instalar.php
// Serve para quem não quer abrir o MySQL Workbench só para importar o arquivo.

if (PHP_SAPI !== 'cli') {
    exit('Rode este arquivo pelo terminal: php database/instalar.php');
}

$config = require __DIR__ . '/../config/config.php';
$arquivoSql = __DIR__ . '/teambuild.sql';

if (!extension_loaded('pdo_mysql')) {
    exit("A extensão pdo_mysql do PHP não está ativa. Veja o passo 2 do README.\n");
}

echo "Conectando ao MySQL em {$config['db_host']}:{$config['db_porta']}...\n";
try {
    // Conecta sem escolher banco, porque o próprio .sql cria o banco "teambuild".
    $pdo = new PDO(
        "mysql:host={$config['db_host']};port={$config['db_porta']};charset=utf8mb4",
        $config['db_user'],
        $config['db_senha'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit("Não consegui conectar: " . $e->getMessage() . "\nConfira usuário e senha em config/config.php.\n");
}

echo "Criando banco, tabelas e dados iniciais...\n";
try {
    $pdo->exec(file_get_contents($arquivoSql));
} catch (PDOException $e) {
    exit("Erro ao executar o SQL: " . $e->getMessage() . "\n");
}

// Confere se deu certo contando os registros principais.
$pdo->exec('USE `' . $config['db_nome'] . '`');
$pokemon = $pdo->query('SELECT COUNT(*) FROM pokemon')->fetchColumn();
$tipos = $pdo->query('SELECT COUNT(*) FROM tipos')->fetchColumn();
echo "Pronto! {$pokemon} Pokémon e {$tipos} tipos cadastrados.\n";
echo "Login de administrador: admin@teambuild.com / Admin@123 (troque a senha depois).\n";
