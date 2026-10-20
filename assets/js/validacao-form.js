/**
 * Validação dos formulários no navegador.
 * Mostra uma mensagem vermelha embaixo de cada campo vazio ou inválido
 * e impede o envio enquanto houver erro.
 *
 * Como usar: inclua este arquivo na página (<script src=".../validacao-form.js" defer>).
 * Ele valida todos os <form> da página. Para deixar um formulário de fora,
 * coloque o atributo data-sem-validacao no <form>.
 *
 * Atributos opcionais nos campos:
 *   data-msg-vazio="..."   mensagem própria para campo obrigatório vazio
 *   data-igual-a="id"      o valor deve ser igual ao do campo com esse id (ex.: confirmar senha)
 *   data-msg-igual="..."   mensagem própria para quando os valores forem diferentes
 */
document.addEventListener("DOMContentLoaded", function () {
    // Estilo dos avisos (funciona em qualquer página, mesmo sem CSS próprio)
    const estilo = document.createElement("style");
    estilo.textContent =
        ".erro-campo{display:block;color:#c0392b;font-size:12px;margin-top:5px;}" +
        ".campo-invalido{border-color:#c0392b !important;}";
    document.head.appendChild(estilo);

    const TIPOS_IGNORADOS = ["hidden", "submit", "button", "reset", "file", "checkbox", "radio"];

    function camposDoFormulario(form) {
        return Array.from(form.querySelectorAll("input, textarea, select")).filter(function (campo) {
            return !campo.disabled && !TIPOS_IGNORADOS.includes(campo.type) && !campo.closest("[hidden]");
        });
    }

    // Devolve o texto do erro do campo, ou "" se estiver tudo certo
    function mensagemDeErro(campo) {
        const valor = campo.type === "password" ? campo.value : campo.value.trim();

        if (campo.required && valor === "") {
            return campo.dataset.msgVazio || "Preencha este campo.";
        }

        // Campo opcional e vazio: nada a validar
        if (valor === "") {
            return "";
        }

        if (campo.type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
            return "Informe um e-mail válido, como nome@email.com.";
        }

        if (campo.type === "tel") {
            const digitos = valor.replace(/\D/g, "");
            if (digitos.length < 10 || digitos.length > 11) {
                return "Telefone inválido. Use DDD + número.";
            }
        }

        if (campo.minLength > 0 && valor.length < campo.minLength) {
            return "Use pelo menos " + campo.minLength + " caracteres.";
        }

        if (campo.dataset.igualA) {
            const outro = document.getElementById(campo.dataset.igualA);
            if (outro && outro.value !== campo.value) {
                return campo.dataset.msgIgual || "Os valores não coincidem.";
            }
        }

        return "";
    }

    function mostrarErro(campo, mensagem) {
        campo.classList.add("campo-invalido");
        campo.setAttribute("aria-invalid", "true");

        const aviso = document.createElement("span");
        aviso.className = "erro-campo";
        aviso.dataset.para = campo.id || campo.name;
        aviso.textContent = mensagem;

        const grupo = campo.closest(".form-group, .field");
        if (grupo) {
            grupo.appendChild(aviso);
        } else {
            campo.insertAdjacentElement("afterend", aviso);
        }
    }

    function limparCampo(campo) {
        campo.classList.remove("campo-invalido");
        campo.removeAttribute("aria-invalid");

        const chave = campo.id || campo.name;
        document.querySelectorAll(".erro-campo").forEach(function (aviso) {
            if (aviso.dataset.para === chave) {
                aviso.remove();
            }
        });
    }

    function limparFormulario(form) {
        form.querySelectorAll(".erro-campo").forEach(function (aviso) {
            aviso.remove();
        });
        form.querySelectorAll(".campo-invalido").forEach(function (campo) {
            campo.classList.remove("campo-invalido");
            campo.removeAttribute("aria-invalid");
        });
    }

    document.querySelectorAll("form").forEach(function (form) {
        if (form.hasAttribute("data-sem-validacao")) {
            return;
        }

        // Desliga o balão padrão do navegador, que é substituído pelas mensagens abaixo
        form.setAttribute("novalidate", "");

        form.addEventListener("submit", function (evento) {
            limparFormulario(form);

            let primeiroComErro = null;

            camposDoFormulario(form).forEach(function (campo) {
                const mensagem = mensagemDeErro(campo);
                if (mensagem !== "") {
                    mostrarErro(campo, mensagem);
                    if (primeiroComErro === null) {
                        primeiroComErro = campo;
                    }
                }
            });

            if (primeiroComErro !== null) {
                evento.preventDefault();
                primeiroComErro.focus();
            }
        });

        // Some o aviso do campo assim que a pessoa começa a corrigir
        form.addEventListener("input", function (evento) {
            limparCampo(evento.target);
        });
    });
});
