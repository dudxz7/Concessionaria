document.addEventListener("DOMContentLoaded", function() {
    const navbarHTML = `
        <header>
            <nav class="navbar">
                <div class="logo">
                    <a href="index.html">
                        <img src="img/logoofcbmw.png" alt="Logo BMW">
                    </a>
                    <a href="index.html" id="textlogo">BMW</a>
                </div>

                <div class="divider"></div>

                <div class="location" id="open-location-modal">
                    <img src="img/pin-de-localizacao.png" alt="Ícone de localização">
                    <div class="location-text">
                        <span>Pesquisando ofertas em</span>  
                        <u><strong id="user-location">XXXX e Região</strong></u>
                    </div>
                </div>

                <div class="nav-icons">
                    <a href="carrinho.html">
                        <img src="img/heart.png" alt="Favoritos" class="heart-icon">
                    </a>
                    <div class="login">
                        <img src="img/usercomcontorno.png" alt="Login">
                        <span>Entrar</span>
                    </div>
                </div>
            </nav>
        </header>
    `;

    // Carrega o conteúdo da navbar
    document.getElementById("navbar").innerHTML = navbarHTML;

    // Lógica para o modal (exibido ao clicar na localização)
    const locationModal = document.getElementById("location-modal");
    const overlay = document.getElementById("overlay");
    const openLocationModal = document.getElementById("open-location-modal");
    const closeModal = document.getElementById("close-modal");

    // Abre o modal
    openLocationModal.addEventListener("click", function() {
        locationModal.style.display = "block";
        overlay.style.display = "block";
    });

    // Fecha o modal
    closeModal.addEventListener("click", function() {
        locationModal.style.display = "none";
        overlay.style.display = "none";
    });

    // Fecha o modal quando clicar no overlay
    overlay.addEventListener("click", function() {
        locationModal.style.display = "none";
        overlay.style.display = "none";
    });
});
