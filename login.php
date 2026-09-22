<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$erro = null;
$perfilSelecionado = 'egresso';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $perfilSelecionado = $_POST['perfil'] ?? 'egresso';
    $senha = trim($_POST['senha'] ?? '');

    if ($perfilSelecionado === 'egresso') {
        $cpf = trim($_POST['cpf'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM egressos WHERE cpf = ?');
        $stmt->execute([$cpf]);
        $egresso = $stmt->fetch();

        if (!$egresso || !password_verify($senha, $egresso['senha'])) {
            $erro = 'CPF não encontrado ou senha incorreta.';
        } elseif ($egresso['status'] === 'pendente') {
            $erro = 'Seu cadastro ainda está em análise pela instituição.';
        } elseif ($egresso['status'] === 'recusado') {
            $erro = 'Seu cadastro foi recusado pela instituição. Procure a coordenação.';
        } else {
            definirSessao('egresso', ['id' => $egresso['id']]);
            header('Location: perfil-egresso.php?id=' . $egresso['id']);
            exit;
        }
    } elseif ($perfilSelecionado === 'empresa') {
        $cnpj = trim($_POST['cnpj'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM empresas_parceiras WHERE cnpj = ?');
        $stmt->execute([$cnpj]);
        $empresa = $stmt->fetch();

        if (!$empresa || !password_verify($senha, $empresa['senha'])) {
            $erro = 'CNPJ não encontrado ou senha incorreta.';
        } else {
            definirSessao('empresa', ['id' => $empresa['id'], 'identificacao' => $empresa['cnpj']]);
            header('Location: busca-empresa.php');
            exit;
        }
    } elseif ($perfilSelecionado === 'instituicao') {
        $codigo = trim($_POST['codigo'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM instituicoes WHERE codigo = ?');
        $stmt->execute([$codigo]);
        $instituicao = $stmt->fetch();

        if (!$instituicao || !password_verify($senha, $instituicao['senha'])) {
            $erro = 'Código ou senha incorretos.';
        } else {
            definirSessao('instituicao', ['id' => $instituicao['id']]);
            header('Location: painel-admin.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — Janus</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

  <header class="site-header">
    <div class="wrap">
      <a class="logo" href="index.html">Janus</a>
      <nav class="main-nav" aria-label="Navegação principal">
        <ul>
          <li><a href="index.html">Início</a></li>
        </ul>
      </nav>
      <span></span>
      <button class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="mobile-menu">Menu</button>
    </div>
    <div class="mobile-menu" id="mobile-menu" hidden>
      <ul>
        <li><a href="index.html">Início</a></li>
      </ul>
    </div>
  </header>

  <main id="conteudo" class="login-main">
    <div class="login-card">
      <h1>Acesse sua conta</h1>
      <p class="login-intro">Escolha seu perfil para entrar.</p>

      <?php if ($erro): ?>
        <p class="form-message is-error"><?= e($erro) ?></p>
      <?php endif; ?>

      <form id="login-form" action="login.php" method="post">
        <fieldset>
          <legend class="visually-hidden">Tipo de perfil</legend>
          <div class="role-tabs">
            <input type="radio" name="perfil" id="perfil-egresso" value="egresso" <?= $perfilSelecionado === 'egresso' ? 'checked' : '' ?>>
            <label for="perfil-egresso">Egresso</label>

            <input type="radio" name="perfil" id="perfil-empresa" value="empresa" <?= $perfilSelecionado === 'empresa' ? 'checked' : '' ?>>
            <label for="perfil-empresa">Empresa</label>

            <input type="radio" name="perfil" id="perfil-instituicao" value="instituicao" <?= $perfilSelecionado === 'instituicao' ? 'checked' : '' ?>>
            <label for="perfil-instituicao">Instituição</label>
          </div>
        </fieldset>

        <div class="field-group" data-role="egresso" <?= $perfilSelecionado === 'egresso' ? '' : 'hidden' ?>>
          <div class="field">
            <label for="cpf">CPF</label>
            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" autocomplete="username" data-mask="cpf" inputmode="numeric" value="<?= e($_POST['cpf'] ?? '') ?>">
          </div>
        </div>

        <div class="field-group" data-role="empresa" <?= $perfilSelecionado === 'empresa' ? '' : 'hidden' ?>>
          <div class="field">
            <label for="cnpj">CNPJ</label>
            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" autocomplete="username" value="<?= e($_POST['cnpj'] ?? '') ?>">
          </div>
        </div>

        <div class="field-group" data-role="instituicao" <?= $perfilSelecionado === 'instituicao' ? '' : 'hidden' ?>>
          <div class="field">
            <label for="codigo">Código institucional</label>
            <input type="text" id="codigo" name="codigo" placeholder="INST-000000" autocomplete="username" value="<?= e($_POST['codigo'] ?? '') ?>">
          </div>
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" placeholder="Sua senha" autocomplete="current-password">
        </div>

        <p class="forgot"><a href="#">Esqueci minha senha</a></p>

        <button type="submit" class="button button-primary button-block">Entrar</button>

        <p class="register-hint" data-role="egresso" <?= $perfilSelecionado === 'egresso' ? '' : 'hidden' ?>>Ainda não tem cadastro? <a href="cadastro-egresso.php">Criar conta</a></p>
        <p class="register-hint" data-role="empresa" <?= $perfilSelecionado === 'empresa' ? '' : 'hidden' ?>>Empresa ainda não parceira? <a href="#">Solicitar parceria</a></p>
        <p class="register-hint" data-role="instituicao" <?= $perfilSelecionado === 'instituicao' ? '' : 'hidden' ?>>Acesso institucional é concedido pela equipe do sistema.</p>
      </form>
    </div>
  </main>

  <script src="js/main.js"></script>
</body>
</html>
