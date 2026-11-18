<?php
// Minhas equipes: lista todas as equipes do usuário. Ao clicar numa delas,
// aparecem os detalhes e a análise (equipes.js + analise.js).

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/consultas.php';
require_once __DIR__ . '/../includes/cards.php';

$usuario = exigir_login();
$equipes = listar_equipes((int) $usuario['id']);

$tituloPagina = 'Minhas equipes';
$paginaAtual = 'equipes';
$scripts = ['analise.js', 'equipes.js'];
require __DIR__ . '/../includes/cabecalho.php';
?>
<main class="container pagina-equipes">
    <span class="etiqueta-texto">Treinador</span>
    <h1>Minhas equipes</h1>
    <p class="intro">Selecione uma equipe para ver seus Pokémon e a análise completa de tipos.</p>

    <?php if (!$equipes): ?>
        <div class="vazio">
            <p><b>Você ainda não criou equipes.</b></p>
            <a class="btn btn-primario" href="<?= url('montar.php') ?>">Montar primeira equipe</a>
        </div>
    <?php else: ?>
        <div class="grade-equipes">
            <?php foreach ($equipes as $e): ?>
                <button type="button" class="equipe-card" data-equipe="<?= $e['id'] ?>">
                    <div class="equipe-card-topo">
                        <h2><?= h($e['nome']) ?></h2>
                    </div>
                    <div class="equipe-fotos">
                        <?php foreach ($e['pokemon'] as $p): ?>
                            <img src="<?= h($p['imagem_url']) ?>" alt="<?= h($p['nome']) ?>" width="48" height="48">
                        <?php endforeach; ?>
                    </div>
                    <span class="ver-mais">Ver detalhes e análise</span>
                </button>
            <?php endforeach; ?>
        </div>

        <?php foreach ($equipes as $e): ?>
            <section class="detalhe" id="detalhe-<?= $e['id'] ?>" hidden>
                <div class="painel">
                    <div class="painel-topo">
                        <h2><?= h($e['nome']) ?></h2>
                        <div class="botoes-linha">
                            <a class="btn btn-secundario btn-pequeno" href="<?= url('montar.php?editar=' . $e['id']) ?>">Editar</a>
                            <button type="button" class="btn btn-secundario btn-pequeno texto-perigo"
                                    data-excluir="<?= $e['id'] ?>" data-nome="<?= h($e['nome']) ?>">Excluir</button>
                        </div>
                    </div>
                    <div class="grade-detalhe">
                        <?php foreach ($e['pokemon'] as $p): ?>
                            <?= card_pokemon($p, true) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
                // Só o que a análise precisa: nome e tipos de cada Pokémon.
                $paraAnalise = array_map(fn($p) => [
                    'nome'   => $p['nome'],
                    'imagem' => $p['imagem_url'],
                    'tipos'  => array_column($p['tipos'], 'slug'),
                ], $e['pokemon']);
                ?>
                <div class="analise" data-membros="<?= h(json_encode($paraAnalise, JSON_UNESCAPED_UNICODE)) ?>"></div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<!-- Modal: confirmar exclusão -->
<div class="modal" id="modal-excluir" hidden role="dialog" aria-modal="true">
    <div class="modal-caixa">
        <h3 class="modal-titulo">Excluir equipe</h3>
        <p class="modal-excluir-msg"></p>
        <div class="modal-botoes">
            <button type="button" class="btn btn-secundario texto-perigo" data-confirmar-excluir>Excluir</button>
            <button type="button" class="btn btn-secundario" data-cancelar-excluir>Cancelar</button>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/rodape.php'; ?>
