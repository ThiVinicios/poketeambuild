<?php
// Topo comum de todas as páginas (menu + início do HTML).
// Antes de incluir este arquivo, a página define:
//   $tituloPagina  -> título da aba
//   $paginaAtual   -> nome do link do menu que fica destacado

require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/funcoes.php';

$usuario = usuario_logado();
$tituloPagina = $tituloPagina ?? 'TeamBuild';
$paginaAtual = $paginaAtual ?? '';

// Links do menu principal.
$menu = [
    'pokedex' => ['Pokédex', 'pokedex.php'],
    'montar'  => ['Montar equipe', 'montar.php'],
    'equipes' => ['Minhas equipes', 'equipes.php'],
];
if (eh_admin()) {
    $menu['admin'] = ['Administração', 'admin.php'];
}
$flash = pegar_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= url('assets/img/pokebola.png') ?>">
    <title><?= h($tituloPagina) ?> | TeamBuild</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?= url('assets/css/estilo.css') ?>">
</head>
<body data-base="<?= h(rtrim(config()['base_url'], '/')) ?>">
<header class="topo">
    <div class="topo-linha container">
        <a class="logo" href="<?= url('index.php') ?>">
            <span class="pokebola" aria-hidden="true"><span></span></span>
            <span>Team<b>Build</b></span>
        </a>

        <nav class="menu menu-desktop">
            <?php foreach ($menu as $chave => [$rotulo, $arquivo]): ?>
                <a href="<?= url($arquivo) ?>" class="<?= $paginaAtual === $chave ? 'ativo' : '' ?>"><?= h($rotulo) ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="topo-acoes">
            <?php if ($usuario): ?>
                <span class="ola">Olá, <?= h($usuario['nome']) ?></span>
                <button type="button" class="btn btn-secundario btn-pequeno" id="abrir-sair">Sair</button>
            <?php else: ?>
                <a class="link-simples" href="<?= url('login.php') ?>">Entrar</a>
                <a class="btn btn-primario btn-pequeno" href="<?= url('cadastro.php') ?>">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Menu do celular: uma linha rolável abaixo do topo -->
    <nav class="menu menu-mobile">
        <?php foreach ($menu as $chave => [$rotulo, $arquivo]): ?>
            <a href="<?= url($arquivo) ?>" class="<?= $paginaAtual === $chave ? 'ativo' : '' ?>"><?= h($rotulo) ?></a>
        <?php endforeach; ?>
    </nav>
</header>

<?php if ($flash): ?>
    <div class="container"><p class="aviso aviso-<?= h($flash['tipo']) ?>"><?= h($flash['texto']) ?></p></div>
<?php endif; ?>
