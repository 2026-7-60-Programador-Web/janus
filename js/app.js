/**
 * js/app.js
 * ---------------------------------------------------------------------
 * Liga cada página ao JanusData (js/data.js). Cada bloco só roda se os
 * elementos daquela página existirem, então este arquivo pode ser
 * incluído em todas as páginas sem problema.
 *
 * Isto é um MODELO de front-end: valida o mínimo (campos vazios) e
 * assume que os dados chegam mais ou menos corretos. Numa versão real
 * com PHP, a validação de verdade (CPF válido, senha forte, CSRF,
 * sanitização) tem que acontecer no servidor, não só aqui no JS.
 * ---------------------------------------------------------------------
 */

(function () {
  function mostrarMensagem(el, texto, tipo) {
    if (!el) return;
    el.textContent = texto;
    el.hidden = false;
    el.classList.remove('is-error', 'is-success');
    if (tipo) el.classList.add(tipo === 'erro' ? 'is-error' : 'is-success');
  }

  // Lê as competências digitadas num widget de chips a partir do DOM.
  // (main.js escreve o nome da competência como o primeiro nó de texto
  // de cada .chip, antes do botão de remover.)
  function lerChipsDoDOM(container) {
    if (!container) return [];
    return Array.prototype.map.call(container.querySelectorAll('.chip'), function (chip) {
      return chip.childNodes[0] ? chip.childNodes[0].textContent.trim() : '';
    }).filter(Boolean);
  }

  // Preenche um widget de chips já existente na página simulando cliques
  // em "Adicionar", pra reaproveitar a lógica (e o estado interno) do main.js.
  function preencherChips(widget, valores) {
    var input = widget.querySelector('.skill-input');
    var addBtn = widget.querySelector('.skill-add-btn');
    if (!input || !addBtn) return;
    valores.forEach(function (v) {
      input.value = v;
      addBtn.click();
    });
  }

  // Lê as formações (curso/instituição/ano) de um widget a partir dos
  // atributos data-* que main.js grava em cada <li> da lista.
  function lerFormacoesDoDOM(listEl) {
    if (!listEl) return [];
    return Array.prototype.map.call(listEl.querySelectorAll('.formacao-item'), function (li) {
      return {
        curso: li.getAttribute('data-curso') || '',
        instituicao: li.getAttribute('data-instituicao') || '',
        ano: li.getAttribute('data-ano') || ''
      };
    });
  }

  // Pré-preenche um widget de formações (modo edição), usando o método
  // exposto pelo próprio widget em main.js.
  function preencherFormacoes(widget, formacoes) {
    if (!widget || typeof widget.adicionarFormacao !== 'function') return;
    (formacoes || []).forEach(function (f) {
      widget.adicionarFormacao(f.curso, f.instituicao, f.ano);
    });
  }

  // Converte um arquivo pra data URL (base64), só usado para pré-visualização
  // de imagens dentro do próprio navegador. Documentos em PDF ficam só com
  // o nome do arquivo, sem prévia (numa versão real, o PHP guardaria o
  // arquivo em disco/storage e a tabela guardaria o caminho).
  function lerArquivoComoImagem(file) {
    return new Promise(function (resolve) {
      if (!file || file.type.indexOf('image/') !== 0) {
        resolve(file ? { nome: file.name, tipo: file.type, dataUrl: '' } : null);
        return;
      }
      var leitor = new FileReader();
      leitor.onload = function () {
        resolve({ nome: file.name, tipo: file.type, dataUrl: leitor.result });
      };
      leitor.onerror = function () {
        resolve({ nome: file.name, tipo: file.type, dataUrl: '' });
      };
      leitor.readAsDataURL(file);
    });
  }

  function encerrarSessaoAoSair() {
    document.querySelectorAll('.app-header-right a[href="login.html"]').forEach(function (link) {
      link.addEventListener('click', function () {
        JanusData.encerrarSessao();
      });
    });
  }
  encerrarSessaoAoSair();

  // ---------------- LOGIN ----------------

  var loginForm = document.getElementById('login-form');
  if (loginForm) {
    var loginMessage = document.getElementById('login-message');

    loginForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var role = document.querySelector('input[name="perfil"]:checked');
      role = role ? role.id.replace('perfil-', '') : 'egresso';
      var senha = document.getElementById('senha').value.trim();

      if (role === 'egresso') {
        var cpf = document.getElementById('cpf').value.trim();
        var egresso = JanusData.buscarEgressoPorCpf(cpf);

        if (!egresso) {
          mostrarMensagem(loginMessage, 'CPF não encontrado. Verifique ou crie uma conta.', 'erro');
          return;
        }
        if (senha !== egresso.senha) {
          mostrarMensagem(loginMessage, 'Senha incorreta (modelo de demonstração).', 'erro');
          return;
        }
        if (egresso.status === 'pendente') {
          mostrarMensagem(loginMessage, 'Seu cadastro ainda está em análise pela instituição.', 'erro');
          return;
        }
        if (egresso.status === 'recusado') {
          mostrarMensagem(loginMessage, 'Seu cadastro foi recusado pela instituição. Procure a coordenação.', 'erro');
          return;
        }

        JanusData.definirSessao({ tipo: 'egresso', id: egresso.id });
        window.location.href = 'perfil-egresso.html?id=' + egresso.id;
        return;
      }

      if (role === 'empresa') {
        var cnpj = document.getElementById('cnpj').value.trim();
        var parceira = JanusData.buscarParceiraPorCnpj(cnpj);

        if (!parceira || senha !== 'empresa123') {
          mostrarMensagem(loginMessage, 'CNPJ não encontrado ou senha incorreta.', 'erro');
          return;
        }
        JanusData.definirSessao({ tipo: 'empresa', id: parceira.id, identificacao: parceira.cnpj });
        window.location.href = 'busca-empresa.html';
        return;
      }

      if (role === 'instituicao') {
        var codigo = document.getElementById('codigo').value.trim().toUpperCase();

        if (codigo !== 'INST-0000' || senha !== 'admin123') {
          mostrarMensagem(loginMessage, 'Código ou senha incorretos.', 'erro');
          return;
        }
        JanusData.definirSessao({ tipo: 'instituicao' });
        window.location.href = 'painel-admin.html';
      }
    });
  }

  // ---------------- CADASTRO DE EGRESSO ----------------

  var cadastroForm = document.getElementById('cadastro-form');
  if (cadastroForm) {
    var cadastroMessage = document.getElementById('cadastro-message');

    // Pré-visualização da foto de perfil escolhida no cadastro. Sem
    // foto nenhuma, nem o avatar nem a imagem aparecem (ainda não há
    // nome/iniciais pra mostrar num avatar vazio).
    var cadastroFotoInput = document.getElementById('cadastro-foto-input');
    if (cadastroFotoInput) {
      cadastroFotoInput.addEventListener('change', function (event) {
        var file = event.target.files[0];
        var fotoPreview = document.getElementById('cadastro-foto-preview');
        var avatarPreview = document.getElementById('cadastro-avatar-preview');
        if (!file) {
          fotoPreview.hidden = true;
          avatarPreview.hidden = true;
          return;
        }
        var leitor = new FileReader();
        leitor.onload = function () {
          fotoPreview.src = leitor.result;
          fotoPreview.hidden = false;
          avatarPreview.hidden = true;
        };
        leitor.readAsDataURL(file);
      });
    }

    cadastroForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var nome = document.getElementById('nome').value.trim();
      var cpf = document.getElementById('cpf').value.trim();
      var telefone = document.getElementById('telefone').value.trim();
      var email = document.getElementById('email').value.trim();
      var senha = document.getElementById('senha-cadastro').value.trim();
      var confirmarSenha = document.getElementById('confirmar-senha-cadastro').value.trim();
      var arqDocumentos = cadastroForm.querySelector('[name="documentos_pdf"]').files[0];

      if (!nome || !cpf || !telefone || !email || !senha || !confirmarSenha || !arqDocumentos) {
        mostrarMensagem(cadastroMessage, 'Preencha todos os campos antes de enviar.', 'erro');
        return;
      }
      if (senha !== confirmarSenha) {
        mostrarMensagem(cadastroMessage, 'A confirmação de senha precisa ser igual à senha.', 'erro');
        return;
      }
      if (JanusData.buscarEgressoPorCpf(cpf)) {
        mostrarMensagem(cadastroMessage, 'Já existe um cadastro com este CPF.', 'erro');
        return;
      }

      Promise.all([
        lerArquivoComoImagem(arqDocumentos)
      ]).then(function (resultados) {
        JanusData.cadastrarEgresso({
          nome: nome,
          cpf: cpf,
          senha: senha,
          email: email,
          telefone: telefone,
          foto: '',
          documentos: {
            pacotePdf: resultados[0]
          }
        });

        cadastroForm.hidden = true;
        if (cadastroMessage) cadastroMessage.hidden = true;
        document.getElementById('cadastro-confirmacao').hidden = false;
      });
    });
  }

  // ---------------- BUSCA (EMPRESA) ----------------

  var listaResultados = document.getElementById('lista-resultados');
  if (listaResultados) {
    var contagemEl = document.getElementById('resultados-contagem');
    var empresaNomeEl = document.getElementById('empresa-nome');
    var sessaoBusca = JanusData.obterSessao();

    if (sessaoBusca && sessaoBusca.tipo === 'empresa' && empresaNomeEl) {
      empresaNomeEl.textContent = 'Empresa parceira · CNPJ ' + sessaoBusca.identificacao;
    }

    function renderResultados(skillsDesejadas) {
      var resultado = JanusData.buscarPorCompetencias(skillsDesejadas);

      if (skillsDesejadas.length === 0) {
        resultado = JanusData.listarAprovados().map(function (e) {
          return { egresso: e, compativeis: null, totalCompetencias: (e.softSkills || []).length + (e.hardSkills || []).length };
        });
      }

      if (contagemEl) {
        contagemEl.textContent = skillsDesejadas.length === 0
          ? resultado.length + ' egresso(s) aprovado(s) no total'
          : resultado.length + ' egresso(s) encontrado(s), por compatibilidade';
      }

      listaResultados.innerHTML = '';

      if (resultado.length === 0) {
        var vazio = document.createElement('p');
        vazio.className = 'empty-state';
        vazio.textContent = 'Nenhum egresso aprovado tem essas competências ainda.';
        listaResultados.appendChild(vazio);
        return;
      }

      resultado.forEach(function (item) {
        var e = item.egresso;
        var li = document.createElement('li');
        li.className = 'result-item';

        var todasCompetencias = (e.hardSkills || []).concat(e.softSkills || []);
        var badge = item.compativeis === null
          ? '<span class="badge badge-neutral">' + item.totalCompetencias + ' competência(s)</span>'
          : '<span class="badge ' + (item.compativeis === skillsDesejadas.length ? 'badge-success' : 'badge-warning') + '">' + item.compativeis + '/' + skillsDesejadas.length + '</span>';

        var fotoHtml = e.foto
          ? '<img class="avatar" style="object-fit:cover;" src="' + e.foto + '" alt="">'
          : '<span class="avatar">' + JanusData.iniciais(e.nome) + '</span>';

        li.innerHTML =
          '<div class="result-item-top">' +
            '<div class="result-person">' +
              fotoHtml +
              '<div><h3>' + e.nome + '</h3><p>' + (todasCompetencias.join(', ') || 'Sem competências informadas') + '</p></div>' +
            '</div>' +
            badge +
          '</div>' +
          '<div class="result-actions">' +
            '<a class="button button-secondary" href="perfil-egresso.html?id=' + e.id + '">Ver perfil completo</a>' +
            '<a class="button button-primary" target="_blank" rel="noopener" data-contato="' + e.id + '">Entrar em contato</a>' +
          '</div>';

        listaResultados.appendChild(li);
      });

      listaResultados.querySelectorAll('[data-contato]').forEach(function (link) {
        var egresso = JanusData.buscarEgressoPorId(link.getAttribute('data-contato'));
        var mensagem = 'Olá, ' + egresso.nome + '! Vi seu perfil no Janus e gostaria de falar sobre uma oportunidade.';
        var url = JanusData.linkWhatsapp(egresso.telefone, mensagem);
        if (url) {
          link.href = url;
        } else {
          link.removeAttribute('href');
          link.classList.add('button-secondary');
          link.classList.remove('button-primary');
          link.textContent = 'Sem telefone cadastrado';
        }
      });
    }

    document.addEventListener('skillschange', function (event) {
      renderResultados(event.detail.skills);
    });

    renderResultados([]);
  }

  // ---------------- PAINEL ADMIN (INSTITUIÇÃO) ----------------

  var listaPendentes = document.getElementById('lista-pendentes');
  if (listaPendentes) {
    var listaAprovados = document.getElementById('lista-aprovados');
    var statAprovados = document.getElementById('stat-aprovados');
    var statPendentes = document.getElementById('stat-pendentes');
    var pendenteAbertoId = null;

    // ---- abas Egressos / Empresas ----
    var tabs = document.querySelectorAll('.admin-tab');
    var paineis = { egressos: document.getElementById('painel-egressos'), empresas: document.getElementById('painel-empresas') };
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.classList.remove('is-active'); });
        tab.classList.add('is-active');
        Object.keys(paineis).forEach(function (chave) {
          paineis[chave].hidden = chave !== tab.getAttribute('data-tab');
        });
      });
    });

    function docBlocoHtml(titulo, doc) {
      if (!doc || (!doc.dataUrl && !doc.nome)) {
        return '<div class="detail-doc"><p>' + titulo + '</p><p class="doc-placeholder">Não enviado</p></div>';
      }
      if (doc.dataUrl) {
        return '<div class="detail-doc"><p>' + titulo + '</p><img src="' + doc.dataUrl + '" alt="' + titulo + '"></div>';
      }
      return '<div class="detail-doc"><p>' + titulo + '</p><p class="doc-placeholder">Arquivo anexado: ' + doc.nome + '</p></div>';
    }

    function renderDetalhe(e) {
      var docs = e.documentos || {};

      return (
        '<div class="detail-panel">' +
          '<dl class="detail-grid">' +
            '<dt>Nome completo</dt><dd>' + e.nome + '</dd>' +
            '<dt>CPF</dt><dd>' + e.cpf + '</dd>' +
            '<dt>E-mail</dt><dd>' + (e.email || '—') + '</dd>' +
            '<dt>Telefone</dt><dd>' + (e.telefone || '—') + '</dd>' +
          '</dl>' +
          '<p class="section-label">Documentos enviados</p>' +
          '<div class="detail-docs">' +
            docBlocoHtml('Comprovante de residência, certificado e identidade (PDF)', docs.pacotePdf) +
          '</div>' +
          '<div class="pending-actions">' +
            '<button type="button" class="button button-approve" data-aprovar="' + e.id + '">Aceitar cadastro</button>' +
            '<button type="button" class="button button-secondary" data-recusar="' + e.id + '">Recusar</button>' +
          '</div>' +
        '</div>'
      );
    }

    function renderPainel() {
      var pendentes = JanusData.listarPendentes();
      var aprovados = JanusData.listarAprovados();
      statAprovados.textContent = aprovados.length;
      statPendentes.textContent = pendentes.length;

      // ---- pendentes ----
      listaPendentes.innerHTML = '';
      if (pendentes.length === 0) {
        listaPendentes.innerHTML = '<p class="empty-state">Nenhum cadastro pendente no momento.</p>';
      } else {
        pendentes.forEach(function (e) {
          var aberto = pendenteAbertoId === e.id;
          var div = document.createElement('div');
          div.className = 'pending-item' + (aberto ? ' is-open' : '');
          var fotoHtmlPendente = e.foto
            ? '<img class="avatar" style="object-fit:cover;" src="' + e.foto + '" alt="">'
            : '<span class="avatar">' + JanusData.iniciais(e.nome) + '</span>';
          div.innerHTML =
            '<button type="button" class="list-item-clickable" data-abrir="' + e.id + '">' +
              '<div class="pending-top">' +
                '<div class="result-person">' +
                  fotoHtmlPendente +
                  '<div><h3>' + e.nome + '</h3><p>' + (e.telefone || 'Sem telefone informado') + '</p></div>' +
                '</div>' +
                '<span class="badge badge-warning">Pendente</span>' +
              '</div>' +
            '</button>' +
            (aberto ? renderDetalhe(e) : '');
          listaPendentes.appendChild(div);
        });
      }

      listaPendentes.querySelectorAll('[data-abrir]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var id = Number(btn.getAttribute('data-abrir'));
          pendenteAbertoId = pendenteAbertoId === id ? null : id;
          renderPainel();
        });
      });
      listaPendentes.querySelectorAll('[data-aprovar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          JanusData.aprovarEgresso(btn.getAttribute('data-aprovar'));
          pendenteAbertoId = null;
          renderPainel();
        });
      });
      listaPendentes.querySelectorAll('[data-recusar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          JanusData.recusarEgresso(btn.getAttribute('data-recusar'));
          pendenteAbertoId = null;
          renderPainel();
        });
      });

      // ---- aprovados ----
      listaAprovados.innerHTML = '';
      if (aprovados.length === 0) {
        listaAprovados.innerHTML = '<p class="empty-state">Nenhum egresso aprovado ainda.</p>';
      } else {
        aprovados.forEach(function (e) {
          var div = document.createElement('div');
          div.className = 'approved-item';
          var fotoHtmlAprovado = e.foto
            ? '<img class="avatar" style="object-fit:cover;" src="' + e.foto + '" alt="">'
            : '<span class="avatar">' + JanusData.iniciais(e.nome) + '</span>';
          div.innerHTML =
            '<div class="result-person">' +
              fotoHtmlAprovado +
              '<div><h3>' + e.nome + '</h3><p>' + (e.telefone || 'Sem telefone informado') + '</p></div>' +
            '</div>' +
            '<div class="approved-item-actions">' +
              '<a class="button button-secondary" href="perfil-egresso.html?id=' + e.id + '">Ver perfil</a>' +
              '<button type="button" class="button button-danger" data-remover="' + e.id + '">Remover</button>' +
            '</div>';
          listaAprovados.appendChild(div);
        });
      }

      listaAprovados.querySelectorAll('[data-remover]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (!window.confirm('Remover este egresso do sistema? Essa ação não pode ser desfeita.')) return;
          JanusData.removerEgresso(btn.getAttribute('data-remover'));
          renderPainel();
        });
      });
    }

    renderPainel();

    // ---- cadastro manual ----
    var manualToggle = document.getElementById('manual-add-toggle');
    var manualForm = document.getElementById('manual-add-form');
    manualToggle.addEventListener('click', function () {
      manualForm.hidden = !manualForm.hidden;
      manualToggle.textContent = manualForm.hidden ? 'Adicionar egresso' : 'Fechar formulário';
    });

    manualForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var manualMessage = document.getElementById('manual-add-message');
      var nome = document.getElementById('manual-nome').value.trim();
      var cpf = document.getElementById('manual-cpf').value.trim();
      var senhaManual = document.getElementById('manual-senha').value;
      var telefone = document.getElementById('manual-telefone').value.trim();
      var formacoes = lerFormacoesDoDOM(manualForm.querySelector('.formacao-list'));
      var hardSkills = lerChipsDoDOM(manualForm.querySelector('.chip-list'));

      if (!nome || !cpf) {
        mostrarMensagem(manualMessage, 'Preencha ao menos nome e CPF.', 'erro');
        return;
      }
      if (!senhaManual || senhaManual.length < 6) {
        mostrarMensagem(manualMessage, 'Defina uma senha de acesso com pelo menos 6 caracteres — sem ela o egresso não consegue entrar.', 'erro');
        return;
      }
      if (JanusData.buscarEgressoPorCpf(cpf)) {
        mostrarMensagem(manualMessage, 'Já existe um cadastro com este CPF.', 'erro');
        return;
      }

      JanusData.adicionarEgressoManual({ nome: nome, cpf: cpf, senha: senhaManual, telefone: telefone, formacao: formacoes[0] || null, hardSkills: hardSkills });
      mostrarMensagem(manualMessage, nome + ' foi cadastrado(a) e já aparece como aprovado(a). Repasse o CPF e a senha definida para ele(a) acessar o perfil.', 'sucesso');
      manualForm.reset();
      manualForm.querySelector('.chip-list').innerHTML = '';
      manualForm.querySelector('.formacao-list').innerHTML = '';
      renderPainel();
    });

    // ---- empresas parceiras ----
    var listaParceiras = document.getElementById('lista-parceiras');

    function renderParceiras() {
      var parceiras = JanusData.listarParceiras();
      listaParceiras.innerHTML = '';
      if (parceiras.length === 0) {
        listaParceiras.innerHTML = '<p class="empty-state">Nenhuma empresa parceira cadastrada ainda.</p>';
        return;
      }
      parceiras.forEach(function (p) {
        var div = document.createElement('div');
        div.className = 'partner-item';
        div.innerHTML =
          '<p>' + p.nome + (p.cnpj ? ' <span class="field-hint" style="display:inline;">· ' + p.cnpj + '</span>' : '') + '</p>' +
          '<div style="display:flex; align-items:center; gap:0.6rem;">' +
            '<span class="badge badge-success">Ativa</span>' +
            '<button type="button" class="button button-danger" data-remover-parceira="' + p.id + '">Remover</button>' +
          '</div>';
        listaParceiras.appendChild(div);
      });

      listaParceiras.querySelectorAll('[data-remover-parceira]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          if (!window.confirm('Remover esta empresa parceira?')) return;
          JanusData.removerParceira(btn.getAttribute('data-remover-parceira'));
          renderParceiras();
        });
      });
    }

    renderParceiras();

    var parceiraToggle = document.getElementById('parceira-add-toggle');
    var parceiraForm = document.getElementById('parceira-add-form');
    parceiraToggle.addEventListener('click', function () {
      parceiraForm.hidden = !parceiraForm.hidden;
      parceiraToggle.textContent = parceiraForm.hidden ? 'Adicionar empresa parceira' : 'Fechar formulário';
    });

    parceiraForm.addEventListener('submit', function (event) {
      event.preventDefault();
      var parceiraMessage = document.getElementById('parceira-add-message');
      var nome = document.getElementById('parceira-nome').value.trim();
      var cnpj = document.getElementById('parceira-cnpj').value.trim();

      if (!nome) {
        mostrarMensagem(parceiraMessage, 'Informe o nome da empresa.', 'erro');
        return;
      }

      JanusData.adicionarParceira({ nome: nome, cnpj: cnpj });
      parceiraForm.reset();
      parceiraForm.hidden = true;
      parceiraToggle.textContent = 'Adicionar empresa parceira';
      renderParceiras();
    });
  }

  // ---------------- PERFIL DO EGRESSO ----------------

  var perfilNomeEl = document.getElementById('perfil-nome');
  if (perfilNomeEl) {
    var params = new URLSearchParams(window.location.search);
    var perfilId = params.get('id');
    var egresso = perfilId ? JanusData.buscarEgressoPorId(perfilId) : null;
    var perfilMessage = document.getElementById('perfil-message');
    var sessaoPerfil = JanusData.obterSessao();

    if (!egresso) {
      mostrarMensagem(perfilMessage, 'Perfil não encontrado.', 'erro');
      document.getElementById('perfil-visualizacao').hidden = true;
    } else {
      var souOProprio = !!(sessaoPerfil && sessaoPerfil.tipo === 'egresso' && Number(sessaoPerfil.id) === egresso.id);
      var vindoDeEmpresa = !!(sessaoPerfil && sessaoPerfil.tipo === 'empresa');
      var vindoDeInstituicao = !!(sessaoPerfil && sessaoPerfil.tipo === 'instituicao');

      document.getElementById('header-meta').textContent = souOProprio ? 'Meu currículo' : 'Perfil do egresso';
      if (vindoDeEmpresa) {
        var voltarLink = document.getElementById('voltar-link');
        voltarLink.hidden = false;
        voltarLink.textContent = 'Voltar à busca';
        voltarLink.href = 'busca-empresa.html';
      } else if (vindoDeInstituicao) {
        var voltarLinkAdmin = document.getElementById('voltar-link');
        voltarLinkAdmin.hidden = false;
        voltarLinkAdmin.textContent = 'Voltar ao painel';
        voltarLinkAdmin.href = 'painel-admin.html';
      }

      function preencherVisualizacao() {
        var fotoEl = document.getElementById('perfil-foto');
        var avatarEl = document.getElementById('perfil-avatar');
        if (egresso.foto) {
          fotoEl.src = egresso.foto;
          fotoEl.hidden = false;
          avatarEl.hidden = true;
        } else {
          fotoEl.hidden = true;
          avatarEl.hidden = false;
          avatarEl.textContent = JanusData.iniciais(egresso.nome);
        }

        document.getElementById('perfil-nome').textContent = egresso.nome;
        var formacaoPrincipal = (egresso.formacoes && egresso.formacoes[0]) || null;
        document.getElementById('perfil-formacao-principal').textContent = formacaoPrincipal
          ? formacaoPrincipal.curso + ' · ' + formacaoPrincipal.instituicao
          : 'Formação não informada';

        var contatoEl = document.getElementById('perfil-contato');
        var linhas = [];
        if (egresso.email) linhas.push('<span><strong>E-mail:</strong> ' + egresso.email + '</span>');
        if (egresso.telefone) linhas.push('<span><strong>Telefone:</strong> ' + egresso.telefone + '</span>');
        if (egresso.endereco) linhas.push('<span><strong>Endereço:</strong> ' + egresso.endereco + '</span>');
        if (egresso.genero) linhas.push('<span><strong>Gênero:</strong> ' + egresso.genero + '</span>');
        if (egresso.dataNascimento) linhas.push('<span><strong>Nascimento:</strong> ' + egresso.dataNascimento + '</span>');
        contatoEl.innerHTML = linhas.join('');

        var bioEl = document.getElementById('perfil-bio-visualizacao');
        if (egresso.bio) {
          bioEl.textContent = egresso.bio;
          bioEl.classList.remove('profile-bio-empty');
        } else {
          bioEl.textContent = souOProprio ? 'Você ainda não escreveu sobre você. Clique em "Editar meu perfil" para preencher.' : 'Este egresso ainda não preencheu esta seção.';
          bioEl.classList.add('profile-bio-empty');
        }

        function preencherChipsView(elId, lista) {
          var el = document.getElementById(elId);
          el.innerHTML = '';
          if (!lista || lista.length === 0) {
            el.innerHTML = '<p class="empty-state">Nenhuma competência cadastrada ainda.</p>';
            return;
          }
          lista.forEach(function (c) {
            var chip = document.createElement('span');
            chip.className = 'chip';
            chip.textContent = c;
            el.appendChild(chip);
          });
        }
        preencherChipsView('perfil-soft-visualizacao', egresso.softSkills);
        preencherChipsView('perfil-hard-visualizacao', egresso.hardSkills);

        var formacoesEl = document.getElementById('perfil-formacoes-visualizacao');
        formacoesEl.innerHTML = '';
        if (!egresso.formacoes || egresso.formacoes.length === 0) {
          formacoesEl.innerHTML = '<li class="empty-state">Nenhuma formação cadastrada ainda.</li>';
        } else {
          egresso.formacoes.forEach(function (f) {
            var li = document.createElement('li');
            li.textContent = f.curso + ' — ' + f.instituicao + (f.ano ? ' (' + f.ano + ')' : '');
            formacoesEl.appendChild(li);
          });
        }
      }

      preencherVisualizacao();

      var editarBtn = document.getElementById('editar-perfil-btn');
      var contatoBtn = document.getElementById('contato-whatsapp-btn');
      var contatoSemTelefone = document.getElementById('contato-sem-telefone');
      var removerBtnEl = document.getElementById('remover-perfil-btn');
      var visualizacao = document.getElementById('perfil-visualizacao');
      var edicao = document.getElementById('perfil-edicao');
      var fotoEditadaDataUrl = null;

      // Cada tipo de visitante só pode ver o botão que lhe cabe: o próprio
      // egresso só edita; empresa só entra em contato; instituição só
      // remove. Escondemos os outros dois explicitamente aqui pra nenhum
      // deles aparecer junto por engano.
      if (souOProprio) {
        editarBtn.hidden = false;
        contatoBtn.hidden = true;
        contatoSemTelefone.hidden = true;
        removerBtnEl.hidden = true;
        editarBtn.addEventListener('click', function () {
          document.getElementById('perfil-email-input').value = egresso.email || '';
          document.getElementById('perfil-telefone-input').value = egresso.telefone || '';
          document.getElementById('perfil-endereco-input').value = egresso.endereco || '';
          document.getElementById('perfil-genero-input').value = egresso.genero || '';
          document.getElementById('perfil-nascimento-input').value = egresso.dataNascimento || '';
          document.getElementById('perfil-bio-input').value = egresso.bio || '';
          fotoEditadaDataUrl = egresso.foto || null;

          var avatarPreview = document.getElementById('edicao-avatar-preview');
          var fotoPreview = document.getElementById('edicao-foto-preview');
          if (egresso.foto) {
            fotoPreview.src = egresso.foto;
            fotoPreview.hidden = false;
            avatarPreview.hidden = true;
          } else {
            fotoPreview.hidden = true;
            avatarPreview.hidden = false;
            avatarPreview.textContent = JanusData.iniciais(egresso.nome);
          }

          var widgetsSkill = edicao.querySelectorAll('[data-skill-widget]');
          var softWidget = widgetsSkill[0];
          var hardWidget = widgetsSkill[1];
          softWidget.parentElement.querySelector('[data-lista="soft"]').innerHTML = '';
          hardWidget.parentElement.querySelector('[data-lista="hard"]').innerHTML = '';
          preencherChips(softWidget, egresso.softSkills || []);
          preencherChips(hardWidget, egresso.hardSkills || []);

          var formacaoWidget = edicao.querySelector('[data-formacao-widget]');
          edicao.querySelector('.formacao-list').innerHTML = '';
          preencherFormacoes(formacaoWidget, egresso.formacoes || []);

          visualizacao.hidden = true;
          edicao.hidden = false;
        });

        document.getElementById('perfil-foto-input').addEventListener('change', function (event) {
          var file = event.target.files[0];
          if (!file) return;
          var leitor = new FileReader();
          leitor.onload = function () {
            fotoEditadaDataUrl = leitor.result;
            var fotoPreview = document.getElementById('edicao-foto-preview');
            var avatarPreview = document.getElementById('edicao-avatar-preview');
            fotoPreview.src = fotoEditadaDataUrl;
            fotoPreview.hidden = false;
            avatarPreview.hidden = true;
          };
          leitor.readAsDataURL(file);
        });

        document.getElementById('cancelar-edicao-btn').addEventListener('click', function () {
          edicao.hidden = true;
          visualizacao.hidden = false;
        });

        edicao.addEventListener('submit', function (event) {
          event.preventDefault();
          var widgetsSkill = edicao.querySelectorAll('[data-skill-widget]');
          var softSkills = lerChipsDoDOM(widgetsSkill[0].parentElement.querySelector('[data-lista="soft"]'));
          var hardSkills = lerChipsDoDOM(widgetsSkill[1].parentElement.querySelector('[data-lista="hard"]'));
          var formacoes = lerFormacoesDoDOM(edicao.querySelector('.formacao-list'));

          JanusData.atualizarPerfil(egresso.id, {
            email: document.getElementById('perfil-email-input').value.trim(),
            telefone: document.getElementById('perfil-telefone-input').value.trim(),
            endereco: document.getElementById('perfil-endereco-input').value.trim(),
            genero: document.getElementById('perfil-genero-input').value,
            dataNascimento: document.getElementById('perfil-nascimento-input').value,
            bio: document.getElementById('perfil-bio-input').value.trim(),
            foto: fotoEditadaDataUrl || '',
            softSkills: softSkills,
            hardSkills: hardSkills,
            formacoes: formacoes
          });

          egresso = JanusData.buscarEgressoPorId(egresso.id);
          preencherVisualizacao();
          edicao.hidden = true;
          visualizacao.hidden = false;
        });
      } else if (vindoDeEmpresa) {
        editarBtn.hidden = true;
        removerBtnEl.hidden = true;
        var mensagem = 'Olá, ' + egresso.nome + '! Vi seu perfil no Janus e gostaria de falar sobre uma oportunidade.';
        var url = JanusData.linkWhatsapp(egresso.telefone, mensagem);
        if (url) {
          contatoBtn.href = url;
          contatoBtn.hidden = false;
        } else {
          contatoSemTelefone.hidden = false;
        }
      } else if (vindoDeInstituicao) {
        // A instituição vê o mesmo currículo que a empresa vê e também
        // pode entrar em contato — mas nunca edita as informações do
        // egresso (só remover ou visualizar).
        editarBtn.hidden = true;
        removerBtnEl.hidden = false;
        removerBtnEl.addEventListener('click', function () {
          if (!window.confirm('Remover este egresso do sistema? Essa ação não pode ser desfeita.')) return;
          JanusData.removerEgresso(egresso.id);
          window.location.href = 'painel-admin.html';
        });

        var mensagemInstituicao = 'Olá, ' + egresso.nome + '! Sou da instituição e gostaria de falar com você.';
        var urlInstituicao = JanusData.linkWhatsapp(egresso.telefone, mensagemInstituicao);
        if (urlInstituicao) {
          contatoBtn.href = urlInstituicao;
          contatoBtn.hidden = false;
        } else {
          contatoSemTelefone.hidden = false;
        }
      }
    }
  }
})();
