<?php
// Formulário do admin para cadastrar um Pokémon novo ou editar um existente (?id=...).

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/consultas.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigir_admin();
$pdo = pdo();
$tipos = listar_tipos();

// Se veio ?id, estamos editando: carregamos os dados atuais.
$id = isset($_GET['id']) && ctype_digit((string) $_GET['id']) ? (int) $_GET['id'] : null;
$atual = $id ? buscar_pokemon($id) : null;
if ($id && !$atual) {
    flash('erro', 'Pokémon não encontrado.');
    redirecionar('admin.php');
}

// Valores que aparecem nos campos (do banco ou do que a pessoa digitou).
$form = [
    'numero' => $atual['numero'] ?? '',
    'nome'   => $atual['nome'] ?? '',
    'imagem' => $atual['imagem_url'] ?? '',
    'tipo1'  => $atual['tipos'][0]['slug'] ?? '',
    'tipo2'  => $atual['tipos'][1]['slug'] ?? '',
];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $form = [
        'numero' => campo('numero', 4),
        'nome'   => campo('nome', 60),
        'imagem' => campo('imagem', 255),
        'tipo1'  => campo('tipo1', 20),
        'tipo2'  => campo('tipo2', 20),
    ];

    // Mapa slug -> id dos tipos válidos.
    $mapaTipos = array_column($tipos, 'id', 'slug');

    if (!ctype_digit($form['numero']) || (int) $form['numero'] < 1 || (int) $form['numero'] > 9999) {
        $erro = 'O número precisa estar entre 1 e 9999.';
    } elseif (tamanho($form['nome']) < 2) {
        $erro = 'Informe o nome do Pokémon.';
    } elseif (!filter_var($form['imagem'], FILTER_VALIDATE_URL) || !str_starts_with($form['imagem'], 'https://')) {
        $erro = 'A imagem precisa ser um link que comece com https://';
    } elseif (!isset($mapaTipos[$form['tipo1']])) {
        $erro = 'Escolha o tipo principal.';
    } elseif ($form['tipo2'] !== '' && (!isset($mapaTipos[$form['tipo2']]) || $form['tipo2'] === $form['tipo1'])) {
        $erro = 'O tipo secundário é inválido ou igual ao principal.';
    } else {
        try {
            $pdo->beginTransaction();
            if ($atual) {
                $pdo->prepare('UPDATE pokemon SET numero = ?, nome = ?, imagem_url = ? WHERE id = ?')
                    ->execute([(int) $form['numero'], $form['nome'], $form['imagem'], $atual['id']]);
                $pokemonId = $atual['id'];
                $pdo->prepare('DELETE FROM pokemon_tipos WHERE pokemon_id = ?')->execute([$pokemonId]);
            } else {
                $pdo->prepare('INSERT INTO pokemon (numero, nome, imagem_url) VALUES (?, ?, ?)')
                    ->execute([(int) $form['numero'], $form['nome'], $form['imagem']]);
                $pokemonId = (int) $pdo->lastInsertId();
            }

            $liga = $pdo->prepare('INSERT INTO pokemon_tipos (pokemon_id, tipo_id, ordem) VALUES (?, ?, ?)');
            $liga->execute([$pokemonId, $mapaTipos[$form['tipo1']], 1]);
            if ($form['tipo2'] !== '') {
                $liga->execute([$pokemonId, $mapaTipos[$form['tipo2']], 2]);
            }
            $pdo->commit();

            flash('ok', $atual ? 'Pokémon atualizado.' : 'Pokémon cadastrado.');
            redirecionar('admin.php');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erro = $e->getCode() === '23000'
                ? 'Já existe um Pokémon com esse número ou nome.'
                : 'Não foi possível salvar o Pokémon.';
        }
    }
}

$tituloPagina = $atual ? 'Editar Pokémon' : 'Cadastrar Pokémon';
$paginaAtual = 'admin';
require __DIR__ . '/../includes/cabecalho.php';
?>
<main class="container pagina-admin">
    <a class="link-pequeno" href="<?= url('admin.php') ?>">&larr; Voltar ao painel</a>
    <h1><?= h($tituloPagina) ?></h1>

    <div class="painel formulario-admin">
        <?php if ($erro): ?><p class="aviso aviso-erro"><?= h($erro) ?></p><?php endif; ?>
        <form method="post" novalidate<?= $atual ? ' data-confirmar="Salvar as alterações deste Pokémon?"' : '' ?>>
            <?= csrf_campo() ?>
            <label>Número na Pokédex
                <input class="campo" type="number" name="numero" min="1" max="9999" required value="<?= h((string) $form['numero']) ?>">
            </label>
            <label>Nome
                <input class="campo" name="nome" maxlength="60" required value="<?= h($form['nome']) ?>">
            </label>
            <label>Link da imagem (https://...)
                <input class="campo" type="url" name="imagem" maxlength="255" required value="<?= h($form['imagem']) ?>" placeholder="https://.../sprite.png">
            </label>
            <label>Tipo principal
                <select class="campo" name="tipo1" required>
                    <option value="">Escolha...</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= h($t['slug']) ?>" <?= $form['tipo1'] === $t['slug'] ? 'selected' : '' ?>><?= h($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tipo secundário (opcional)
                <select class="campo" name="tipo2">
                    <option value="">Nenhum</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= h($t['slug']) ?>" <?= $form['tipo2'] === $t['slug'] ? 'selected' : '' ?>><?= h($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="btn btn-primario" type="submit"><?= $atual ? 'Salvar alterações' : 'Cadastrar Pokémon' ?></button>
        </form>
    </div>
</main>
<?php if ($atual): ?>
<div class="modal" id="modal-admin-confirmacao" hidden role="dialog" aria-modal="true">
  <div class="modal-caixa">
    <h3 class="modal-titulo">Confirmar alterações</h3>
    <p class="modal-admin-msg modal-incompleta-msg">Deseja salvar as alterações deste Pokémon?</p>
    <div class="modal-botoes">
      <button type="button" class="btn btn-primario" data-admin-confirmar>Salvar alterações</button>
      <button type="button" class="btn btn-secundario" data-admin-cancelar>Cancelar</button>
    </div>
  </div>
</div>
<script src="<?= h(url('assets/js/admin.js')) ?>" defer></script>
<?php endif; ?>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
