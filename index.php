<?php
session_start();
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;

// Verificar se o nome do usuário está na sessão
$nomeUsuario = "";
if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomes = explode(" ", trim($_SESSION['usuarioNome']));
    $primeiroNome = $nomes[0] ?? "";
    $nomeUsuario = $primeiroNome;
}

// Verificar se é admin
$linkPerfil = 'perfil.php';
if ($usuarioLogado && isset($_SESSION['usuarioAdmin']) && $_SESSION['usuarioAdmin'] == 1) {
    $linkPerfil = 'php/admin_dashboard.php';
}

// Mapeamento das capitais
$capitais = [
    "AC" => "Rio Branco", "AL" => "Maceió", "AM" => "Manaus", "AP" => "Macapá",
    "BA" => "Salvador", "CE" => "Fortaleza", "DF" => "Brasília", "ES" => "Vitória",
    "GO" => "Goiânia", "MA" => "São Luís", "MG" => "Belo Horizonte", "MS" => "Campo Grande",
    "MT" => "Cuiabá", "PA" => "Belém", "PB" => "João Pessoa", "PE" => "Recife",
    "PI" => "Teresina", "PR" => "Curitiba", "RJ" => "Rio de Janeiro", "RN" => "Natal",
    "RO" => "Porto Velho", "RR" => "Boa Vista", "RS" => "Porto Alegre", "SC" => "Florianópolis",
    "SE" => "Aracaju", "SP" => "São Paulo", "TO" => "Palmas"
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
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/liquid-button.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>

    <!-- Tela inicial com a imagem de fundo -->
    <div class="hero-section">
        <header>
            <div>
                <?php include 'php/navbar.php'; ?>
            </div>
        </header>

        <div class="hero-content">
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
    <div class="search-bar-wrapper">
        <h2 class="titulo-oferta">ENCONTRE UMA OFERTA</h2>

        <div class="search-container">
            <input type="text" name="search" class="input" placeholder="Buscar por modelo...">
            <button type="submit" class="btn-search">
                <img src="img/icon-search-azul.svg" alt="Buscar" class="icone-lupa">
            </button>
        </div>

        <div class="filtros-container">
            <span class="filtro"><strong>Filtros <img src="img/seta-para-baixo.png" alt=""></strong></span>
            <a class="limpar-filtros">Limpar Filtros</a>
        </div>
    </div>

    <div class="cards-container">
        <!-- Card 1 -->
        <div class="card">
            <div class="favorite-icon">
                <img src="img/coracao-nao-salvo.png" alt="Favoritar" class="heart-icon">
            </div>
            <img src="img/carro1.webp" alt="BMW 118i">
            <h2>BMW 118i</h2>
            <p>1.5 12V GASOLINA SPORT GP STEPTRONIC</p>
            <p><img src="img/calendario.png"></img> 2024/2025 <img src="img/painel-de-controle.png"></img> 0 Km</p>
            <div class="rating">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela-neutra.png" alt="estrela ">
                <span class="nota">(1.010)</span>
            </div>
            <h2>R$ 320.950</h2>
            <button class="btn-send">
                Estou interessado
            </button>
        </div>

        <!-- Card 2 -->
        <div class="card">
            <div class="favorite-icon">
                <img src="img/coracao-salvo.png" alt="Favoritar" class="heart-icon">
            </div>
            <img src="img/carro2.webp" alt="BMW 128i">
            <h2>BMW 128i</h2>
            <p>1.5 TWINTURBO GASOLINA GRAN COUPE M SPORT STEPTRONIC</p>
            <p><img src="img/calendario.png"></img> 2024/2025 <img src="img/painel-de-controle.png"></img> 0 Km</p>
            <div class="rating">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela-neutra.png" alt="estrela ">
                <img src="img/estrela-neutra.png" alt="estrela ">
                <span class="nota">(1.823)</span>
            </div>
            <h2>R$ 320.950</h2>
            <button class="btn-send">
                Estou interessado
            </button>
        </div>
        <!-- Card 3 -->
        <div class="card">
            <div class="favorite-icon">
                <img src="img/coracao-salvo.png" alt="Favoritar" class="heart-icon">
            </div>
            <img src="img/carro3.webp" alt="BMW 320i">
            <h2>BMW 320i</h2>
            <p>2.0 16V TURBO FLEX M SPORT 10TH ANNIVERSARY EDITION AUTOMÁTICO</p>
            <p><img src="img/calendario.png"></img> 2024/2025 <img src="img/painel-de-controle.png"></img> 0 Km</p>
            <div class="rating">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela.png" alt="estrela ">
                <img src="img/estrela-metade.png" alt="estrela ">
                <span class="nota">(20)</span>
            </div>
            <h2>R$ 412.950</h2>
            <button class="btn-send">
                Estou interessado
            </button>
        </div>

        <div class="view-all">
            <button>Ver todos os modelos</button>
        </div>
    </div>
</div>
    
    <script src="js/trocar-bg-index.js"></script>
    <script src="js/liquid-button.js"></script>

</body>
</html>
