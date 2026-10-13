# Projeto Integrador SENAC — Repositório Base em PHP

<img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="32" alt="PHP"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" width="32" alt="HTML5"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" width="32" alt="CSS3"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" width="32" alt="JavaScript"> <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg" width="32" alt="MySQL">

Repositório de partida do **Projeto Integrador** (Curso de Qualificação Profissional em Programador Web — SENAC). Já vem com a estrutura de pastas e a conexão com o banco prontas, para o grupo focar no que importa: **modelar o banco de dados** e **desenvolver as regras de negócio**.

## Sobre o projeto

Cada grupo escolhe um tema gerador (um problema real) e desenvolve um sistema web em:

Não usamos MVC nem sistema de rotas. A separação é simples:

| Pasta | Responsabilidade                                                                           |
|---|--------------------------------------------------------------------------------------------|
| `views` | O que o usuário vê (HTML)                                                                  |
| `controllers` | Processa dados enviados pelo usuário                                                       |
| `core` | Recursos reaproveitados (conexão, funções auxiliares, validação, upload de arquivos, etc.) |

Cada arquivo em `views`/`controllers` é acessado direto pela sua URL — a "rota" é o caminho do arquivo no servidor.

## Estrutura de pastas

```
projeto-integrador/
│
├── assets/
│   ├── css/style.css
│   └── js/script.js
│
├── config/
│   └── config.php
│
├── controllers/
│
├── database/
│
├── core/
│   ├── autenticacao.php
│   ├── conexao.php
│   ├── flash.php
│   ├── upload.php
│   └── validacao.php
│
├── views/
│
├── index.php
└── README.md
```

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" width="20" valign="middle"> `assets/`
Só estático: `css/style.css` e `js/script.js`. Nenhum PHP aqui.

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="20" valign="middle"> `config/config.php`
Dados de acesso ao banco (host, nome, usuário, senha) separados da conexão. Trocou de servidor? Só edita esse arquivo.

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="20" valign="middle"> `controllers/`
Processa dados de formulário (`$_POST`) ou link (`$_GET`). Sem HTML: recebe, valida, executa no banco e redireciona pra uma `view`.

```
controllers/
└── vagas/
    ├── cadastrar.php
    ├── editar.php
    └── excluir.php
```

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg" width="20" valign="middle"> `database/`
Scripts SQL: criação de tabelas e dados de exemplo.

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" width="20" valign="middle"> `core/conexao.php`
Já pronto. Cria a conexão PDO lendo `config/config.php`. Todo arquivo que mexe no banco inclui esse com `require_once`.

Novos arquivos chegam nessa pasta ao longo do curso — já explicados abaixo mesmo antes de existirem:

- **`core/autenticacao.php`** — login, proteção de páginas, logout.
- **`views/login.php` / `controllers/logout.php` / `views/cadastro.php`** — cadastro (senha com hash), login (autentica e inicia sessão), logout (encerra e redireciona).
- **`core/validacao.php`** — validação e higienização de dados de formulário (campo obrigatório, e-mail, sanitização).
- **`core/flash.php`** — mensagens de sucesso/erro após uma ação (ex: "Cadastro realizado com sucesso!").
- **`core/upload.php`** *(só quem precisar)* — upload de arquivo/imagem: valida tipo/tamanho e gera nome único.

### <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" width="20" valign="middle"> `views/`
Telas do sistema (HTML + PHP mínimo pra exibir dados). Não recebe `$_POST` nem mexe direto no banco — isso é papel do `controller`.

```
views/
└── vagas/
    ├── listar.php
    └── form.php
```

### `index.php`
Página inicial do sistema.

```bash
php -S localhost:8000
```