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
        });
    });
});
