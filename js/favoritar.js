document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".favorite-icon button[data-modelo-id]")
    .forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        const modeloId = btn.getAttribute("data-modelo-id");
        fetch("php/favoritar.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "modelo_id=" + encodeURIComponent(modeloId),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              const img = btn.querySelector("img.heart-icon");
              if (data.favorito) {
                img.src = "img/coracoes/coracao-salvo.png";
              } else {
                img.src = "img/coracoes/coracao-nao-salvo.png";
              }
            } else if (data.error) {
              alert(data.error);
            }
          })
          .catch(() => alert("Erro ao favoritar."));
      });
    });
});
