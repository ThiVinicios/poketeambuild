<?php
// Pokédex: mostra os 151 Pokémon. A busca e o filtro por tipo funcionam no navegador (pokedex.js).

require_once __DIR__ . '/../includes/consultas.php';
require_once __DIR__ . '/../includes/cards.php';

$pokemon = listar_pokemon();
$tipos = listar_tipos();

$tituloPagina = 'Pokédex';
$paginaAtual = 'pokedex';
$scripts = ['pokedex.js'];
require __DIR__ . '/../includes/cabecalho.php';
?>
<main>
    <section class="cabecalho-pagina">
        <div class="container">
            <span class="etiqueta-texto">Região de Kanto</span>
            <h1>Pokédex Nacional</h1>
            <p>Conheça os 151 Pokémon da primeira geração. Pesquise por nome ou encontre o tipo ideal para sua equipe.</p>
        </div>
    </section>

    <?php if (!$pokemon): ?>
        <div class="container vazio"><p>O catálogo ainda não possui Pokémon cadastrados.</p></div>
    <?php else: ?>
        <div class="filtros">
            <div class="container">
                <div class="filtros-linha">
                    <input class="campo" type="search" id="busca" placeholder="Buscar Pokémon pelo nome..." autocomplete="off">
                    <div class="contador"><span id="contador"><?= count($pokemon) ?></span> resultados</div>
                </div>
                <div class="chips" id="chips">
                    <button type="button" class="chip ativo" data-tipo="todos">Todos</button>
                    <?php foreach ($tipos as $t): ?>
                        <button type="button" class="chip" data-tipo="<?= h($t['slug']) ?>"><?= h($t['nome']) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="container lista-pokedex">
            <div class="grade-pokemon" id="lista">
                <?php foreach ($pokemon as $p): ?>
                    <div class="item-pokemon"
                         data-nome="<?= h(strtolower($p['nome'])) ?>"
                         data-tipos="<?= h(implode(' ', array_column($p['tipos'], 'slug'))) ?>">
                        <?= card_pokemon($p) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="vazio" id="sem-resultado" hidden>
                <p><b>Nenhum Pokémon encontrado.</b></p>
                <p>Tente outro nome ou filtro.</p>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
