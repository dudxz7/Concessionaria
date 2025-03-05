// Referências aos elementos
const eyeIcon = document.getElementById("eyeIcon");
const senhaInput = document.getElementById("senha");
const confirmaSenhaInput = document.getElementById("confirmaSenha");
const eyeIcon2 = document.getElementById("eyeIcon2");
const errorMessage = document.getElementById("error-message"); // Elemento de mensagem de erro

// Função para alternar visibilidade da senha
function togglePasswordVisibility(inputElement, eyeElement) {
    if (inputElement.type === "password") {
        inputElement.type = "text";  // Torna a senha visível
        eyeElement.src = "img/olhoaberto.png";  // Ícone de olho aberto
    } else {
        inputElement.type = "password";  // Torna a senha oculta
        eyeElement.src = "img/olhofechado.png";  // Ícone de olho fechado
    }
}

// Evento para o primeiro ícone de olho (senha)
eyeIcon.addEventListener("click", function () {
    togglePasswordVisibility(senhaInput, eyeIcon);
    togglePasswordVisibility(confirmaSenhaInput, eyeIcon2);
});

// Evento para o segundo ícone de olho (confirmar senha)
eyeIcon2.addEventListener("click", function () {
    togglePasswordVisibility(confirmaSenhaInput, eyeIcon2);
    togglePasswordVisibility(senhaInput, eyeIcon);
});

// Função para verificar se as senhas coincidem em tempo real
function verificarSenhas() {
    if (senhaInput.value !== confirmaSenhaInput.value) {
        errorMessage.textContent = "As senhas não coincidem."; // Exibe a mensagem de erro
        errorMessage.style.display = "block"; // Torna a mensagem visível
    } else {
        errorMessage.textContent = ""; // Limpa a mensagem de erro
        errorMessage.style.display = "none"; // Esconde a mensagem de erro
    }
}

// Verifica as senhas enquanto o usuário digita
senhaInput.addEventListener("input", verificarSenhas);
confirmaSenhaInput.addEventListener("input", verificarSenhas);

// Validação final ao enviar o formulário
document.querySelector("form").addEventListener("submit", function (e) {
    // Se as senhas não coincidirem, não envia o formulário
    if (senhaInput.value !== confirmaSenhaInput.value) {
        e.preventDefault(); // Impede o envio do formulário
        errorMessage.textContent = "As senhas não coincidem."; // Exibe a mensagem de erro
        errorMessage.style.display = "block"; // Exibe a mensagem de erro
    }
});
