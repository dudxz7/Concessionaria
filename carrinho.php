<?php
session_start();
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;

// Pegar o primeiro e segundo nome
$nomeUsuario = "";
if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomes = explode(" ", $_SESSION['usuarioNome']);
    $primeiroNome = $nomes[0];
    $segundoNome = isset($nomes[1]) ? $nomes[1] : "";
    $nomeUsuario = $primeiroNome . " " . $segundoNome;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <!-- Navbar -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">
                    <img src="img/logoofcbmw.png" alt="Logo BMW">
                </a>
                <a href="index.php" id="textlogo">BMW</a>
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
                <a href="carrinho.php">
                    <img src="img/heart.png" alt="Favoritos" class="heart-icon">
                </a>
                <div class="login">
                    <?php if ($usuarioLogado): ?>
                        <!-- Se o usuário estiver logado, mostra o nome -->
                        <a href="perfil.php">
                            <img src="img/usercomcontorno.png" alt="Perfil">
                        </a>
                        <a href="perfil.php"><span><?php echo htmlspecialchars($nomeUsuario); ?></span></a>
                    <?php else: ?>
                        <!-- Se não estiver logado, mostra o link para login -->
                        <a href="login.php">
                            <img src="img/usercomcontorno.png" alt="Login">
                        </a>
                        <a href="login.php"><span>Entrar</span></a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <h1>Seu Carrinho</h1>
    <p>Aqui vão os itens que você adicionou.</p>

</body>
</html>
