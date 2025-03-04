document.addEventListener("DOMContentLoaded", function() {
    const navbarHTML = `
        <header>
            <nav class="navbar">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.html">
                        <img src="img/logoofcbmw.png" alt="Logo BMW">
                    </a>
                    <a href="index.html" id="textlogo">BMW</a>
                </div>

                <!-- Barra vertical -->
                <div class="divider"></div>

                <!-- Localização -->
                <div class="location">
                    <img src="img/pin-de-localizacao.png" alt="Ícone de localização">
                    <div class="location-text">
                        <span>pesquisando ofertas em</span>  
                        <u><strong>Fortaleza e Região</strong></u>
                    </div>
                </div>

                <!-- Ícones -->
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

    document.getElementById("navbar").innerHTML = navbarHTML;
});
