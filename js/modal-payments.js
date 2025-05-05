document.addEventListener("DOMContentLoaded", () => {
    const abrirModal = document.getElementById("abrirModal");
    const modal = document.getElementById("modalPagamento");
    const fecharModal = document.getElementById("fecharModal");

    abrirModal.addEventListener("click", () => {
        modal.style.display = "flex";
    });

    fecharModal.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // Clicar fora do conteúdo fecha o modal
    window.addEventListener("click", (e) => {
        if (e.target === modal) {
        modal.style.display = "none";
        }
    });
});