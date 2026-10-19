<?php

// Dados de exemplo (fase 3 do roadmap - ainda sem banco de dados)
$egressosCadastrados = [
    ["nome" => "Ana Beatriz Sousa",     "area" => "Informática",   "curso" => "Programador Web",          "ano_formatura" => 2023, "email" => "ana.sousa@email.com",       "telefone" => "(99) 99111-2233", "instituicao" => "Senac", "sobre" => "Egressa em busca de oportunidades na área de tecnologia. Gosto de aprender rápido e trabalhar em equipe.", "soft_skills" => ["Comunicação", "Organização", "Trabalho em equipe"], "hard_skills" => ["PHP", "HTML", "CSS", "JavaScript"]],
    ["nome" => "Carlos Eduardo Lima",   "area" => "Saúde",         "curso" => "Técnico em Enfermagem",    "ano_formatura" => 2022, "email" => "carlos.lima@email.com",     "telefone" => "(99) 99222-3344", "instituicao" => "Senac", "sobre" => "Busco atuar em hospitais e clínicas da região.", "soft_skills" => ["Empatia", "Atenção aos detalhes"], "hard_skills" => ["Primeiros Socorros", "Administração de Medicamentos"]],
    ["nome" => "Fernanda Rocha",        "area" => "Administração", "curso" => "Técnico em Administração", "ano_formatura" => 2024, "email" => "fernanda.rocha@email.com",  "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Interesse em gestão financeira e processos administrativos.", "soft_skills" => ["Proatividade", "Liderança"], "hard_skills" => ["Excel", "Gestão de Estoque"]],
    ["nome" => "João Pedro Alves",      "area" => "Informática",   "curso" => "Redes de Computadores",    "ano_formatura" => 2023, "email" => "joao.alves@email.com",      "telefone" => "(99) 99333-4455", "instituicao" => "Senac", "sobre" => "Foco em infraestrutura e suporte técnico.", "soft_skills" => ["Resolução de problemas"], "hard_skills" => ["Redes", "Linux", "Suporte Técnico"]],
    ["nome" => "Larissa Menezes",       "area" => "Saúde",         "curso" => "Técnico em Radiologia",    "ano_formatura" => 2021, "email" => "larissa.menezes@email.com", "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Experiência em clínicas de imagem.", "soft_skills" => ["Atenção aos detalhes"], "hard_skills" => ["Raio-X", "Tomografia"]],
    ["nome" => "Marcos Vinícius Silva", "area" => "Design",        "curso" => "Design Gráfico",           "ano_formatura" => 2024, "email" => "marcos.silva@email.com",    "telefone" => "(99) 99444-5566", "instituicao" => "Senac", "sobre" => "Apaixonado por identidade visual e branding.", "soft_skills" => ["Criatividade"], "hard_skills" => ["Photoshop", "Illustrator", "Figma"]],
    ["nome" => "Patrícia Nunes",        "area" => "Informática",   "curso" => "Programador Web",          "ano_formatura" => 2022, "email" => "patricia.nunes@email.com",  "telefone" => "(99) 99555-6677", "instituicao" => "Senac", "sobre" => "Focada em front-end e experiência do usuário.", "soft_skills" => ["Comunicação"], "hard_skills" => ["HTML", "CSS", "React"]],
    ["nome" => "Rafael Gomes",          "area" => "Administração", "curso" => "Técnico em Logística",     "ano_formatura" => 2023, "email" => "rafael.gomes@email.com",    "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Experiência em controle de estoque e distribuição.", "soft_skills" => ["Organização"], "hard_skills" => ["Gestão de Estoque", "Excel"]],
    ["nome" => "Sabrina Teixeira",      "area" => "Saúde",         "curso" => "Técnico em Enfermagem",    "ano_formatura" => 2024, "email" => "sabrina.teixeira@email.com","telefone" => "(99) 99666-7788", "instituicao" => "Senac", "sobre" => "Busca primeira oportunidade na área da saúde.", "soft_skills" => ["Empatia"], "hard_skills" => ["Primeiros Socorros"]],
    ["nome" => "Thiago Barros",         "area" => "Informática",   "curso" => "Redes de Computadores",    "ano_formatura" => 2021, "email" => "thiago.barros@email.com",   "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Atua com suporte técnico há 2 anos.", "soft_skills" => ["Resolução de problemas"], "hard_skills" => ["Redes", "Windows Server"]],
    ["nome" => "Vanessa Cardoso",       "area" => "Design",        "curso" => "Design Gráfico",           "ano_formatura" => 2022, "email" => "vanessa.cardoso@email.com", "telefone" => "(99) 99777-8899", "instituicao" => "Senac", "sobre" => "Ilustradora nas horas vagas.", "soft_skills" => ["Criatividade"], "hard_skills" => ["Illustrator", "Procreate"]],
    ["nome" => "Wesley Farias",         "area" => "Administração", "curso" => "Técnico em Administração", "ano_formatura" => 2024, "email" => "wesley.farias@email.com",   "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Recém-formado, buscando primeira experiência.", "soft_skills" => ["Proatividade"], "hard_skills" => ["Excel", "Atendimento"]],
    ["nome" => "Yasmin Correia",        "area" => "Saúde",         "curso" => "Técnico em Radiologia",    "ano_formatura" => 2023, "email" => "yasmin.correia@email.com",  "telefone" => "(99) 99888-9900", "instituicao" => "Senac", "sobre" => "Interesse em atuar em hospitais de grande porte.", "soft_skills" => ["Atenção aos detalhes"], "hard_skills" => ["Tomografia", "Raio-X"]],
    ["nome" => "Bruno Castro",          "area" => "Informática",   "curso" => "Programador Web",          "ano_formatura" => 2021, "email" => "bruno.castro@email.com",    "telefone" => "Não informado",   "instituicao" => "Senac", "sobre" => "Trabalha atualmente como freelancer.", "soft_skills" => ["Trabalho em equipe"], "hard_skills" => ["PHP", "JavaScript", "MySQL"]],
    ["nome" => "Camila Duarte",         "area" => "Design",        "curso" => "Design Gráfico",           "ano_formatura" => 2023, "email" => "camila.duarte@email.com",   "telefone" => "(99) 99999-0011", "instituicao" => "Senac", "sobre" => "Especialista em redes sociais e conteúdo visual.", "soft_skills" => ["Criatividade", "Comunicação"], "hard_skills" => ["Photoshop", "Canva"]],
];

