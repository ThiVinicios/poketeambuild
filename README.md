# TeamBuild (PHP + MySQL)

Site para montar equipes Pokémon. Feito com PHP, HTML, CSS, JavaScript e MySQL.

## O que tem

- Landing page, cadastro e login (senha com hash)
- Dois níveis: `usuario` e `admin`
- Pokédex com os 151 Pokémon, busca por nome e filtro por tipo
- Montador de equipes (até 6 Pokémon), editar e excluir
- Análise da equipe: tipos, fraquezas, resistências e avaliação
- Painel admin: usuários, equipes e CRUD de Pokémon

## Estrutura de pastas

```
teambuild-php/
├── config/config.php        <- ÚNICO arquivo que você edita (senha do MySQL)
├── database/
│   ├── teambuild.sql        <- cria o banco, as tabelas e os dados
│   └── instalar.php         <- roda o .sql sem precisar de Workbench/phpMyAdmin
├── includes/                <- código PHP reutilizado pelas páginas
│   ├── conexao.php          (conexão PDO)
│   ├── sessao.php           (login, papéis, CSRF)
│   ├── funcoes.php          (h(), url(), redirecionar()...)
│   ├── consultas.php        (consultas SQL repetidas)
│   ├── cards.php            (desenho do card de Pokémon)
│   ├── cabecalho.php        (topo + menu)
│   └── rodape.php           (janela de sair + scripts)
└── public/                  <- raiz do site (o servidor aponta para cá)
    ├── index.php            landing page
    ├── login.php, cadastro.php, sair.php
    ├── pokedex.php
    ├── montar.php           montador de equipes
    ├── equipes.php          minhas equipes + análise
    ├── admin.php            painel admin
    ├── admin_pokemon.php    cadastrar/editar Pokémon
    ├── api/equipes.php      salvar/excluir equipes (usado pelo JS)
    └── assets/
        ├── css/estilo.css   todo o visual
        └── js/              pokedex.js, montar.js, equipes.js, analise.js...
```

## Passo a passo no Windows + VS Code

### 1. Levar o projeto para o VS Code
1. No Figma Make, baixe o código do projeto (Export / Download code).
2. Descompacte.
3. Copie **só a pasta `teambuild-php`** para onde quiser (ex.: Área de Trabalho).
4. VS Code: **File > Open Folder** > escolha `teambuild-php`.
5. Abra o terminal: **Terminal > New Terminal**.

### 2. Instalar o PHP
No PowerShell:

```powershell
winget install PHP.PHP.8.3
```

Feche e abra o VS Code. Depois ative a extensão do MySQL:
y
```powershell
$dir = (Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Recurse -Filter php.exe | Select-Object -First 1).DirectoryName
Copy-Item "$dir\php.ini-development" "$dir\php.ini"
(Get-Content "$dir\php.ini") -replace ';extension_dir = "ext"','extension_dir = "ext"' -replace ';extension=pdo_mysql','extension=pdo_mysql' | Set-Content "$dir\php.ini"
php -m | findstr pdo_mysql
```

Se aparecer `pdo_mysql`, está certo.

> Atalho: se você tem o XAMPP instalado, use `C:\xampp\php\php.exe` no lugar de `php` nos comandos abaixo. Não precisa ligar o Apache.

### 3. Configurar a senha do MySQL
Abra `config/config.php` e coloque a senha do seu MySQL em `db_senha`.
Usuário padrão: `root`. Porta padrão: `3306`.

### 4. Criar o banco
No terminal do VS Code, dentro da pasta do projeto:

```powershell
php database/instalar.php
```

Deve aparecer: `Pronto! 151 Pokémon e 18 tipos cadastrados.`

### 5. Rodar o site
```powershell
php -S localhost:8000 -t public
```

Abra http://localhost:8000

Para parar: `Ctrl + C` no terminal.

### 6. Entrar como administrador
- E-mail: `admin@teambuild.com`
- Senha: `Admin@123`

Troque essa senha: crie sua conta no site, depois no painel admin use **Tornar admin** nela e exclua o admin padrão.

## Ver o banco sem phpMyAdmin
Use a extensão **Database Client** (autor: cweijan) no VS Code, ou o **MySQL Workbench**.
Conexão: host `127.0.0.1`, porta `3306`, usuário `root`, banco `teambuild`.

## Regras de segurança já aplicadas
- SQL: só consultas preparadas (PDO)
- XSS: toda saída passa por `h()` (htmlspecialchars)
- CSRF: token em todo formulário e na API
- Senhas: `password_hash` / `password_verify`
- Sessão: cookie HttpOnly, `session_regenerate_id` no login
- Limite de 6 Pokémon: validado no JS, na API e no banco (`CHECK` + chave primária)
- Cada usuário só mexe nas próprias equipes

## Apresentar em outro lugar
**Outro computador:** instale PHP e MySQL, copie a pasta, edite `config/config.php` e rode os passos 4 e 5.

**Hospedagem com PHP + MySQL:**
1. Crie o banco no painel da hospedagem.
2. Importe `database/teambuild.sql` pelo painel (remova as 2 primeiras linhas `CREATE DATABASE` e `USE`, se o banco já existir com outro nome).
3. Envie as pastas para o servidor. A pasta `public` deve ser a raiz do site.
4. Edite `config/config.php` com os dados do banco da hospedagem.

**Se o site ficar numa subpasta** (ex.: `http://localhost/teambuild/public`), mude `base_url` em `config/config.php`.

## Problemas comuns
| Erro | Solução |
|---|---|
| `could not find driver` | `pdo_mysql` não está ativo. Refaça o passo 2. |
| `Access denied for user 'root'` | Senha errada em `config/config.php`. |
| `Connection refused` | MySQL desligado. Inicie o serviço **MySQL** do Windows. |
| `php` não reconhecido | Feche e reabra o VS Code após instalar. |
| Imagens não aparecem | Sem internet. Os sprites vêm da PokéAPI. |


## Atualizar uma instalação existente para a 1ª geração

Se o banco `teambuild` já estiver instalado, execute **uma única vez** o arquivo
`database/atualizar_gen1.sql` no próprio banco pelo phpMyAdmin ou MySQL Workbench.
Essa migração remove Aço, Fada e Sombrio, corrige as tipagens incompatíveis com a
Geração I e atualiza apenas as imagens padrão da PokéAPI. Ela não apaga usuários,
equipes ou URLs de imagens personalizadas.

Em uma instalação nova, basta importar o `database/teambuild.sql` atualizado.
