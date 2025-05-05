document.addEventListener("DOMContentLoaded", function () {
    const maxVisible = 6;
    const columns = document.querySelectorAll(".accessories .column");
    const moreLink = document.querySelector("a.more");
    const arrowIcon = document.querySelector(".arrow-icon");
    const textNode = moreLink.querySelector("strong");

    let expanded = false;

  // Ocultar extras
    function hideItems() {
    columns.forEach((column) => {
        const items = column.querySelectorAll(".accessory-item");
        items.forEach((item, index) => {
        item.classList.toggle("hidden", index >= maxVisible);
        });
    });
    }

  // Alternar ao clicar
    moreLink.addEventListener("click", function (e) {
    e.preventDefault();
    expanded = !expanded;

    columns.forEach((column) => {
        const items = column.querySelectorAll(".accessory-item");
        items.forEach((item, index) => {
        if (index >= maxVisible) {
            item.classList.toggle("hidden");
        }
        });
    });

    textNode.textContent = expanded ? "Mostrar menos" : "Mostrar mais";
    arrowIcon.classList.toggle("rotate", expanded);
    });

  // Inicial
    hideItems();
});
