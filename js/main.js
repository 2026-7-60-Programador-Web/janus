(function () {
  var navToggle = document.getElementById('nav-toggle');
  var mobileMenu = document.getElementById('mobile-menu');

  if (navToggle && mobileMenu) {
    navToggle.addEventListener('click', function () {
      var isOpen = !mobileMenu.hidden;
      mobileMenu.hidden = isOpen;
      navToggle.setAttribute('aria-expanded', String(!isOpen));
    });
  }

  var roleInputs = document.querySelectorAll('input[name="perfil"]');

  if (roleInputs.length) {
    var fieldGroups = document.querySelectorAll('.field-group');
    var registerHints = document.querySelectorAll('.register-hint');

    var updateRole = function () {
      var selected = document.querySelector('input[name="perfil"]:checked');
      if (!selected) return;
      var role = selected.id.replace('perfil-', '');

      fieldGroups.forEach(function (group) {
        group.hidden = group.getAttribute('data-role') !== role;
      });

      registerHints.forEach(function (hint) {
        hint.hidden = hint.getAttribute('data-role') !== role;
      });
    };

    roleInputs.forEach(function (input) {
      input.addEventListener('change', updateRole);
    });

    updateRole();
  }

  var skillWidgets = document.querySelectorAll('[data-skill-widget]');

  skillWidgets.forEach(function (widget) {
    var input = widget.querySelector('.skill-input');
    var addBtn = widget.querySelector('.skill-add-btn');
    var errorMsg = widget.parentElement.querySelector('.skill-error');
    var chipList = widget.parentElement.querySelector('.chip-list');
    var chipStyle = widget.getAttribute('data-chip-style') === 'info' ? ' chip-info' : '';
    var skills = [];

    if (!input || !addBtn || !chipList) return;

    function render() {
      chipList.innerHTML = '';
      skills.forEach(function (skill, index) {
        var chip = document.createElement('span');
        chip.className = 'chip' + chipStyle;
        chip.textContent = skill;

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '\u00d7';
        removeBtn.setAttribute('aria-label', 'Remover ' + skill);
        removeBtn.addEventListener('click', function () {
          skills.splice(index, 1);
          render();
        });

        chip.appendChild(removeBtn);
        chipList.appendChild(chip);
      });

      widget.dispatchEvent(new CustomEvent('skillschange', {
        bubbles: true,
        detail: { skills: skills.slice() }
      }));
    }

    function addSkill() {
      var value = input.value.trim();
      if (!value) {
        if (errorMsg) errorMsg.hidden = false;
        return;
      }
      if (errorMsg) errorMsg.hidden = true;
      if (skills.indexOf(value) === -1) {
        skills.push(value);
        render();
      }
      input.value = '';
      input.focus();
    }

    addBtn.addEventListener('click', addSkill);

    input.addEventListener('input', function () {
      if (errorMsg && input.value.trim()) errorMsg.hidden = true;
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        addSkill();
      }
    });
  });

  // Widget de formações: curso + instituição + ano, cada um vira um item
  // da lista (com botão de remover). Os dados de cada item ficam em
  // atributos data-* do próprio <li>, pra js/app.js poder ler tudo de volta
  // na hora de salvar (ver lerFormacoesDoDOM em js/app.js).
  var formacaoWidgets = document.querySelectorAll('[data-formacao-widget]');

  formacaoWidgets.forEach(function (widget) {
    var cursoInput = widget.querySelector('.formacao-curso');
    var instituicaoInput = widget.querySelector('.formacao-instituicao');
    var anoInput = widget.querySelector('.formacao-ano');
    var addBtn = widget.querySelector('.formacao-add-btn');
    var errorMsg = widget.parentElement.querySelector('.formacao-error');
    var listEl = widget.parentElement.querySelector('.formacao-list');

    if (!cursoInput || !instituicaoInput || !addBtn || !listEl) return;

    function criarItem(curso, instituicao, ano) {
      var li = document.createElement('li');
      li.className = 'formacao-item';
      li.setAttribute('data-curso', curso);
      li.setAttribute('data-instituicao', instituicao);
      li.setAttribute('data-ano', ano || '');

      var texto = document.createElement('span');
      texto.textContent = curso + ' — ' + instituicao + (ano ? ' (' + ano + ')' : '');

      var removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.textContent = '\u00d7';
      removeBtn.setAttribute('aria-label', 'Remover formação ' + curso);
      removeBtn.addEventListener('click', function () {
        li.remove();
      });

      li.appendChild(texto);
      li.appendChild(removeBtn);
      listEl.appendChild(li);
    }

    // Exposto pra js/app.js poder pré-preencher formações já salvas (modo edição).
    widget.adicionarFormacao = criarItem;

    addBtn.addEventListener('click', function () {
      var curso = cursoInput.value.trim();
      var instituicao = instituicaoInput.value.trim();
      var ano = anoInput ? anoInput.value.trim() : '';

      if (!curso || !instituicao) {
        if (errorMsg) errorMsg.hidden = false;
        return;
      }
      if (errorMsg) errorMsg.hidden = true;

      criarItem(curso, instituicao, ano);
      cursoInput.value = '';
      instituicaoInput.value = '';
      if (anoInput) anoInput.value = '';
      cursoInput.focus();
    });
  });

  // Máscara de CPF: formata como 000.000.000-00 enquanto a pessoa
  // digita, mas o limite de tamanho conta só os 11 dígitos — os pontos
  // e o hífen são inseridos por cima e nunca contam pra esse limite.
  function formatarCpf(digitos) {
    digitos = digitos.slice(0, 11);
    var resultado = digitos.slice(0, 3);
    if (digitos.length > 3) resultado += '.' + digitos.slice(3, 6);
    if (digitos.length > 6) resultado += '.' + digitos.slice(6, 9);
    if (digitos.length > 9) resultado += '-' + digitos.slice(9, 11);
    return resultado;
  }

  document.querySelectorAll('[data-mask="cpf"]').forEach(function (input) {
    input.addEventListener('input', function () {
      var digitos = input.value.replace(/\D/g, '');
      input.value = formatarCpf(digitos);
    });
  });

  var uploadFields = document.querySelectorAll('[data-upload-field]');

  uploadFields.forEach(function (field) {
    var input = field.querySelector('[data-upload-input]');
    var textEl = field.querySelector('[data-upload-text]');
    if (!input || !textEl) return;

    var defaultText = textEl.textContent;

    input.addEventListener('change', function () {
      if (input.files && input.files.length) {
        var name = input.files[0].name;
        textEl.textContent = name.length > 22 ? name.slice(0, 19) + '...' : name;
        field.classList.add('is-filled');
      } else {
        textEl.textContent = defaultText;
        field.classList.remove('is-filled');
      }
    });
  });
})();
