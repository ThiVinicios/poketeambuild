<?php
// Desenha o cartão de um Pokémon (imagem, número, nome e tipos).
// Usado na Pokédex, no montador de equipes e na página de equipes.

function selo_tipo(array $tipo): string
{
    return '<span class="tipo tipo-' . h($tipo['slug']) . '">' . h($tipo['nome']) . '</span>';
}

function card_pokemon(array $p, bool $compacto = false): string
{
    $numero = str_pad((string) $p['numero'], 3, '0', STR_PAD_LEFT);
    // Garante a tipagem histórica de Clefairy e Clefable também em bancos
    // que ainda não receberam a migração correspondente.
    if (in_array((int) $p['numero'], [35, 36], true)) {
        $temNormal = array_filter($p['tipos'], fn($tipo) => $tipo['slug'] === 'normal');
        if (!$temNormal) {
            array_unshift($p['tipos'], ['slug' => 'normal', 'nome' => 'Normal']);
        }
    }
    $selos = implode('', array_map('selo_tipo', $p['tipos']));
    $classe = $compacto ? 'card-pokemon compacto' : 'card-pokemon';
    if (in_array((int) $p['numero'], [35, 36, 39, 40, 81, 82, 122], true)) {
        $classe .= ' tipagens-centralizadas';
    }

    return '<article class="' . $classe . '">'
        . '<span class="card-numero">#' . $numero . '</span>'
        . '<div class="card-foto"><img src="' . h($p['imagem_url']) . '" alt="' . h($p['nome']) . '" loading="lazy"></div>'
        . '<h3>' . h($p['nome']) . '</h3>'
        . '<div class="card-tipos">' . $selos . '</div>'
        . '</article>';
}
