document.addEventListener("DOMContentLoaded", function () {
  function atualizarFavoritosNavbar() {
    fetch("php/contar-favoritos.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const counter = document.querySelector(".heart-counter");
          if (counter) {
            counter.textContent = data.count;
            if (data.count > 0) {
              counter.classList.remove("oculto");
            } else {
              counter.classList.add("oculto");
            }
          }
        }
      });
  }

  // Atualiza ao favoritar (escuta evento customizado ou clique)
  document.body.addEventListener(
    "favoritoAtualizado",
    atualizarFavoritosNavbar
  );

  // Também atualiza ao clicar no botão de favoritar
  document.body.addEventListener("click", function (e) {
    if (e.target.closest(".btn-favoritar")) {
      setTimeout(atualizarFavoritosNavbar, 50); // delay mínimo para garantir AJAX
    }
  });
});
