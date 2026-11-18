<?php
// Tela de cadastro. Valida os dados, guarda a senha como hash e já deixa a pessoa logada.

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/funcoes.php';

if (usuario_logado()) {
    redirecionar('montar.php');
}

$erro = '';
$nome = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_csrf();
    $nome = campo('nome', 80);
    $email = strtolower(campo('email', 160));
    $senha = $_POST['senha'] ?? '';

    // Mesmas regras do JavaScript, repetidas aqui porque o navegador pode ser burlado.
    if (tamanho($nome) < 2) {
        $erro = 'O nome precisa ter pelo menos 2 letras.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } elseif (!is_string($senha) || strlen($senha) < 8 || strlen($senha) > 72) {
        $erro = 'A senha precisa ter de 8 a 72 caracteres.';
    } else {
        try {
            // O papel é sempre "usuario": ninguém vira admin pelo cadastro.
            $stmt = pdo()->prepare('INSERT INTO usuarios (nome, email, senha_hash, papel) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), 'usuario']);

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) pdo()->lastInsertId();
            redirecionar('montar.php');
        } catch (PDOException $e) {
            // 23000 = violação de chave única, ou seja, o e-mail já existe.
            if ($e->getCode() === '23000') {
                $erro = 'Esse e-mail já está cadastrado.';
            } else {
                throw $e;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar conta | TeamBuild</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="<?= url('assets/css/estilo.css') ?>">
</head>
<body class="pagina-auth">
<main class="auth auth-invertido">
    <section class="auth-arte auth-arte-vermelha">
        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/3.png" alt="Venusaur" width="430" height="430">
    </section>
    <section class="auth-form">
        <a class="logo" href="<?= url('index.php') ?>">
            <span class="pokebola" aria-hidden="true"><span></span></span>
            <span>Team<b>Build</b></span>
        </a>
        <div class="auth-caixa">
            <span class="etiqueta-texto">Novo treinador</span>
            <h1>Crie sua conta</h1>
            <p class="auth-sub">Comece a montar e salvar suas equipes.</p>
            <?php if ($erro): ?><p class="aviso aviso-erro"><?= h($erro) ?></p><?php endif; ?>
            <form method="post" novalidate id="form-cadastro">
                <?= csrf_campo() ?>
                <label>Nome
                    <input class="campo" name="nome" value="<?= h($nome) ?>" required minlength="2" maxlength="80" placeholder="Como quer ser chamado?">
                </label>
                <label>E-mail
                    <input class="campo" type="email" name="email" value="<?= h($email) ?>" required maxlength="160" autocomplete="email" placeholder="treinador@email.com">
                </label>
                <label>Senha
                    <input class="campo" type="password" name="senha" required minlength="8" maxlength="72" autocomplete="new-password" placeholder="Mínimo de 8 caracteres">
                </label>
                <p class="erro-js" id="erro-js" hidden></p>
                <button class="btn btn-primario btn-total" type="submit">Criar conta gratuita</button>
            </form>
            <p class="auth-rodape">Já tem conta? <a href="<?= url('login.php') ?>">Entrar</a></p>
        </div>
    </section>
</main>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/formularios.js') ?>"></script>
</body>
</html>
