<?php
// Configurações do projeto. É o único arquivo que você precisa editar para rodar.

return [
    // Dados de conexão com o MySQL (o mesmo usuário/senha que você usa no MySQL Workbench).
    'db_host'  => '127.0.0.1',
    'db_porta' => 3306,
    'db_nome'  => 'teambuild',
    'db_user'  => 'root',
    'db_senha' => '',         // <- coloque aqui a senha do seu MySQL

    // Se o site rodar dentro de uma subpasta (ex.: http://localhost/teambuild),
    // coloque '/teambuild'. Com "php -S" deixe vazio.
    'base_url' => '',
];
