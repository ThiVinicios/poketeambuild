<?php
// Painel administrativo (só para administradores).
// Mostra números gerais, usuários, Pokémon e equipes, e trata as ações de excluir/mudar papel.

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/consultas.php';
require_once __DIR__ . '/../includes/funcoes.php';

$admin = exigir_admin(); // quem não é admin é redirecionado aqui mesmo
$pdo = pdo();

// ---------- Ações enviadas pelos botões da página ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $acao = $_POST['acao'] ?? '';
    $id = isset($_POST['id']) && ctype_digit((string) $_POST['id']) ? (int) $_POST['id'] : 0;

    try {
        if ($acao === 'papel' && $id !== (int) $admin['id']) {
            // Alterna entre usuário e administrador (não vale para a própria conta).
            $pdo->prepare("UPDATE usuarios SET papel = IF(papel = 'admin', 'usuario', 'admin') WHERE id = ?")->execute([$id]);
            flash('ok', 'Nível de acesso atualizado.');
        } elseif ($acao === 'excluir_usuario' && $id !== (int) $admin['id']) {
            $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
            flash('ok', 'Usuário excluído junto com as equipes dele.');
        } elseif ($acao === 'excluir_equipe') {
            $pdo->prepare('DELETE FROM equipes WHERE id = ?')->execute([$id]);
            flash('ok', 'Equipe excluída.');
        } elseif ($acao === 'excluir_pokemon') {
            $pdo->prepare('DELETE FROM pokemon WHERE id = ?')->execute([$id]);
            flash('ok', 'Pokémon excluído.');
        } else {
            flash('erro', 'Você não pode fazer isso com a própria conta.');
        }
    } catch (PDOException $e) {
        // Chave estrangeira: o Pokémon ainda está em alguma equipe.
        flash('erro', $e->getCode() === '23000'
            ? 'Esse Pokémon está em uso em equipes e não pode ser excluído.'
            : 'Não foi possível concluir a ação.');
    }
    redirecionar('admin.php');
}

// ---------- Dados da página ----------
$usuarios = $pdo->query('SELECT id, nome, email, papel, criado_em FROM usuarios ORDER BY criado_em DESC LIMIT 200')->fetchAll();
$pokemon = listar_pokemon();
$equipes = $pdo->query(
    'SELECT e.id, e.nome, u.nome AS dono, COUNT(ep.pokemon_id) AS total
       FROM equipes e
       JOIN usuarios u ON u.id = e.usuario_id
       LEFT JOIN equipe_pokemon ep ON ep.equipe_id = e.id
      GROUP BY e.id, e.nome, u.nome
      ORDER BY e.atualizado_em DESC LIMIT 200'
)->fetchAll();

$tituloPagina = 'Administração';
$paginaAtual = 'admin';
$scripts = ['admin.js'];
require __DIR__ . '/../includes/cabecalho.php';
require_once __DIR__ . '/../includes/cards.php';

// Pequeno atalho para os botões de ação (cada um é um mini formulário).
function botao_acao(string $acao, int $id, string $rotulo, string $confirmar = '', string $classe = ''): string
{
    return '<form method="post" class="form-linha"' . ($confirmar ? ' data-confirmar="' . h($confirmar) . '"' : '') . '>'
        . csrf_campo()
        . '<input type="hidden" name="acao" value="' . h($acao) . '">'
        . '<input type="hidden" name="id" value="' . $id . '">'
        . '<button class="btn-icone ' . h($classe) . '">' . h($rotulo) . '</button></form>';
}
?>
<main class="container pagina-admin">
    <span class="etiqueta-texto">Acesso restrito</span>
    <h1>Painel administrativo</h1>

    <div class="grade-3 indicadores">
        <div class="indicador"><b><?= count($usuarios) ?></b><span>Usuários cadastrados</span></div>
        <div class="indicador"><b><?= count($pokemon) ?></b><span>Pokémon cadastrados</span></div>
        <div class="indicador"><b><?= count($equipes) ?></b><span>Equipes criadas</span></div>
    </div>

    <div class="admin-grade">
        <section class="painel">
            <h2>Usuários</h2>
            <div class="tabela-rolagem">
                <table class="tabela">
                    <thead><tr><th>Nome</th><th>E-mail</th><th>Papel</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?= h($u['nome']) ?></td>
                            <td><?= h($u['email']) ?></td>
                            <td><span class="papel papel-<?= h($u['papel']) ?>"><?= h($u['papel']) ?></span></td>
                            <td class="acoes">
                                <?php if ((int) $u['id'] !== (int) $admin['id']): ?>
                                    <?= botao_acao('papel', (int) $u['id'], $u['papel'] === 'admin' ? 'Tornar usuário' : 'Tornar admin', $u['papel'] === 'admin' ? 'Tornar ' . $u['nome'] . ' um usuário comum? Ele perderá o acesso administrativo.' : 'Tornar ' . $u['nome'] . ' administrador?') ?>
                                    <?= botao_acao('excluir_usuario', (int) $u['id'], 'Excluir', 'Excluir ' . $u['nome'] . ' e todas as equipes dele?', 'perigo') ?>
                                <?php else: ?>
                                    <small>você</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="painel">
            <h2>Equipes de todos os usuários</h2>
            <?php if (!$equipes): ?>
                <p class="vazio-pequeno">Nenhuma equipe criada ainda.</p>
            <?php else: ?>
                <div class="tabela-rolagem">
                    <table class="tabela">
                        <thead><tr><th>Equipe</th><th>Treinador</th><th>Pokémon</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($equipes as $e): ?>
                            <tr>
                                <td><?= h($e['nome']) ?></td>
                                <td><?= h($e['dono']) ?></td>
                                <td><?= (int) $e['total'] ?>/6</td>
                                <td class="acoes"><?= botao_acao('excluir_equipe', (int) $e['id'], 'Excluir', 'Excluir a equipe "' . $e['nome'] . '" de ' . $e['dono'] . '? Essa ação não pode ser desfeita.', 'perigo') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <section class="painel">
        <div class="painel-topo">
            <h2>Catálogo de Pokémon</h2>
            <a class="btn btn-primario btn-pequeno" href="<?= url('admin_pokemon.php') ?>">Cadastrar Pokémon</a>
        </div>
        <div class="tabela-rolagem tabela-alta">
            <table class="tabela">
                <thead><tr><th></th><th>Nº</th><th>Nome</th><th>Tipos</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($pokemon as $p): ?>
                    <tr>
                        <td><img src="<?= h($p['imagem_url']) ?>" alt="" width="40" height="40" loading="lazy"></td>
                        <td>#<?= str_pad((string) $p['numero'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= h($p['nome']) ?></td>
                        <td><?= implode(' ', array_map('selo_tipo', $p['tipos'])) ?></td>
                        <td class="acoes">
                            <a class="btn-icone" href="<?= url('admin_pokemon.php?id=' . $p['id']) ?>">Editar</a>
                            <?= botao_acao('excluir_pokemon', $p['id'], 'Excluir', 'Excluir ' . $p['nome'] . ' do catálogo?', 'perigo') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<div class="modal" id="modal-admin-confirmacao" hidden role="dialog" aria-modal="true">
  <div class="modal-caixa">
    <h3 class="modal-titulo">Confirmar ação</h3>
    <p class="modal-admin-msg modal-incompleta-msg"></p>
    <div class="modal-botoes">
      <button type="button" class="btn btn-primario" data-admin-confirmar>Confirmar</button>
      <button type="button" class="btn btn-secundario" data-admin-cancelar>Cancelar</button>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
