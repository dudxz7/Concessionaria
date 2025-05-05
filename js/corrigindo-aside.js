window.addEventListener("scroll", function () {
    const sideCard = document.querySelector(".side-card");
  const footer = document.querySelector("footer"); // Selecione o rodapé

    const sideCardRect = sideCard.getBoundingClientRect();
    const footerRect = footer.getBoundingClientRect();

  // Verifica se o aside está perto do rodapé
    if (footerRect.top <= window.innerHeight && footerRect.top > 0) {
    // Ajusta a posição do aside para que ele "pare" de ser fixo ao atingir o rodapé
    sideCard.style.position = "absolute";
    sideCard.style.top =
        window.innerHeight - footerRect.top - sideCardRect.height - 20 + "px";
    } else {
    // Caso contrário, o aside continua fixo
    sideCard.style.position = "fixed";
    sideCard.style.top = "90px"; // Distância da navbar (ajuste conforme necessário)
    }
});
