<?php
// Montador de equipes: escolher até 6 Pokémon, dar um nome e salvar.
// Toda a interação (escolher, remover, salvar) é feita pelo montar.js,
// que conversa com api/equipes.php.

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/consultas.php';
require_once __DIR__ . '/../includes/cards.php';

$usuario = exigir_login();

$pokemon = listar_pokemon();
$minhasEquipes = listar_equipes((int) $usuario['id']);

// Se a URL tiver ?editar=ID, carregamos aquela equipe (só se for do próprio usuário).
$edicao = null;
if (isset($_GET['editar']) && ctype_digit((string) $_GET['editar'])) {
    foreach ($minhasEquipes as $e) {
        if ($e['id'] === (int) $_GET['editar']) {
            $edicao = ['id' => $e['id'], 'nome' => $e['nome'], 'pokemon' => array_column($e['pokemon'], 'id')];
        }
    }
}

$tituloPagina = 'Montar equipe';
$paginaAtual = 'montar';
$scripts = ['analise.js', 'montar.js'];
require __DIR__ . '/../includes/cabecalho.php';
?>
<main class="container pagina-montar">
    <div class="montar-grade">
        <section>
            <div class="painel">
                <div class="painel-topo">
                    <div>
                        <span class="etiqueta-texto" id="modo-edicao"><?= $edicao ? 'Editando equipe' : 'Nova equipe' ?></span>
                        <h1>Monte sua formação</h1>
                    </div>
                    <span class="contagem" id="contagem">0/6 escolhidos</span>
                </div>

                <input class="campo" id="nome-equipe" maxlength="60" placeholder="Nome da equipe (ex.: Campeões de Kanto)">
                <div class="slots" id="slots"></div>
                <p class="aviso" id="mensagem" hidden></p>

                <div class="botoes-linha">
                    <button type="button" class="btn btn-primario" id="salvar">Salvar equipe</button>
                    <button type="button" class="btn btn-secundario" id="cancelar" hidden>Cancelar edição</button>
                </div>
            </div>

            <div class="busca-montar">
                <input class="campo" type="search" id="busca" placeholder="Buscar Pokémon para adicionar..." autocomplete="off">
                <div class="grade-escolha" id="lista">
                    <?php foreach ($pokemon as $p): ?>
                        <button type="button" class="escolha"
                                data-id="<?= $p['id'] ?>"
                                data-nome="<?= h($p['nome']) ?>"
                                data-imagem="<?= h($p['imagem_url']) ?>"
                                data-tipos="<?= h(implode(' ', array_column($p['tipos'], 'slug'))) ?>">
                            <?= card_pokemon($p, true) ?>
                            <span class="marca">&#10003;</span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <p class="busca-sem-resultados" id="busca-sem-resultados" hidden>Nenhum Pokémon encontrado. Tente outro nome.</p>
            </div>
        </section>

        <aside class="lateral">
            <div class="analise" id="analise"></div>

            <div class="painel">
                <div class="painel-topo">
                    <h2>Minhas equipes</h2>
                    <a class="link-pequeno" href="<?= url('equipes.php') ?>">Ver detalhes</a>
                </div>
                <?php if (!$minhasEquipes): ?>
                    <p class="vazio-pequeno">Você ainda não salvou equipes.</p>
                <?php endif; ?>
                <?php foreach ($minhasEquipes as $e): ?>
                    <article class="mini-equipe">
                        <div class="mini-equipe-topo">
                            <div>
                                <h3><?= h($e['nome']) ?></h3>
                            </div>
                            <div class="mini-acoes">
                                <a class="btn btn-secundario btn-pequeno" href="<?= url('montar.php?editar=' . $e['id']) ?>" title="Editar">Editar</a>
                                <button type="button" class="btn btn-secundario btn-pequeno texto-perigo" data-excluir="<?= $e['id'] ?>" data-nome="<?= h($e['nome']) ?>">Excluir</button>
                            </div>
                        </div>
                        <div class="mini-fotos">
                            <?php foreach ($e['pokemon'] as $p): ?>
                                <img src="<?= h($p['imagem_url']) ?>" alt="<?= h($p['nome']) ?>" width="36" height="36">
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>
</main>

<!-- Dados que o JavaScript precisa (equipe em edição). Fica em JSON para não misturar com o código. -->
<script type="application/json" id="dados-edicao"><?= json_encode($edicao, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>

<!-- Modal: equipe incompleta -->
<div class="modal" id="modal-incompleta" hidden role="dialog" aria-modal="true">
    <div class="modal-caixa">
        <h3 class="modal-titulo">⚠️ Equipe incompleta</h3>
        <p class="modal-incompleta-msg"></p>
        <div class="modal-botoes">
            <button type="button" class="btn btn-primario" data-confirmar>Salvar mesmo assim</button>
            <button type="button" class="btn btn-secundario" data-cancelar>Voltar</button>
        </div>
    </div>
</div>


<!-- Modal: sair da edição sem salvar -->
<div class="modal" id="modal-navegacao" hidden role="dialog" aria-modal="true">
  <div class="modal-caixa">
    <h3 class="modal-titulo">Equipe não salva</h3>
    <p class="modal-incompleta-msg">Você possui Pokémon na equipe em edição. Se sair agora, as alterações não salvas serão perdidas.</p>
    <div class="modal-botoes">
      <button type="button" class="btn btn-secundario" data-continuar-editando>Continuar editando</button>
      <button type="button" class="btn btn-primario" data-ignorar-navegacao>Ignorar</button>
    </div>
  </div>
</div>

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
