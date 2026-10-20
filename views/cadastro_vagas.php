<?php
/**
 * Janus - Cadastro de Vaga (VIEW)
 * Somente a tela. O processamento fica em cadastro_vaga_processar.php.
 *
 * Retorno esperado do processar (opcional), via redirecionamento:
 *   cadastro_vaga.php?status=sucesso
 *   cadastro_vaga.php?status=erro&msg=Texto+do+erro
 */
session_start();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Troque por consulta ao banco se as áreas já existirem lá.
$areas = [
    'Tecnologia da Informação', 'Administração', 'Gastronomia', 'Saúde e Bem-estar',
    'Design e Comunicação', 'Logística', 'Hotelaria e Turismo', 'Vendas e Atendimento',
    'Beleza e Estética', 'Idiomas', 'Outra'
];
$tiposContrato = ['CLT', 'PJ', 'Estágio', 'Jovem Aprendiz', 'Temporário', 'Freelancer'];
$modalidades   = ['Presencial', 'Híbrido', 'Remoto'];
$niveis        = ['Estagiário', 'Júnior', 'Pleno', 'Sênior', 'Não se aplica'];
$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

$status = $_GET['status'] ?? '';
$msg    = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Janus | Cadastrar vaga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --janus-accent: #0d6efd; }
        body { min-height: 100vh; }
        .janus-card { border: 0; box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08); }
        .section-title { font-size: .8rem; letter-spacing: .08em; text-transform: uppercase;
                         color: var(--bs-secondary-color); font-weight: 600; }
        .section-title::after { content: ""; display: block; height: 2px; width: 40px;
                                background: var(--janus-accent); margin-top: .35rem; }
    </style>
</head>
<body class="bg-body-tertiary">

<nav class="navbar bg-body border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-signpost-split me-2"></i>Janus</a>
        <button id="themeToggle" class="btn btn-outline-secondary btn-sm" type="button" aria-label="Alternar tema">
            <i class="bi bi-moon-stars-fill"></i>
        </button>
    </div>
</nav>

