<?php
// API das equipes (chamada pelo JavaScript). Aceita POST com JSON:
//   { "acao": "salvar",  "id": 3 (opcional), "nome": "...", "pokemon": [1, 4, 7] }
//   { "acao": "excluir", "id": 3 }
// Toda validação é refeita aqui, mesmo que o JavaScript já tenha validado.

require_once __DIR__ . '/../../includes/sessao.php';
require_once __DIR__ . '/../../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'mensagem' => 'Método não permitido.'], 405);
}

$usuario = usuario_logado();
if (!$usuario) {
    responder_json(['ok' => false, 'mensagem' => 'Sessão expirada. Entre novamente.'], 401);
}
if (!csrf_valido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responder_json(['ok' => false, 'mensagem' => 'Requisição inválida. Recarregue a página.'], 400);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    responder_json(['ok' => false, 'mensagem' => 'Dados inválidos.'], 400);
}

$pdo = pdo();
$acao = $entrada['acao'] ?? '';
$equipeId = isset($entrada['id']) && is_numeric($entrada['id']) ? (int) $entrada['id'] : null;

// ---------- EXCLUIR ----------
if ($acao === 'excluir') {
    // Filtra por usuario_id: ninguém apaga a equipe de outra pessoa.
    $stmt = $pdo->prepare('DELETE FROM equipes WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$equipeId, $usuario['id']]);
    if ($stmt->rowCount() === 0) {
        responder_json(['ok' => false, 'mensagem' => 'Equipe não encontrada.'], 404);
    }
    responder_json(['ok' => true, 'mensagem' => 'Equipe excluída.']);
}

// ---------- SALVAR (criar ou editar) ----------
if ($acao !== 'salvar') {
    responder_json(['ok' => false, 'mensagem' => 'Ação desconhecida.'], 400);
}

$nome = trim((string) ($entrada['nome'] ?? ''));
if (tamanho($nome) < 2 || tamanho($nome) > 60) {
    responder_json(['ok' => false, 'mensagem' => 'Dê um nome à equipe (2 a 60 letras).'], 422);
}

// Só aceita números inteiros, sem repetir. É aqui que a regra dos 6 Pokémon é garantida.
$ids = [];
foreach ((array) ($entrada['pokemon'] ?? []) as $valor) {
    if (is_int($valor) || (is_string($valor) && ctype_digit($valor))) {
        $ids[(int) $valor] = (int) $valor;
    }
}
$ids = array_values($ids);
if (count($ids) < 1 || count($ids) > 6) {
    responder_json(['ok' => false, 'mensagem' => 'Escolha entre 1 e 6 Pokémon.'], 422);
}

// Todos os Pokémon enviados precisam existir no banco.
$marcas = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pokemon WHERE id IN ($marcas)");
$stmt->execute($ids);
if ((int) $stmt->fetchColumn() !== count($ids)) {
    responder_json(['ok' => false, 'mensagem' => 'Pokémon inválido na seleção.'], 422);
}

try {
    // Transação: ou salva tudo, ou não salva nada.
    $pdo->beginTransaction();

    if ($equipeId !== null) {
        // Edição: confere se a equipe é mesmo do usuário logado.
        $stmt = $pdo->prepare('SELECT id FROM equipes WHERE id = ? AND usuario_id = ? FOR UPDATE');
        $stmt->execute([$equipeId, $usuario['id']]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            responder_json(['ok' => false, 'mensagem' => 'Equipe não encontrada.'], 404);
        }
        $pdo->prepare('UPDATE equipes SET nome = ? WHERE id = ?')->execute([$nome, $equipeId]);
        $pdo->prepare('DELETE FROM equipe_pokemon WHERE equipe_id = ?')->execute([$equipeId]);
    } else {
        $pdo->prepare('INSERT INTO equipes (usuario_id, nome) VALUES (?, ?)')->execute([$usuario['id'], $nome]);
        $equipeId = (int) $pdo->lastInsertId();
    }

    $insere = $pdo->prepare('INSERT INTO equipe_pokemon (equipe_id, posicao, pokemon_id) VALUES (?, ?, ?)');
    foreach ($ids as $i => $pokemonId) {
        $insere->execute([$equipeId, $i + 1, $pokemonId]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder_json(['ok' => false, 'mensagem' => 'Não foi possível salvar a equipe.'], 500);
}

responder_json(['ok' => true, 'mensagem' => 'Equipe salva com sucesso.', 'id' => $equipeId]);
