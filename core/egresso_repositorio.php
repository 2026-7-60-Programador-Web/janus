<?php

/**
 * Acesso ao banco para os dados do egresso
 * (tabelas egressos, perfis_egresso, egresso_habilidades e egresso_formacoes).
 * Crie as tabelas rodando database/egressos.sql.
 */

// Devolve a conexão PDO (criada por core/conexao.php), reaproveitando a mesma na requisição
function egressoConexao(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        require __DIR__ . '/conexao.php'; // cria $pdo neste escopo
    }

    return $pdo;
}

// Busca um egresso pelo e-mail, já com habilidades e formações. Devolve null se não existir.
function egressoBuscarPorEmail(string $email): ?array
{
    $pdo = egressoConexao();

    $stmt = $pdo->prepare(
        'SELECT e.id, e.nome, e.email, e.telefone, e.curso, e.area_atuacao, p.bio
         FROM egressos e
         LEFT JOIN perfis_egresso p ON p.egresso_id = e.id
         WHERE e.email = :email'
    );
    $stmt->execute([':email' => $email]);
    $egresso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$egresso) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT tipo, nome FROM egresso_habilidades WHERE egresso_id = :id ORDER BY id');
    $stmt->execute([':id' => $egresso['id']]);

    $egresso['soft_skills'] = [];
    $egresso['hard_skills'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $habilidade) {
        $egresso[$habilidade['tipo'] . '_skills'][] = $habilidade['nome'];
    }

    $stmt = $pdo->prepare('SELECT curso, instituicao, ano FROM egresso_formacoes WHERE egresso_id = :id ORDER BY id');
    $stmt->execute([':id' => $egresso['id']]);
    $egresso['formacoes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $egresso;
}

// Lista todos os egressos no formato usado pelo painel do administrador.
// Se o banco ainda não estiver configurado, devolve lista vazia (o painel continua abrindo).
function egressoListarParaAdmin(): array
{
    if (!is_file(__DIR__ . '/../config/database.php')) {
        return [];
    }

    try {
        $pdo = egressoConexao();

        $egressos    = $pdo->query('SELECT e.id, e.nome, e.email, e.telefone, e.curso, e.area_atuacao, p.bio
                              FROM egressos e
                              LEFT JOIN perfis_egresso p ON p.egresso_id = e.id
                              ORDER BY e.nome')
            ->fetchAll(PDO::FETCH_ASSOC);
        $habilidades = $pdo->query('SELECT egresso_id, tipo, nome FROM egresso_habilidades ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);
        $formacoes   = $pdo->query('SELECT egresso_id, curso, instituicao, ano FROM egresso_formacoes ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }

    $skillsPorEgresso = [];
    foreach ($habilidades as $h) {
        $skillsPorEgresso[$h['egresso_id']][$h['tipo']][] = $h['nome'];
    }

    $primeiraFormacao = [];
    foreach ($formacoes as $f) {
        if (!isset($primeiraFormacao[$f['egresso_id']])) {
            $primeiraFormacao[$f['egresso_id']] = $f;
        }
    }

    $lista = [];
    foreach ($egressos as $e) {
        $formacao = $primeiraFormacao[$e['id']] ?? null;

        $lista[] = [
            'nome'          => $e['nome'],
            'area'          => $e['area_atuacao'],
            'curso'         => $e['curso'],
            'ano_formatura' => $formacao['ano'] ?? '',
            'email'         => $e['email'],
            'telefone'      => $e['telefone'] !== '' ? $e['telefone'] : 'Não informado',
            'instituicao'   => $formacao['instituicao'] ?? '',
            'sobre'         => (string) ($e['bio'] ?? ''),
            'soft_skills'   => $skillsPorEgresso[$e['id']]['soft'] ?? [],
            'hard_skills'   => $skillsPorEgresso[$e['id']]['hard'] ?? [],
        ];
    }

    return $lista;
}
