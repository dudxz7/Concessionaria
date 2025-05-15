        function trocarImagem(icone, srcHover) {
            const img = icone.querySelector("img");
            const originalSrc = img.src;

            icone.addEventListener("mouseenter", () => {
                img.src = srcHover;
            });

            icone.addEventListener("mouseleave", () => {
                img.src = originalSrc;
            });
        }

        // Rodar só depois que carregar tudo
        document.addEventListener("DOMContentLoaded", () => {
            trocarImagem(document.querySelector(".instagram"), "../img/redes-sociais/insta-colorido.png");
            trocarImagem(document.querySelector(".whatsapp"), "../img/redes-sociais/whatsapp-colorido.png");
            trocarImagem(document.querySelector(".facebook"), "../img/redes-sociais/facebook-colorido.png");
        });