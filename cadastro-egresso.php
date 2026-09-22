<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$erro = null;
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpfLimpo = limparCpf($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $confirmarSenha = trim($_POST['confirmarSenha'] ?? '');

    if ($nome === '' || $cpfLimpo === '' || $senha === '') {
        $erro = 'Preencha nome, CPF e senha antes de enviar.';
    } elseif ($senha !== $confirmarSenha) {
        $erro = 'A confirmação de senha precisa ser igual à senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM egressos WHERE cpf = ?');
        $stmt->execute([formatarCpf($cpfLimpo)]);
        if ($stmt->fetch()) {
            $erro = 'Já existe um cadastro com este CPF.';
        }
    }

    if (!$erro) {
        $caminhoFoto = salvarUpload('foto', __DIR__ . '/uploads/fotos');
        $caminhoDocumento = salvarUpload('documentos_pdf', __DIR__ . '/uploads/documentos');

        $stmt = $pdo->prepare(
            'INSERT INTO egressos (nome, cpf, senha, email, telefone, foto, documento_pdf, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "pendente")'
        );
        $stmt->execute([
            $nome,
            formatarCpf($cpfLimpo),
            password_hash($senha, PASSWORD_DEFAULT),
            $email,
            $telefone,
            $caminhoFoto,
            $caminhoDocumento,
        ]);

        $sucesso = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar perfil de egresso — Janus</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

  <header class="app-header">
    <div class="wrap">
      <div>
        <a class="logo" href="index.html">Janus</a>
        <p class="app-header-meta">Criar perfil de egresso</p>
      </div>
      <div class="app-header-right">
        <a href="login.php">Já tenho conta</a>
      </div>
    </div>
  </header>

  <main id="conteudo" class="app-main">
    <div class="wrap">
      <div class="app-card">

        <?php if ($sucesso): ?>
          <div id="cadastro-confirmacao" class="app-confirm">
            <h1>Cadastro enviado!</h1>
            <p>Seus dados foram enviados para análise da instituição. Você poderá entrar assim que seu cadastro for aprovado.</p>
            <a class="button button-primary button-block" href="login.php">Voltar para o login</a>
          </div>
        <?php else: ?>

          <h1>Seus dados</h1>
          <p class="app-intro">Essas informações formam a base do seu cadastro para a instituição analisar. Depois de aprovado, você pode completar o restante do perfil (habilidades e formação adicional).</p>

          <?php if ($erro): ?>
            <p class="form-message is-error"><?= e($erro) ?></p>
          <?php endif; ?>

          <form id="cadastro-form" action="cadastro-egresso.php" method="post" enctype="multipart/form-data">
            <div class="field">
              <label for="nome">Nome completo</label>
              <input type="text" id="nome" name="nome" placeholder="Cauan Silva Ferreira" value="<?= e($_POST['nome'] ?? '') ?>">
            </div>

            <div class="field">
              <label for="cpf">CPF</label>
              <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" data-mask="cpf" inputmode="numeric" value="<?= e($_POST['cpf'] ?? '') ?>">
            </div>

            <div class="field">
              <label for="telefone">Telefone (com DDD)</label>
              <input type="tel" id="telefone" name="telefone" placeholder="(99) 99999-9999" autocomplete="tel" value="<?= e($_POST['telefone'] ?? '') ?>">
              <p class="field-hint">Empresas usam este número para falar com você direto pelo WhatsApp.</p>
            </div>

            <div class="field">
              <label for="email">E-mail</label>
              <input type="email" id="email" name="email" placeholder="seu.nome@exemplo.com" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="field">
              <span class="field-label">Documentos necessários</span>
              <p class="field-hint" style="margin-top: -0.2em;">Reúna em um único arquivo PDF: comprovante de residência, certificado de conclusão do curso e documento de identidade.</p>
              <label class="upload-button" data-upload-field style="width: 100%;">
                <input type="file" name="documentos_pdf" accept="application/pdf" data-upload-input>
                <span data-upload-text>Anexar PDF com os documentos</span>
              </label>
            </div>

            <div class="field">
              <label for="senha-cadastro">Senha</label>
              <input type="password" id="senha-cadastro" name="senha" placeholder="Crie uma senha" autocomplete="new-password">
            </div>

            <div class="field">
              <label for="confirmar-senha-cadastro">Confirmar senha</label>
              <input type="password" id="confirmar-senha-cadastro" name="confirmarSenha" placeholder="Repita a senha" autocomplete="new-password">
            </div>

            <button type="submit" class="button button-primary button-block">Enviar cadastro</button>
          </form>

        <?php endif; ?>

      </div>
    </div>
  </main>

  <script src="js/main.js"></script>
</body>
</html>
