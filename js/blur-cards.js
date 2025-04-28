const cards = document.querySelectorAll(".card");

// Quando o mouse entra na área de um card
cards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
    // Adiciona a classe 'blur' a todos os cards
    cards.forEach((c) => {
        c.classList.add("blur");
    });

    // Adiciona a classe 'imune' ao card que está com hover
    card.classList.add("imune");
    });

  // Quando o mouse sai de um card
    card.addEventListener("mouseleave", () => {
    // Remove o blur de todos os cards
    cards.forEach((c) => {
        c.classList.remove("blur");
    });

    // Remove a classe 'imune' do card
    card.classList.remove("imune");
    });
});
