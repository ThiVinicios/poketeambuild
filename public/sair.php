<?php
// Encerra a sessão. Só aceita POST (vem do botão "Sair" da janela de confirmação).

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/funcoes.php';

exigir_csrf();

$_SESSION = [];
session_destroy();
redirecionar('index.php');
