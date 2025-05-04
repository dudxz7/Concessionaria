const faqItems = document.querySelectorAll(".faq-item");

faqItems.forEach((item) => {
    const toggleBtn = item.querySelector(".faq-toggle");
    const img = toggleBtn.querySelector("img");
  const question = item.querySelector(".faq-question"); // Aqui estamos pegando a questão (span)

    question.addEventListener("click", () => {
    const isOpen = item.classList.contains("open");

    // Fecha todos
    faqItems.forEach((i) => {
        i.classList.remove("open");
        i.querySelector(".faq-toggle img").src = "img/faq/mais.png";
        // Remove a classe 'highlighted' de todos os spans
        i.querySelector(".faq-question span").classList.remove("highlighted");
    });

    if (!isOpen) {
        item.classList.add("open");
        img.src = "img/faq/x-fechar.png";
        // Adiciona a classe 'highlighted' ao span quando o item for aberto
        question.querySelector("span").classList.add("highlighted");
    }
    });
});
