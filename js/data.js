var JanusData = (function () {
  var CHAVE_EGRESSOS = 'jg_egressos';
  var CHAVE_PARCEIRAS = 'jg_parceiras';

  function ler(chave) {
    try {
      return JSON.parse(localStorage.getItem(chave)) || [];
    } catch (e) {
      return [];
    }
  }

  function salvar(chave, valor) {
    try {
      localStorage.setItem(chave, JSON.stringify(valor));
      return true;
    } catch (e) {
      return false;
    }
  }

  function gerarId(lista) {
    return lista.reduce(function (max, item) { return Math.max(max, item.id); }, 0) + 1;
  }

  function somenteDigitos(valor) {
    return (valor || '').replace(/\D/g, '');
  }

  function iniciais(nome) {
    var partes = (nome || '').trim().split(/\s+/);
    var a = partes[0] ? partes[0][0] : '';
    var b = partes.length > 1 ? partes[partes.length - 1][0] : '';
    return (a + b).toUpperCase();
  }

  // Deixa só dígitos no telefone e monta o link do WhatsApp (padrão BR: +55).
  function linkWhatsapp(telefone, mensagem) {
    var digitos = (telefone || '').replace(/\D/g, '');
    if (!digitos) return null;
    if (digitos.length <= 11) digitos = '55' + digitos;
    return 'https://wa.me/' + digitos + (mensagem ? '?text=' + encodeURIComponent(mensagem) : '');
  }

  // Sem dados de exemplo: as listas nascem vazias e só recebem
  // registros reais, feitos pelo cadastro do egresso ou pelo painel
  // do admin — igual à versão com banco de dados de verdade.

  return {
    // ---------------- EGRESSOS ----------------

    // PHP equivalente: INSERT INTO egressos (...) VALUES (...) com status = 'pendente'
    // Em produção, os documentos iriam pra disco/storage e a tabela guardaria só o caminho.
    cadastrarEgresso: function (dados) {
      var lista = ler(CHAVE_EGRESSOS);
      var novo = {
        id: gerarId(lista),
        nome: dados.nome,
        cpf: dados.cpf,
        senha: dados.senha || '',
        status: 'pendente',
        origem: 'cadastro',
        email: dados.email || '',
        telefone: dados.telefone || '',
        endereco: '',
        genero: '',
        dataNascimento: '',
        foto: dados.foto || '',
        bio: '',
        softSkills: [],
        hardSkills: [],
        formacoes: dados.formacao ? [dados.formacao] : [],
        documentos: dados.documentos || {}
      };
      lista.push(novo);
      salvar(CHAVE_EGRESSOS, lista);
      return novo;
    },

    // PHP equivalente: INSERT INTO egressos (...) VALUES (...) com status = 'aprovado'
    // (cadastro manual feito pela instituição já nasce aprovado)
    // A senha é sempre definida pela instituição no momento do cadastro —
    // sem ela, o egresso não tem como entrar no sistema depois.
    adicionarEgressoManual: function (dados) {
      var lista = ler(CHAVE_EGRESSOS);
      var novo = {
        id: gerarId(lista),
        nome: dados.nome,
        cpf: dados.cpf,
        senha: dados.senha,
        status: 'aprovado',
        origem: 'manual',
        email: dados.email || '',
        telefone: dados.telefone || '',
        endereco: '', genero: '', dataNascimento: '', foto: '',
        bio: '',
        softSkills: [],
        hardSkills: dados.hardSkills || [],
        formacoes: dados.formacao ? [dados.formacao] : [],
        documentos: {}
      };
      lista.push(novo);
      salvar(CHAVE_EGRESSOS, lista);
      return novo;
    },

    // PHP equivalente: SELECT * FROM egressos WHERE status = 'pendente'
    listarPendentes: function () {
      return ler(CHAVE_EGRESSOS).filter(function (e) { return e.status === 'pendente'; });
    },

    // PHP equivalente: SELECT * FROM egressos WHERE status = 'aprovado'
    listarAprovados: function () {
      return ler(CHAVE_EGRESSOS).filter(function (e) { return e.status === 'aprovado'; });
    },

    // PHP equivalente: SELECT * FROM egressos WHERE id = ?
    buscarEgressoPorId: function (id) {
      return ler(CHAVE_EGRESSOS).filter(function (e) { return e.id === Number(id); })[0] || null;
    },

    // PHP equivalente: SELECT * FROM egressos WHERE cpf = ?
    buscarEgressoPorCpf: function (cpf) {
      return ler(CHAVE_EGRESSOS).filter(function (e) { return e.cpf === cpf; })[0] || null;
    },

    // PHP equivalente: UPDATE egressos SET status = 'aprovado' WHERE id = ?
    aprovarEgresso: function (id) {
      var lista = ler(CHAVE_EGRESSOS);
      lista.forEach(function (e) { if (e.id === Number(id)) e.status = 'aprovado'; });
      salvar(CHAVE_EGRESSOS, lista);
    },

    // PHP equivalente: UPDATE egressos SET status = 'recusado' WHERE id = ?
    recusarEgresso: function (id) {
      var lista = ler(CHAVE_EGRESSOS);
      lista.forEach(function (e) { if (e.id === Number(id)) e.status = 'recusado'; });
      salvar(CHAVE_EGRESSOS, lista);
    },

    // PHP equivalente: DELETE FROM egressos WHERE id = ?
    removerEgresso: function (id) {
      var lista = ler(CHAVE_EGRESSOS).filter(function (e) { return e.id !== Number(id); });
      salvar(CHAVE_EGRESSOS, lista);
    },

    // PHP equivalente: UPDATE egressos SET ... WHERE id = ?
    // (soft/hard skills e formações normalmente ficariam em tabelas à parte)
    atualizarPerfil: function (id, dados) {
      var lista = ler(CHAVE_EGRESSOS);
      lista.forEach(function (e) {
        if (e.id !== Number(id)) return;
        ['bio', 'email', 'telefone', 'endereco', 'genero', 'dataNascimento', 'foto', 'softSkills', 'hardSkills', 'formacoes'].forEach(function (campo) {
          if (dados[campo] !== undefined) e[campo] = dados[campo];
        });
      });
      salvar(CHAVE_EGRESSOS, lista);
    },

    // PHP equivalente: SELECT * FROM egressos WHERE status = 'aprovado'
    // + no PHP a ordenação por compatibilidade seria feita com um COUNT()
    // de competências em comum, num JOIN com a tabela de busca da empresa.
    buscarPorCompetencias: function (competenciasDesejadas) {
      var termos = competenciasDesejadas.map(function (c) { return c.toLowerCase(); });
      var aprovados = ler(CHAVE_EGRESSOS).filter(function (e) { return e.status === 'aprovado'; });

      var resultado = aprovados.map(function (e) {
        var todas = (e.softSkills || []).concat(e.hardSkills || []);
        var compativeis = todas.filter(function (c) {
          return termos.indexOf(c.toLowerCase()) !== -1;
        });
        return { egresso: e, compativeis: compativeis.length, totalCompetencias: todas.length };
      });

      if (termos.length > 0) {
        resultado = resultado.filter(function (r) { return r.compativeis > 0; });
      }

      resultado.sort(function (a, b) { return b.compativeis - a.compativeis; });
      return resultado;
    },

    iniciais: iniciais,
    linkWhatsapp: linkWhatsapp,

    // ---------------- EMPRESAS PARCEIRAS (gerenciadas pela instituição) ----------------

    // PHP equivalente: SELECT * FROM empresas_parceiras
    listarParceiras: function () {
      return ler(CHAVE_PARCEIRAS);
    },

    // PHP equivalente: INSERT INTO empresas_parceiras (...) VALUES (...)
    adicionarParceira: function (dados) {
      var lista = ler(CHAVE_PARCEIRAS);
      var nova = { id: gerarId(lista), nome: dados.nome, cnpj: dados.cnpj || '', status: 'ativa' };
      lista.push(nova);
      salvar(CHAVE_PARCEIRAS, lista);
      return nova;
    },

    // PHP equivalente: DELETE FROM empresas_parceiras WHERE id = ?
    removerParceira: function (id) {
      var lista = ler(CHAVE_PARCEIRAS).filter(function (p) { return p.id !== Number(id); });
      salvar(CHAVE_PARCEIRAS, lista);
    },

    // PHP equivalente: SELECT * FROM empresas_parceiras WHERE cnpj = ?
    // Compara só os dígitos, pra funcionar com ou sem a máscara do CNPJ.
    buscarParceiraPorCnpj: function (cnpj) {
      var alvo = somenteDigitos(cnpj);
      if (!alvo) return null;
      return ler(CHAVE_PARCEIRAS).filter(function (p) { return somenteDigitos(p.cnpj) === alvo; })[0] || null;
    },

    // ---------------- SESSÃO (login simulado, sem segurança real) ----------------

    definirSessao: function (sessao) {
      sessionStorage.setItem('jg_sessao', JSON.stringify(sessao));
    },

    obterSessao: function () {
      try {
        return JSON.parse(sessionStorage.getItem('jg_sessao'));
      } catch (e) {
        return null;
      }
    },

    encerrarSessao: function () {
      sessionStorage.removeItem('jg_sessao');
    }
  };
})();
