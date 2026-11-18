<?php
// Funções pequenas usadas em várias páginas.

// Escapa texto antes de mostrar no HTML (proteção contra XSS). Use SEMPRE que imprimir dados.
function h(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// Monta o endereço de uma página respeitando a base_url do config.
function url(string $caminho = ''): string
{
    return rtrim(config()['base_url'], '/') . '/' . ltrim($caminho, '/');
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

// Conta caracteres de verdade (acentos contam como 1), sem precisar da extensão mbstring.
function tamanho(string $texto): int
{
    return preg_match_all('/./us', $texto);
}

// Lê um campo de texto do formulário já limpo (sem espaços nas pontas).
function campo(string $nome, int $max = 200): string
{
    $valor = $_POST[$nome] ?? '';
    return is_string($valor) ? trim(mb_substr_seguro($valor, $max)) : '';
}

function mb_substr_seguro(string $texto, int $max): string
{
    return function_exists('mb_substr') ? mb_substr($texto, 0, $max) : substr($texto, 0, $max);
}

// Responde em JSON (usado pelas rotas da pasta api/).
function responder_json(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
