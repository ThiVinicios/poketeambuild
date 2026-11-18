<?php
// Consultas ao banco que se repetem em várias páginas.

require_once __DIR__ . '/conexao.php';

// Lista de todos os tipos (usada nos filtros e nos formulários do admin).
// Lista exclusivamente os 15 tipos da primeira geração.
function listar_tipos(): array
{
    // Exibe exclusivamente os 15 tipos existentes na 1ª geração, mesmo se o banco
    // ainda possuir registros antigos de tipos adicionados posteriormente.
    return pdo()->query(
        "SELECT id, slug, nome FROM tipos
         WHERE slug IN ('normal','fire','water','electric','grass','ice','fighting','poison','ground','flying','psychic','bug','rock','ghost','dragon')
         ORDER BY id"
    )->fetchAll();
}

// Devolve os Pokémon com a lista de tipos de cada um.
// Cada item: id, numero, nome, imagem_url, tipos (array de [slug, nome]).
function listar_pokemon(): array
{
    $linhas = pdo()->query(
        'SELECT p.id, p.numero, p.nome, p.imagem_url, t.slug, t.nome AS tipo_nome
           FROM pokemon p
           JOIN pokemon_tipos pt ON pt.pokemon_id = p.id
           JOIN tipos t ON t.id = pt.tipo_id
          ORDER BY p.numero, pt.ordem'
    )->fetchAll();

    $lista = [];
    foreach ($linhas as $l) {
        $id = $l['id'];
        if (!isset($lista[$id])) {
            $lista[$id] = [
                'id'         => (int) $id,
                'numero'     => (int) $l['numero'],
                'nome'       => $l['nome'],
                'imagem_url' => $l['imagem_url'],
                'tipos'      => [],
            ];
        }
        $lista[$id]['tipos'][] = ['slug' => $l['slug'], 'nome' => $l['tipo_nome']];
    }
    return array_values($lista);
}

// Um Pokémon só (usado na edição do admin). Retorna null se não existir.
function buscar_pokemon(int $id): ?array
{
    foreach (listar_pokemon() as $p) {
        if ($p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

// Equipes de um usuário já com os Pokémon de cada uma (na ordem das posições).
// Se $equipeId for passado, traz só aquela equipe.
function listar_equipes(int $usuarioId, ?int $equipeId = null): array
{
    $sql = 'SELECT id, nome FROM equipes WHERE usuario_id = ?';
    $params = [$usuarioId];
    if ($equipeId !== null) {
        $sql .= ' AND id = ?';
        $params[] = $equipeId;
    }
    $stmt = pdo()->prepare($sql . ' ORDER BY atualizado_em DESC');
    $stmt->execute($params);
    $equipes = $stmt->fetchAll();

    if (!$equipes) {
        return [];
    }

    // Pega todos os membros de todas as equipes numa consulta só.
    $ids = array_column($equipes, 'id');
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt = pdo()->prepare(
        "SELECT ep.equipe_id, p.id, p.numero, p.nome, p.imagem_url, t.slug, t.nome AS tipo_nome
           FROM equipe_pokemon ep
           JOIN pokemon p ON p.id = ep.pokemon_id
           JOIN pokemon_tipos pt ON pt.pokemon_id = p.id
           JOIN tipos t ON t.id = pt.tipo_id
          WHERE ep.equipe_id IN ($marcas)
          ORDER BY ep.equipe_id, ep.posicao, pt.ordem"
    );
    $stmt->execute($ids);

    $membros = [];
    foreach ($stmt->fetchAll() as $l) {
        $eid = $l['equipe_id'];
        $pid = $l['id'];
        if (!isset($membros[$eid][$pid])) {
            $membros[$eid][$pid] = [
                'id'         => (int) $pid,
                'numero'     => (int) $l['numero'],
                'nome'       => $l['nome'],
                'imagem_url' => $l['imagem_url'],
                'tipos'      => [],
            ];
        }
        $membros[$eid][$pid]['tipos'][] = ['slug' => $l['slug'], 'nome' => $l['tipo_nome']];
    }

    foreach ($equipes as &$e) {
        $e['id'] = (int) $e['id'];
        $e['pokemon'] = array_values($membros[$e['id']] ?? []);
    }
    return $equipes;
}