<main class="container py-4 py-md-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">

            <div class="mb-4">
                <h1 class="h3 fw-bold mb-1">Cadastrar nova vaga</h1>
                <p class="text-body-secondary mb-0">Preencha os dados abaixo. A vaga será exibida aos egressos conforme a área de atuação.</p>
            </div>

            <?php if ($status === 'sucesso'): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Vaga cadastrada com sucesso!
                </div>
            <?php elseif ($status === 'erro'): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($msg !== '' ? $msg : 'Não foi possível cadastrar a vaga.', ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/cadastro_vaga_processar.php" method="post" class="card janus-card" id="formVaga" novalidate>
                <div class="card-body p-4">
                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                    <!-- Informações básicas -->
                    <p class="section-title mb-3">Informações básicas</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="titulo" class="form-label">Título da vaga *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" maxlength="120"
                                   placeholder="Ex.: Desenvolvedor Web Júnior" required>
                            <div class="invalid-feedback">Informe o título da vaga.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="quantidade" class="form-label">Nº de vagas *</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade"
                                   min="1" max="999" value="1" required>
                            <div class="invalid-feedback">Mínimo 1.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="empresa" class="form-label">Empresa *</label>
                            <input type="text" class="form-control" id="empresa" name="empresa" maxlength="120" required>
                            <div class="invalid-feedback">Informe a empresa.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="area" class="form-label">Área de atuação *</label>
                            <select class="form-select" id="area" name="area_atuacao" required>
                                <option value="" selected disabled>Selecione...</option>
                                <?php foreach ($areas as $a): ?>
                                    <option value="<?= htmlspecialchars($a) ?>"><?= htmlspecialchars($a) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione a área.</div>
                        </div>
                    </div>

                    <!-- Contrato e local -->
                    <p class="section-title mb-3">Contrato e local</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="tipo_contrato" class="form-label">Tipo de contrato *</label>
                            <select class="form-select" id="tipo_contrato" name="tipo_contrato" required>
                                <option value="" selected disabled>Selecione...</option>
                                <?php foreach ($tiposContrato as $t): ?>
                                    <option><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione o contrato.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="nivel" class="form-label">Nível *</label>
                            <select class="form-select" id="nivel" name="nivel" required>
                                <option value="" selected disabled>Selecione...</option>
                                <?php foreach ($niveis as $n): ?>
                                    <option><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione o nível.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="modalidade" class="form-label">Modalidade *</label>
                            <select class="form-select" id="modalidade" name="modalidade" required>
                                <option value="" selected disabled>Selecione...</option>
                                <?php foreach ($modalidades as $m): ?>
                                    <option><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione a modalidade.</div>
                        </div>
                        <div class="col-md-8" id="grpCidade">
                            <label for="cidade" class="form-label">Cidade *</label>
                            <input type="text" class="form-control" id="cidade" name="cidade" maxlength="80">
                            <div class="invalid-feedback">Informe a cidade.</div>
                        </div>
                        <div class="col-md-4" id="grpUf">
                            <label for="uf" class="form-label">UF *</label>
                            <select class="form-select" id="uf" name="uf">
                                <option value="">--</option>
                                <?php foreach ($ufs as $u): ?>
                                    <option><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Selecione a UF.</div>
                        </div>
                    </div>

                    <!-- Remuneração -->
                    <p class="section-title mb-3">Remuneração</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="salario_min" class="form-label">Salário mínimo (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="salario_min" name="salario_min">
                        </div>
                        <div class="col-md-4">
                            <label for="salario_max" class="form-label">Salário máximo (R$)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="salario_max" name="salario_max">
                            <div class="invalid-feedback">O máximo deve ser maior ou igual ao mínimo.</div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="salario_a_combinar"
                                       name="salario_a_combinar" value="1">
                                <label class="form-check-label" for="salario_a_combinar">A combinar</label>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes -->
                    <p class="section-title mb-3">Detalhes da vaga</p>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label for="descricao" class="form-label">Descrição *</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="4" minlength="20"
                                      placeholder="Principais atividades e responsabilidades" required></textarea>
                            <div class="invalid-feedback">Descreva a vaga (mín. 20 caracteres).</div>
                        </div>
                        <div class="col-md-6">
                            <label for="requisitos" class="form-label">Requisitos</label>
                            <textarea class="form-control" id="requisitos" name="requisitos" rows="3"
                                      placeholder="Formação, conhecimentos, experiência"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="beneficios" class="form-label">Benefícios</label>
                            <textarea class="form-control" id="beneficios" name="beneficios" rows="3"
                                      placeholder="VR, VT, plano de saúde..."></textarea>
                        </div>
                    </div>

                    <!-- Contato -->
                    <p class="section-title mb-3">Contato e prazo</p>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="email_contato" class="form-label">E-mail para candidaturas *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email_contato" name="email_contato" required>
                                <div class="invalid-feedback">Informe um e-mail válido.</div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label for="data_limite" class="form-label">Data limite</label>
                            <input type="date" class="form-control" id="data_limite" name="data_limite"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center p-3 px-4">
                    <small class="text-body-secondary">* campos obrigatórios</small>
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Limpar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>Cadastrar vaga</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    // ---- Tema claro/escuro (persistente) ----
    const html = document.documentElement;
    const btn  = document.getElementById('themeToggle');
    const icon = btn.querySelector('i');
    function applyTheme(t) {
        html.setAttribute('data-bs-theme', t);
        icon.className = t === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
    }
    let saved = null;
    try { saved = localStorage.getItem('janus-theme'); } catch (e) {}
    applyTheme(saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
    btn.addEventListener('click', function () {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
        try { localStorage.setItem('janus-theme', next); } catch (e) {}
    });

    // ---- Remoto: esconde cidade/UF ----
    const modalidade = document.getElementById('modalidade');
    const cidade = document.getElementById('cidade');
    const uf = document.getElementById('uf');
    function toggleLocal() {
        const remoto = modalidade.value === 'Remoto';
        document.getElementById('grpCidade').classList.toggle('d-none', remoto);
        document.getElementById('grpUf').classList.toggle('d-none', remoto);
        cidade.required = uf.required = !remoto;
        cidade.disabled = uf.disabled = remoto;
    }
    modalidade.addEventListener('change', toggleLocal);
    toggleLocal();

    // ---- Salário a combinar ----
    const combinar = document.getElementById('salario_a_combinar');
    const sMin = document.getElementById('salario_min');
    const sMax = document.getElementById('salario_max');
    function toggleSalario() {
        [sMin, sMax].forEach(function (el) {
            el.disabled = combinar.checked;
            if (combinar.checked) el.value = '';
        });
    }
    combinar.addEventListener('change', toggleSalario);
    toggleSalario();

    // ---- Validação Bootstrap ----
    const form = document.getElementById('formVaga');
    form.addEventListener('submit', function (e) {
        sMax.setCustomValidity('');
        if (sMin.value && sMax.value && parseFloat(sMin.value) > parseFloat(sMax.value)) {
            sMax.setCustomValidity('invalido');
        }
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    form.addEventListener('reset', function () {
        form.classList.remove('was-validated');
        setTimeout(function () { toggleLocal(); toggleSalario(); }, 0);
    });
})();
</script>
</body>
</html>