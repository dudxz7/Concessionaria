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

                <div class="location">
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
                    <a href="login.html">
                        <img src="img/usercomcontorno.png" alt="Login">
                    </a>
                        <a href="login.html"><span>Entrar</span></a>
                    </div>
                </div>
            </nav>
        </header>
    `;

    // Carrega o conteúdo da navbar
    document.getElementById("navbar").innerHTML = navbarHTML;
});
