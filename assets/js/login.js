document.addEventListener("DOMContentLoaded", function () {
    const botaoToggle = document.getElementById("toggleSenha");
    const campoSenha = document.getElementById("senha");

    if (!botaoToggle || !campoSenha) {
        return;
    }

    botaoToggle.addEventListener("click", function () {
        const visivel = campoSenha.type === "text";

        campoSenha.type = visivel ? "password" : "text";
        botaoToggle.setAttribute("aria-label", visivel ? "Mostrar senha" : "Ocultar senha");
        botaoToggle.textContent = visivel ? "Mostrar" : "Ocultar";
    });
});