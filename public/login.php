<?php
// Tela de login. Confere e-mail e senha (com password_verify) e abre a sessão.

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/funcoes.php';

if (usuario_logado()) {
    redirecionar('montar.php');
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $email = strtolower(campo('email', 160));
    $senha = $_POST['senha'] ?? '';

    // Validação básica dos campos antes de ir ao banco.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($senha) || $senha === '') {
        $erro = 'Informe um e-mail e uma senha válidos.';
    } else {
        $stmt = pdo()->prepare('SELECT id, senha_hash FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $linha = $stmt->fetch();

        // A mesma mensagem vale para e-mail errado ou senha errada (não revela qual deu erro).
        if ($linha && password_verify($senha, $linha['senha_hash'])) {
            session_regenerate_id(true); // evita fixação de sessão
            $_SESSION['usuario_id'] = (int) $linha['id'];
            redirecionar('montar.php');
        }
        $erro = 'E-mail ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | TeamBuild</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?= url('assets/css/estilo.css') ?>">
</head>
<body class="pagina-auth">
<main class="auth">
    <section class="auth-form">
        <a class="logo" href="<?= url('index.php') ?>">
            <span class="pokebola" aria-hidden="true"><span></span></span>
            <span>Team<b>Build</b></span>
        </a>
        <div class="auth-caixa">
            <span class="etiqueta-texto">Bem-vindo de volta</span>
            <h1>Entre na sua conta</h1>
            <p class="auth-sub">Continue de onde parou e aperfeiçoe suas equipes.</p>
            <?php if ($erro): ?><p class="aviso aviso-erro"><?= h($erro) ?></p><?php endif; ?>
            <form method="post" novalidate id="form-login">
                <?= csrf_campo() ?>
                <label>E-mail
                    <input class="campo" type="email" name="email" value="<?= h($email) ?>" required maxlength="160" autocomplete="email" placeholder="treinador@email.com">
                </label>
                <label>Senha
                    <input class="campo" type="password" name="senha" required minlength="8" autocomplete="current-password" placeholder="Sua senha">
                </label>
                <p class="erro-js" id="erro-js" hidden></p>
                <button class="btn btn-primario btn-total" type="submit">Entrar</button>
            </form>
            <p class="auth-rodape">Ainda não tem conta? <a href="<?= url('cadastro.php') ?>">Cadastre-se</a></p>
        </div>
    </section>
    <section class="auth-arte auth-arte-escura">
        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/9.png" alt="Blastoise" width="430" height="430">
    </section>
</main>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/formularios.js') ?>"></script>
</body>
</html>
