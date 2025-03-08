<?php
session_start();

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW Concessionária</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>

    <!-- Navbar -->
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
                    <?php if ($usuarioLogado): ?>
                        <!-- Se o usuário estiver logado, mostra o link para o perfil -->
                        <a href="perfil.html">
                            <img src="img/usercomcontorno.png" alt="Perfil">
                        </a>
                        <a href="perfil.html"><span>Perfil</span></a>
                    <?php else: ?>
                        <!-- Se não estiver logado, mostra o link para login -->
                        <a href="login.html">
                            <img src="img/usercomcontorno.png" alt="Login">
                        </a>
                        <a href="login.html"><span>Entrar</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Restante do conteúdo -->
    <div class="carousel-container">
        <div class="carousel">
            <img src="img/soumteste.jpg" alt="Banner 1" class="carousel-item">
            <img src="img/gatinopc.webp" alt="Banner 2" class="carousel-item">
            <img src="img/gatopequeninin.png" alt="Banner 3" class="carousel-item">
        </div>
        <button class="prev" onclick="moveSlide(-1)">
            <img src="img/seta-esquerda-azul.png" alt="Seta Esquerda">
        </button>
        <button class="next" onclick="moveSlide(1)">
            <img src="img/seta-direita.png" alt="Seta Direita">
        </button>

        <!-- Pontos de navegação -->
        <div class="dots-container">
            <span class="dot" onclick="currentSlide(0)"></span>
            <span class="dot" onclick="currentSlide(1)"></span>
            <span class="dot" onclick="currentSlide(2)"></span>
        </div>
    </div>
    <script src="js/main.js" type="module"></script>

</body>
</html>
