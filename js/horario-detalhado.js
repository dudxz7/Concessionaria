document.addEventListener("DOMContentLoaded", function () {
    const horarios = document.querySelectorAll(".linha-info.horario");

    horarios.forEach(item => {
        item.addEventListener("click", function () {
            // Alterna a visibilidade dos detalhes
            const detalhes = this.nextElementSibling;
            if (detalhes && detalhes.classList.contains("horarios-detalhados")) {
                detalhes.classList.toggle("ativo");
            }

            // Troca o ícone (seta para cima/baixo)
            const icone = this.querySelector(".icone-direita");
            if (icone) {
                // Troca o ícone para cima ou para baixo
                if (icone.src.includes("seta-para-baixo-preta.png")) {
                    icone.src = "../img/setra-pra-cima-.png"; // Ícone para cima
                } else {
                    icone.src = "../img/seta-para-baixo-preta.png"; // Ícone para baixo
                }
            }
        });
    });
});
