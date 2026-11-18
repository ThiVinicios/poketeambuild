<?php
return [
    'db_host'  => getenv('MYSQL_HOST')     ?: '127.0.0.1',
    'db_porta' => getenv('MYSQL_PORT')     ?: 3306,
    'db_nome'  => getenv('MYSQL_DATABASE') ?: 'teambuild',
    'db_user'  => getenv('MYSQLUSER')      ?: 'root',
    'db_senha' => getenv('MYSQL_PASSWORD') ?: '',
    'base_url' => '',
];