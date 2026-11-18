<?php
// Controle de sessão: login, nível de acesso e proteção CSRF.

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

// Cookie da sessão só acessível pelo servidor (HttpOnly) e sem vazar para outros sites.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'path'     => '/',
]);
session_start();

// Devolve os dados do usuário logado (ou null). Sempre confere no banco,
// assim se o admin mudar o papel de alguém isso vale na hora.
function usuario_logado(): ?array
{
    static $usuario = false;
    if ($usuario !== false) {
        return $usuario;
    }
    if (empty($_SESSION['usuario_id'])) {
        return $usuario = null;
    }
    $stmt = pdo()->prepare('SELECT id, nome, email, papel FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch() ?: null;
    if ($usuario === null) {
        unset($_SESSION['usuario_id']);
    }
    return $usuario;
}

function eh_admin(): bool
{
    $u = usuario_logado();
    return $u !== null && $u['papel'] === 'admin';
}

// Páginas que exigem login mandam o visitante para a tela de entrada.
function exigir_login(): array
{
    $u = usuario_logado();
    if ($u === null) {
        redirecionar('login.php');
    }
    return $u;
}

// Páginas do painel admin: usuário comum volta para a área dele.
function exigir_admin(): array
{
    $u = exigir_login();
    if ($u['papel'] !== 'admin') {
        redirecionar('montar.php');
    }
    return $u;
}

// Token CSRF: garante que o formulário foi enviado pelo nosso próprio site.
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_valido(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

// Para formulários comuns: interrompe se o token estiver errado.
function exigir_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido($_POST['csrf'] ?? null)) {
        http_response_code(400);
        exit('Requisição inválida. Volte e tente novamente.');
    }
}

// Mensagens rápidas (ex.: "Pokémon salvo") que aparecem uma vez após redirecionar.
function flash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

function pegar_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
