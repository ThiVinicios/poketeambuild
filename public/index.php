<?php
// Página inicial (landing page): apresenta o TeamBuild e leva para cadastro/login.

require_once __DIR__ . '/../includes/consultas.php';

$tituloPagina = 'Monte sua equipe Pokémon';
$paginaAtual = '';
require __DIR__ . '/../includes/cabecalho.php';
?>
<main>
    <!-- Faixa principal -->
    <section class="hero">
        <div class="container hero-grade">
            <div>
                <span class="etiqueta">Seu time. Sua estratégia.</span>
                <h1>Monte o time que <span class="destaque">vai mais longe.</span></h1>
                <p class="hero-texto">
                    Escolha entre os 151 Pokémon de Kanto, descubra o equilíbrio da sua equipe
                    e salve cada formação em um só lugar.
                </p>
                <div class="hero-botoes">
                    <?php if ($usuario): ?>
                        <a class="btn btn-primario" href="<?= url('montar.php') ?>">Montar minha equipe</a>
                    <?php else: ?>
                        <a class="btn btn-primario" href="<?= url('cadastro.php') ?>">Criar conta</a>
                        <a class="btn btn-secundario" href="<?= url('login.php') ?>">Entrar</a>
                    <?php endif; ?>
                    <a class="btn btn-secundario" href="<?= url('pokedex.php') ?>">Explorar Pokédex</a>
                </div>
            </div>
            <div class="hero-arte">
                <div class="hero-bola"></div>
                <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/6.png"
                     alt="Charizard" width="360" height="360">
            </div>
        </div>
    </section>

    <!-- O que o site oferece -->
    <section class="container secao">
        <span class="etiqueta-texto">Tudo que um treinador precisa</span>
        <h2>Planeje antes da batalha.</h2>
        <div class="grade-3">
            <article class="recurso">
                <span class="recurso-num">01</span>
                <h3>Montar Equipe</h3>
                <p>Crie e edite formações com até seis Pokémon. Suas equipes ficam salvas na sua conta.</p>
            </article>
            <article class="recurso">
                <span class="recurso-num">02</span>
                <h3>Pokédex de Kanto</h3>
                <p>Pesquise por nome, filtre por tipagem e consulte todos os 151 Pokémon da primeira geração.</p>
            </article>
            <article class="recurso">
                <span class="recurso-num">03</span>
                <h3>Análise de equipe</h3>
                <p>Veja as estatísticas da sua equipe e descubra fraquezas e resistências antes de lutar.</p>
            </article>
        </div>
    </section>

    <section class="chamada">
        <h2>Sua próxima equipe começa aqui.</h2>
        <p>Crie sua conta, escolha seus parceiros e entenda como cada tipo ajuda sua estratégia.</p>
        <a class="btn btn-primario" href="<?= url($usuario ? 'montar.php' : 'cadastro.php') ?>">Criar minha equipe</a>
    </section>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
