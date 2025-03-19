// Função para mostrar/ocultar senha
document.getElementById("eyeIcon1").addEventListener("click", function () {
    const senhaInput = document.getElementById("nova_senha");
    if (senhaInput.type === "password") {
        senhaInput.type = "text";
        this.src = "img/olhoaberto.png"; // Ícone de olho aberto
    } else {
        senhaInput.type = "password";
        this.src = "img/olhofechado.png"; // Ícone de olho fechado
    }
});