document.addEventListener("DOMContentLoaded", () => {
    let botoesSuperior = document.querySelectorAll(".botaoSuperior");

    botoesSuperior.forEach((botaoSuperior) => {
        botaoSuperior.addEventListener("mouseenter", () => {
            botaoSuperior.style.animation = "mascara .7s steps(22) forwards";
        });

        botaoSuperior.addEventListener("mouseleave", () => {
            botaoSuperior.style.animation = "mascaraInverso .7s steps(22) forwards";
        });

        botaoSuperior.addEventListener("click", (event) => {
            event.preventDefault(); // Previne o comportamento padrão do botão

            const isMobile = window.matchMedia("(max-width: 767px)").matches;

            if (isMobile) {
                setTimeout(() => {
                    window.open("SEU LINK AQUI", "_blank");
                }, 700);
            } else {
                window.open("SEU LINK AQUI", "_blank");
            }
        });
    });
});
