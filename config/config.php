<?php
return [
    'db_host'  => getenv('MYSQLHOST')     ?: getenv('MYSQL_HOST')     ?: '127.0.0.1',
    'db_porta' => (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306),
    'db_nome'  => getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'teambuild',
    'db_user'  => getenv('MYSQLUSER')     ?: getenv('MYSQL_USER')     ?: 'root',
    'db_senha' => getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '',
    'base_url' => '',
];