// Os egressos que se cadastraram pelo formulário vêm do banco de dados
require_once __DIR__ . "/../../core/egresso_repositorio.php";

$egressosCadastrados = array_merge(
    $egressosCadastrados,
    egressoListarParaAdmin()
);

/**
 * Filtra a lista de egressos por área (exata) e por nome (busca parcial, case-insensitive).
 */
function filtrarEgressos(array $egressos, string $area, string $busca): array
{
    return array_values(array_filter($egressos, function ($egresso) use ($area, $busca) {
        $bateArea = ($area === "" || $area === "todas") || $egresso["area"] === $area;
        $bateBusca = ($busca === "") || (stripos($egresso["nome"], $busca) !== false);
        return $bateArea && $bateBusca;
    }));
}

/**
 * Ordena a lista por um campo válido, na direção "asc" ou "desc".
 */
function ordenarEgressos(array $egressos, string $campo, string $direcao): array
{
    $camposValidos = ["nome", "area", "curso", "ano_formatura"];
    if (!in_array($campo, $camposValidos, true)) {
        $campo = "nome";
    }

    usort($egressos, function ($a, $b) use ($campo) {
        return strnatcasecmp((string) $a[$campo], (string) $b[$campo]);
    });

    if ($direcao === "desc") {
        $egressos = array_reverse($egressos);
    }

    return $egressos;
}

/**
 * Fatia a lista em páginas e devolve os metadados de paginação.
 */
function paginarEgressos(array $egressos, int $paginaAtual, int $itensPorPagina): array
{
    $total = count($egressos);
    $totalPaginas = max(1, (int) ceil($total / $itensPorPagina));
    $paginaAtual = max(1, min($paginaAtual, $totalPaginas));
    $inicio = ($paginaAtual - 1) * $itensPorPagina;

    return [
        "itens"         => array_slice($egressos, $inicio, $itensPorPagina),
        "total"         => $total,
        "pagina_atual"  => $paginaAtual,
        "total_paginas" => $totalPaginas,
        "inicio"        => $total === 0 ? 0 : $inicio + 1,
        "fim"           => min($inicio + $itensPorPagina, $total),
    ];
}

/**
 * Busca um único egresso pelo e-mail (usado na tela de perfil).
 */
function buscarEgressoPorEmail(array $egressos, string $email): ?array
{
    foreach ($egressos as $egresso) {
        if ($egresso["email"] === $email) {
            return $egresso;
        }
    }
    return null;
}

/**
 * Gera as iniciais do nome para exibir no avatar (ex.: "Ana Beatriz Sousa" -> "AS").
 */
function iniciaisNome(string $nome): string
{
    $partes = preg_split('/\s+/', trim($nome));
    $iniciais = mb_strtoupper(mb_substr($partes[0], 0, 1));
    if (count($partes) > 1) {
        $iniciais .= mb_strtoupper(mb_substr(end($partes), 0, 1));
    }
    return $iniciais;
}

/**
 * Cor do avatar, gerada a partir do nome (varia visualmente entre os egressos).
 */
function corAvatar(string $nome): string
{
    $paleta = ["#5b8def", "#a56bf0", "#2fb28c", "#e0a63a", "#e0607a", "#4bb3c9"];
    $indice = array_sum(array_map("ord", str_split(substr($nome, 0, 4)))) % count($paleta);
    return $paleta[$indice];
}

/**
 * Formata telefone com 10 ou 11 dígitos; qualquer outro valor volta como veio.
 */
function telefoneFormatado(string $telefone): string
{
    $d = preg_replace("/\D/", "", $telefone);
    if (strlen($d) === 11) {
        return sprintf("(%s) %s-%s", substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
    }
    if (strlen($d) === 10) {
        return sprintf("(%s) %s-%s", substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
    }
    return $telefone;
}
