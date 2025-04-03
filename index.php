<?php
session_start();
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;

// Verificar se o nome do usuário está na sessão
$nomeUsuario = "";
if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    // Pega apenas o primeiro nome
    $nomes = explode(" ", trim($_SESSION['usuarioNome']));
    $primeiroNome = $nomes[0] ?? "";  // Apenas o primeiro nome
    $nomeUsuario = $primeiroNome;      // Atribui somente o primeiro nome
}

// Mapeamento das capitais
$capitais = [
    "AC" => "Rio Branco",
    "AL" => "Maceió",
    "AM" => "Manaus",
    "AP" => "Macapá",
    "BA" => "Salvador",
    "CE" => "Fortaleza",
    "DF" => "Brasília",
    "ES" => "Vitória",
    "GO" => "Goiânia",
    "MA" => "São Luís",
    "MG" => "Belo Horizonte",
    "MS" => "Campo Grande",
    "MT" => "Cuiabá",
    "PA" => "Belém",
    "PB" => "João Pessoa",
    "PE" => "Recife",
    "PI" => "Teresina",
    "PR" => "Curitiba",
    "RJ" => "Rio de Janeiro",
    "RN" => "Natal",
    "RO" => "Porto Velho",
    "RR" => "Boa Vista",
    "RS" => "Porto Alegre",
    "SC" => "Florianópolis",
    "SE" => "Aracaju",
    "SP" => "São Paulo",
    "TO" => "Palmas",
];

// Definir a capital com base no estado do usuário
$estado = isset($_SESSION['usuarioEstado']) ? $_SESSION['usuarioEstado'] : "";
$capital = isset($capitais[$estado]) ? $capitais[$estado] : "Cidade - Estado";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW Concessionária</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/liquid-button.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>

    <!-- Tela inicial com a imagem de fundo -->
    <div class="hero-section">
        <header>
            <nav class="navbar">
                <div class="logo">
                    <a href="index.php">
                        <img src="img/logoofcbmw.png" alt="Logo BMW">
                    </a>
                    <a href="index.php" id="textlogo">BMW</a>
                </div>

                <div class="location">
                    <img src="img/pin-de-localizacao.png" alt="Ícone de localização">
                    <div class="location-text">
                        <span>Pesquisando ofertas em</span>
                        <u><strong id="user-location"><?php echo htmlspecialchars($capital); ?> e Região</strong></u>
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
                            <a href="login.html">
                                <img src="img/usercomcontorno.png" alt="Login">
                            </a>
                            <a href="login.html"><span>Entrar</span></a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </header>
        <div class="hero-content">
            <!-- pção 1 (Exclusividade e sofisticação)
            h1: O auge da engenharia alemã, feito para você.
            p: Experimente o equilíbrio perfeito entre luxo, potência e inovação. Seu BMW espera por você.

            Opção 2 (Potência e sofisticação)
            h1: Dirija a excelência. Viva a BMW.
            p: Uma experiência de condução sem igual, onde desempenho encontra sofisticação. Descubra o seu próximo BMW.

            Opção 3 (Foco em conforto e tecnologia)
            h1: A perfeição sobre rodas.
            p: Cada detalhe pensado para oferecer luxo, conforto e inovação. Explore a linha BMW.

-->
            <h1>O auge da engenharia alemã, feito para você.</h1>
            <p>Experimente o equilíbrio perfeito entre luxo, potência e inovação. Seu BMW espera por você.</p>
            <div class="botoes-container">
            <div class="containerBotoes">
                <button class="botaoSuperior">COMEÇAR AGORA</button>
                <button class="botaoInferior">COMEÇAR AGORA</button>
            </div>
            <div class="buttonSaibaMais">
                <button class="saibaMais">Saiba mais</button>
            </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo da página que aparece ao rolar -->
    <div class="main-content">
        <h1>Bem-vindo ao nosso site!</h1>
        <p>Este é um exemplo de conteúdo. Quando você adicionar mais conteúdo, a página vai rolar para baixo.</p>
        <p>Continue adicionando conteúdo abaixo para testar o comportamento da imagem.</p>
        <p>Mais conteúdo...</p>
    </div>
    
    <script src="js/trocar-bg-index.js"></script>
    <script src="js/liquid-button.js"></script>

</body>
</html>
