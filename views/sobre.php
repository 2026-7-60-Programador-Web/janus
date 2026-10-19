<?php
// Página pública: apresenta o sistema Janus e o seu objetivo para o SENAC.
// Não exige login, então pode ser aberta por qualquer visitante (professor, banca, empresa).
session_start();

// Se já houver sessão, o botão principal leva direto para o painel certo
$destinoPrincipal = "login.php";
$textoPrincipal   = "Acessar o sistema";

if (isset($_SESSION["email"])) {
    $destinoPrincipal = (($_SESSION["tipo"] ?? "") === "administrador")
        ? "admin/painel.php"
        : "egresso/painel.php";
    $textoPrincipal = "Ir para o meu painel";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Janus: Banco de Talentos de Egressos do SENAC. Conheça o objetivo e as funcionalidades do sistema.">
    <title>Janus | Sobre o sistema</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="../assets/css/sobre.css" rel="stylesheet">
</head>
<body>

<!-- ===== Topo ===== -->
<header class="topo">
    <div class="wrap">
        <a class="marca" href="sobre.php">JANUS</a>
        <nav aria-label="Navegação da página">
            <a class="link" href="#objetivo">Objetivo</a>
            <a class="link" href="#como-funciona">Como funciona</a>
            <a class="link" href="#beneficios">Benefícios</a>
            <a class="link" href="#roadmap">Roadmap</a>
            <a class="btn btn-ouro" href="<?php echo $destinoPrincipal; ?>"><?php echo $textoPrincipal; ?></a>
        </nav>
    </div>
</header>

<!-- ===== Hero ===== -->
<section class="hero">
    <div class="wrap">
        <div class="logo-wrap">
            <svg width="96" height="96" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Logo do Janus: duas faces voltadas para lados opostos">
                <circle cx="50" cy="50" r="47" fill="none" stroke="#f2c94c" stroke-width="2"/>
                <circle cx="50" cy="50" r="40" fill="none" stroke="#f2c94c" stroke-width="1" opacity="0.4"/>
                <path d="M50 30 C 40 30, 33 38, 33 50 C 33 60, 38 66, 44 68 L 44 74 L 39 74 L 39 70 C 30 66, 24 58, 24 50 C 24 34, 36 22, 50 22 Z" fill="#dcc182"/>
                <circle cx="38" cy="46" r="2.2" fill="#ffffff"/>
                <path d="M50 30 C 60 30, 67 38, 67 50 C 67 60, 62 66, 56 68 L 56 74 L 61 74 L 61 70 C 70 66, 76 58, 76 50 C 76 34, 64 22, 50 22 Z" fill="#f2c94c"/>
                <circle cx="62" cy="46" r="2.2" fill="#ffffff"/>
                <line x1="50" y1="22" x2="50" y2="78" stroke="#ffffff" stroke-width="1.2" opacity="0.6"/>
            </svg>
        </div>
        <h1>JANUS</h1>
        <p class="subtitulo">Banco de Talentos de Egressos</p>
        <p class="lead">
            Um portal que mantém o SENAC e seus ex-alunos em contato: o egresso apresenta
            suas habilidades, o SENAC cadastra oportunidades e os dois lados se encontram em um só lugar.
        </p>
        <div class="acoes">
            <a class="btn btn-ouro" href="<?php echo $destinoPrincipal; ?>"><?php echo $textoPrincipal; ?></a>
            <a class="btn btn-contorno" href="#objetivo">Entender o objetivo</a>
        </div>
        <span class="selo-senac">SENAC &middot; CEP Caxias, Maranhão &middot; Programa Senac de Gratuidade (PSG)</span>
    </div>
</section>

<!-- ===== Objetivo ===== -->
<section id="objetivo">
    <div class="wrap">
        <p class="rotulo">Objetivo do sistema</p>
        <h2>Por que o Janus existe para o SENAC</h2>
        <p class="intro">
            O SENAC forma profissionais, mas o trabalho não termina no dia da formatura.
            O Janus nasce para acompanhar o que acontece depois: onde os egressos estão,
            o que sabem fazer e quais oportunidades podem ser encaminhadas a eles.
        </p>

        <div class="destaque">
            <p>
                <strong>Objetivo geral:</strong> reunir os egressos do SENAC em um banco de talentos
                organizado por curso e área de atuação, e usá-lo para aproximar esses profissionais
                de vagas de emprego e de oportunidades de mercado.
            </p>
            <p>
                <strong>Para a instituição:</strong> transformar a lista de ex-alunos, que normalmente
                fica dispersa, em um canal ativo de relacionamento, de colocação profissional
                e de acompanhamento dos resultados da formação oferecida.
            </p>
        </div>

        <div class="grade" style="margin-top:28px;">
            <div class="card">
                <div class="icone"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7"/><path d="M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></svg></div>
                <h3>Manter o vínculo</h3>
                <p>Cada egresso tem cadastro e perfil próprios, e o SENAC passa a ter um registro vivo de quem passou por seus cursos.</p>
            </div>
            <div class="card">
                <div class="icone"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></div>
                <h3>Conectar a oportunidades</h3>
                <p>A equipe cadastra vagas e o egresso vê as compatíveis com a sua área de atuação, sem precisar procurar em vários canais.</p>
            </div>
            <div class="card">
                <div class="icone"><svg viewBox="0 0 24 24"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg></div>
                <h3>Dar visibilidade aos resultados</h3>
                <p>Com os dados organizados, fica mais fácil enxergar a atuação dos egressos por curso e por área, e apoiar a gestão da instituição.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== Como funciona ===== -->
<section id="como-funciona" class="alt">
    <div class="wrap">
        <p class="rotulo">Como funciona</p>
        <h2>Um sistema, dois perfis</h2>
        <p class="intro">
            O nome vem do deus romano Janus, o das duas faces: uma voltada para quem chega (o egresso)
            e outra para quem administra (o SENAC). A logo do sistema carrega exatamente essa ideia.
        </p>

        <div class="grade dois">
            <div class="card">
                <span class="perfil-tag tag-egresso">Perfil egresso</span>
                <h3>O que o ex-aluno faz</h3>
                <ul>
                    <li>Cria o cadastro com nome, e-mail, curso, área de atuação, habilidades e telefone.</li>
                    <li>Entra com e-mail e senha, de forma segura (senha armazenada com hash).</li>
                    <li>Consulta e edita o próprio perfil.</li>
                    <li>Vê as vagas disponíveis para a sua área e pode se candidatar.</li>
                </ul>
            </div>
            <div class="card">
                <span class="perfil-tag tag-admin">Perfil administrador</span>
                <h3>O que o SENAC faz</h3>
                <ul>
                    <li>Acompanha o painel de egressos, com busca por nome e filtro por área.</li>
                    <li>Abre o perfil completo de cada egresso.</li>
                    <li>Cadastra novas vagas (título, empresa, área e descrição).</li>
                    <li>Lista, edita e exclui as vagas já publicadas.</li>
                </ul>
            </div>
        </div>

        <h3 style="margin:36px 0 14px; font-size:18px;">O caminho de um talento dentro do Janus</h3>
        <ol class="etapas">
            <li><strong>O egresso se cadastra</strong><span>Informa curso, área de atuação e habilidades, e passa a fazer parte do banco de talentos.</span></li>
            <li><strong>O SENAC publica vagas</strong><span>O administrador registra as oportunidades recebidas de empresas parceiras, cada uma ligada a uma área.</span></li>
            <li><strong>O sistema aproxima os dois lados</strong><span>O egresso enxerga as vagas da sua área e o administrador enxerga quem está disponível em cada área.</span></li>
            <li><strong>O egresso se candidata</strong><span>O contato acontece e o SENAC cumpre o papel de ponte entre formação e mercado.</span></li>
        </ol>
    </div>
</section>

<!-- ===== Benefícios ===== -->
<section id="beneficios">
    <div class="wrap">
        <p class="rotulo">Benefícios</p>
        <h2>O que o SENAC ganha com o Janus</h2>
        <p class="intro">Resultados esperados com o uso contínuo do sistema.</p>

        <div class="grade quatro">
            <div class="card">
                <h3>Dados organizados</h3>
                <p>Egressos e vagas em um único sistema, em vez de planilhas e contatos soltos.</p>
            </div>
            <div class="card">
                <h3>Relação com o mercado</h3>
                <p>Um canal claro para empresas oferecerem vagas e para a instituição responder a elas.</p>
            </div>
            <div class="card">
                <h3>Valorização da formação</h3>
                <p>Mostra o compromisso do SENAC com a empregabilidade de quem estudou na casa.</p>
            </div>
            <div class="card">
                <h3>Base para decisões</h3>
                <p>A visão por curso e por área ajuda a planejar turmas e parcerias.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== Tecnologias e estrutura ===== -->
<section class="alt">
    <div class="wrap">
        <p class="rotulo">Por dentro do projeto</p>
        <h2>Tecnologias e organização</h2>
        <p class="intro">
            Desenvolvido como Projeto Integrador do curso de Qualificação Profissional em
            Programador Web, sem frameworks de MVC: a rota é o próprio caminho do arquivo.
        </p>

        <div class="techs" style="margin-bottom:28px;">
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="">PHP</span>
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original.svg" alt="">HTML5</span>
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original.svg" alt="">CSS3</span>
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg" alt="">JavaScript</span>
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original.svg" alt="">MySQL</span>
            <span class="tech"><img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/bootstrap/bootstrap-original.svg" alt="">Bootstrap</span>
        </div>

<pre class="arvore"><span class="c">janus/</span>
├── <span class="c">assets/</span>        <span class="m">estilos (css) e scripts (js)</span>
├── <span class="c">config/</span>        <span class="m">dados de acesso ao banco</span>
├── <span class="c">controllers/</span>   <span class="m">recebem os formulários, validam e gravam</span>
├── <span class="c">core/</span>          <span class="m">conexão, autenticação, validação, flash e upload</span>
├── <span class="c">database/</span>      <span class="m">scripts SQL</span>
├── <span class="c">docs/</span>          <span class="m">roadmap e sugestão de telas</span>
├── <span class="c">views/</span>         <span class="m">telas do sistema (login, egresso, admin e esta página)</span>
└── index.php
</pre>
    </div>
</section>

<!-- ===== Roadmap ===== -->
<section id="roadmap">
    <div class="wrap">
        <p class="rotulo">Roadmap</p>
        <h2>Como o sistema foi planejado</h2>
        <p class="intro">O desenvolvimento seguiu cinco fases, do visual até o teste completo do fluxo.</p>

        <div class="fases">
            <div class="fase"><small>Fase 1</small><strong>Estrutura e painéis</strong><p>Menu de navegação e telas iniciais dos dois perfis.</p></div>
            <div class="fase"><small>Fase 2</small><strong>Formulários</strong><p>Cadastro de egresso e de vaga, com validação completa.</p></div>
            <div class="fase"><small>Fase 3</small><strong>Listagens</strong><p>Perfil, vagas disponíveis, painel de egressos e lista de vagas.</p></div>
            <div class="fase"><small>Fase 4</small><strong>Banco de dados</strong><p>Login e cadastros gravando e consultando dados reais.</p></div>
            <div class="fase"><small>Fase 5</small><strong>Refinamento</strong><p>Mensagens padronizadas, sanitização e teste do fluxo completo.</p></div>
        </div>
    </div>
</section>

<!-- ===== Chamada final ===== -->
<section class="alt chamada">
    <div class="wrap">
        <h2>Pronto para conhecer o Janus por dentro?</h2>
        <p>Entre com a sua conta ou faça o cadastro de egresso para ver o sistema funcionando.</p>
        <div class="acoes" style="display:flex; justify-content:center; flex-wrap:wrap; gap:12px;">
            <a class="btn btn-ouro" href="<?php echo $destinoPrincipal; ?>"><?php echo $textoPrincipal; ?></a>
            <?php if (!isset($_SESSION["email"])): ?>
                <a class="btn btn-contorno" href="egresso/cadastro.php">Cadastrar como egresso</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="rodape">
    <strong>Janus</strong> &middot; Banco de Talentos de Egressos<br>
    Projeto Integrador &middot; Programador Web &middot; SENAC CEP Caxias, Maranhão &middot; 2026
</footer>

</body>
</html>
