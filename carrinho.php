<?php
session_start();
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;
$primeiroNome = '';

if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomeCompleto = trim($_SESSION['usuarioNome']);
    $partesNome = explode(' ', $nomeCompleto);
    $primeiroNome = $partesNome[0];
}
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
                    <a href="perfil.html">
                        <img src="img/usercomcontorno.png" alt="Perfil">
                    </a>
                    <a href="perfil.html"><span><?php echo htmlspecialchars($primeiroNome); ?></span></a>
                <?php else: ?>
                    <a href="login.php">
                        <img src="img/usercomcontorno.png" alt="Login">
                    </a>
                    <a href="login.php"><span>Entrar</span></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<h1>Bem-vindo à BMW</h1>
<p>Aqui vai o conteúdo da sua página principal.</p>

</body>
</html>
