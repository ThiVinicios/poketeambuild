<?php
// Final comum das páginas: janela de confirmação para sair e scripts.
// A página pode definir $scripts = ['arquivo.js'] para carregar JS próprio.
$scripts = $scripts ?? [];
?>
<?php if (!empty($usuario)): ?>
    <!-- Janela que pergunta se a pessoa realmente quer sair da conta -->
    <div class="modal" id="modal-sair" hidden>
        <div class="modal-caixa" role="dialog" aria-modal="true" aria-labelledby="titulo-sair">
            <h2 id="titulo-sair">Deseja sair da conta?</h2>
            <p>Você precisará entrar de novo para acessar suas equipes.</p>
            <form method="post" action="<?= url('sair.php') ?>" class="modal-botoes">
                <?= csrf_campo() ?>
                <button type="button" class="btn btn-secundario" id="cancelar-sair">Cancelar</button>
                <button type="submit" class="btn btn-primario">Sair</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script src="<?= url('assets/js/app.js') ?>"></script>
<?php foreach ($scripts as $arquivo): ?>
    <script src="<?= url('assets/js/' . $arquivo) ?>"></script>
<?php endforeach; ?>
</body>
</html>
