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
$capital = isset($capitais[$estado]) ? $capitais[$estado] . " - " . $estado : "Cidade - Estado";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW Concessionária</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" href="img/logos/logoofcbmw.png">
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
    <div class="main-content" id="main">
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

        <div class="carrossel-container">
            <div class="cards-container">
                <?php include 'php/card-veiculos.php'; ?>
            </div>
        </div>

        <div class="view-all">
            <button>Ver todos os modelos</button>
        </div>

        <!-- Promoções -->
        <div class="chamado-promocoes">
            <img src="img/promocoes-azul.png" alt="Ícone de Promoções">
            <h2>Confira Ofertas Especiais da BMW em </h2>
            <span><?php echo htmlspecialchars($capital); ?></span>
        </div>

        <!-- Carrossel Container -->
        <div class="carrossel-container">
            <!-- Card com promoção -->
            <div class="cards-container">
                <?php include 'php/card-promocoes.php'; ?>
            </div>
        </div>

        <div class="view-all">
            <button>Ver todas as ofertas</button>
        </div>

    </div>


    <?php include "php/buywithus.php"; ?>

    <section class="feedback-section">
        <div class="avaliacao">
            <p>
                Avaliação 5,0
                <img src="img/cards/estrela.png" alt="Estrela">
                <img src="img/cards/estrela.png" alt="Estrela">
                <img src="img/cards/estrela.png" alt="Estrela">
                <img src="img/cards/estrela.png" alt="Estrela">
                <img src="img/cards/estrela.png" alt="Estrela">
            </p>
        </div>

        <h2 class="chamado-feedback">Veja o que estão <span class="destaque">falando sobre nós!</span></h2>
        <p class="mensagem-sub">Mensagens de feedback espontâneas recebidas em nosso canal de suporte</p>


        <div class="feedback-cards-container">
            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/comentarios/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/comentarios/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
                    <div>
                        <h3 class="nome">Ricardo Monteiro</h3>
                        <p class="cargo">Analista de Logística</p>
                    </div>
                </div>
                <p class="mensagem">
                    "A melhor experiência que já tive comprando um carro! Desde o primeiro atendimento até a entrega,
                    tudo foi impecável. A equipe foi super atenciosa, tirou todas as minhas dúvidas e me ajudou a
                    encontrar o carro perfeito pra minha necessidade. Dá pra ver que eles realmente entendem do que
                    estão falando. Recomendo de olhos fechados!"
                </p>
                <div class="acoes">
                    <div class="acao">
                        <img src="img/comentarios/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/comentarios/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>

            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/comentarios/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/comentarios/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
                    <div>
                        <h3 class="nome">Lucas Pereira</h3>
                        <p class="cargo">Gerente de Vendas</p>
                    </div>
                </div>
                <p class="mensagem">
                    "Tive uma experiência incrível! Desde o atendimento até a entrega do meu novo carro, tudo foi muito
                    bem organizado e com muita atenção aos detalhes. A equipe me ajudou a escolher o modelo perfeito
                    para minha família, e eu fiquei extremamente satisfeito com o resultado. Super recomendo a todos!"
                </p>
                <div class="acoes">
                    <div class="acao">
                        <img src="img/comentarios/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/comentarios/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>
            <!-- Feedback de mulher (comprou um carro) -->
            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/comentarios/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/comentarios/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
                    <div>
                        <h3 class="nome">Ana Souza</h3>
                        <p class="cargo">Coordenadora de Marketing</p>
                    </div>
                </div>
                <p class="mensagem">
                    "Acabei de comprar um BMW X1 e não poderia estar mais feliz com a minha escolha! O atendimento foi
                    impecável do começo ao fim, e a equipe foi super paciente em me explicar todas as opções. A entrega
                    foi rápida, e o carro está perfeito, com o design incrível e o conforto que eu precisava. Estou
                    apaixonada pelo desempenho e pela tecnologia do modelo. Super recomendo a experiência!"
                </p>
                <div class="acoes">
                    <div class="acao">
                        <img src="img/comentarios/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/comentarios/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "php/faq-section.php"; ?>

    <?php include "php/contato-suporte.php"; ?>

    <?php include "php/footer.php"; ?>
    
    <script src="js/index.js" type="module"></script>

</html>