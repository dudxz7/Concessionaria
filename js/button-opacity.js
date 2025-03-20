document.addEventListener("DOMContentLoaded", function () {
    // Pegando os campos de e-mail e senha
    const emailInput = document.getElementById("email");
    const senhaInput = document.getElementById("senha");
    const botao = document.getElementById("button");

    // Função para verificar se os campos estão preenchidos
    function verificarCampos() {
        const emailPreenchido = emailInput.value.trim() !== "";
        const senhaPreenchida = senhaInput.value.trim() !== "";

        if (emailPreenchido && senhaPreenchida) {
            botao.disabled = false;
            botao.style.opacity = "1";
            botao.style.cursor = "pointer";
        } else {
            botao.disabled = true;
            botao.style.opacity = "0.5";
            botao.style.cursor = "not-allowed";
        }
    }

    // Adicionando eventos para chamar a função quando o usuário digitar
    emailInput.addEventListener("input", verificarCampos);
    senhaInput.addEventListener("input", verificarCampos);

    // Chamando a função ao carregar a página para garantir que o botão inicie desativado
    verificarCampos();
});
