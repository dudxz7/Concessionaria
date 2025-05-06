document.addEventListener("DOMContentLoaded", function () {
    // Parte do cronômetro
    const countdownElement = document.getElementById("countdown");
    const dataLimiteStr = countdownElement?.getAttribute("data-fim");

    if (!dataLimiteStr || !countdownElement) return;

    const countDownDate = new Date(dataLimiteStr).getTime();

    const interval = setInterval(function () {
        const now = new Date().getTime();
        const distance = countDownDate - now;

        if (distance <= 0) {
            clearInterval(interval);
            countdownElement.innerHTML = "Promoção encerrada!";
            return;
        }

        const years = Math.floor(distance / (1000 * 60 * 60 * 24 * 365));
        const months = Math.floor((distance % (1000 * 60 * 60 * 24 * 365)) / (1000 * 60 * 60 * 24 * 30));
        const days = Math.floor((distance % (1000 * 60 * 60 * 24 * 30)) / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.innerHTML =
            `${years > 0 ? years + 'a ' : ''}` +
            `${months > 0 ? months + 'm ' : ''}` +
            `${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);

    // Parte do modal
    const gearIcon = document.getElementById("gearIcon");
    const modalPromo = document.getElementById("modalPromo");
    const closeModal = document.querySelector(".close");

    // Abrir o modal quando o ícone da engrenagem for clicado
    if (gearIcon) {
        gearIcon.addEventListener("click", function () {
            modalPromo.style.display = "flex"; // Mostra o modal
        });
    }

    // Fechar o modal quando clicar no X
    if (closeModal) {
        closeModal.addEventListener("click", function () {
            modalPromo.style.display = "none"; // Esconde o modal
        });
    }

    // Fechar o modal se o usuário clicar fora do conteúdo
    window.addEventListener("click", function (event) {
        if (event.target === modalPromo) {
            modalPromo.style.display = "none"; // Esconde o modal
        }
    });
});
