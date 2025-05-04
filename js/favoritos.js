document.addEventListener("DOMContentLoaded", function () {
    const coracoes = document.querySelectorAll(".heart-icon");

    coracoes.forEach(coracao => {
        coracao.addEventListener("click", function (e) {
            const estaSalvo = this.getAttribute("data-salvo") === "true";

            // Troca a imagem
            if (estaSalvo) {
                this.src = "img/coracoes/coracao-nao-salvo.png";
                this.setAttribute("data-salvo", "false");
            } else {
                this.src = "img/coracoes/coracao-salvo.png";
                this.setAttribute("data-salvo", "true");

                // Cria partículas rosa
                criarParticulas(e.pageX, e.pageY);
            }
        });
    });

    // Partículas simples
    function criarParticulas(x, y) {
        for (let i = 0; i < 10; i++) {
            const part = document.createElement("div");
            part.className = "particle";
            part.style.left = x + "px";
            part.style.top = y + "px";

            // Cria movimentação aleatória
            const offsetX = (Math.random() - 0.5) * 100 + "px";
            const offsetY = (Math.random() - 0.5) * 100 + "px";

            part.style.setProperty('--x', offsetX);
            part.style.setProperty('--y', offsetY);

            document.body.appendChild(part);

            // Remove partícula depois da animação
            setTimeout(() => {
                part.remove();
            }, 600);
        }
    }


    // // Partículas extravagantes
    // function criarParticulas(x, y) {
    //     for (let i = 0; i < 15; i++) {
    //         const part = document.createElement("div");
    //         part.className = "particle";

    //         // Tamanho aleatório
    //         const size = Math.random() * 6 + 4;
    //         part.style.width = `${size}px`;
    //         part.style.height = `${size}px`;

    //         // Posição inicial
    //         part.style.left = `${x}px`;
    //         part.style.top = `${y}px`;

    //         // Movimento aleatório
    //         const offsetX = (Math.random() - 0.5) * 120 + "px";
    //         const offsetY = (Math.random() - 0.5) * 120 + "px";

    //         part.style.setProperty('--x', offsetX);
    //         part.style.setProperty('--y', offsetY);

    //         document.body.appendChild(part);

    //         setTimeout(() => {
    //             part.remove();
    //         }, 800);
    //     }
    // }
});
