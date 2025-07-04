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
                    <button class="botaoSuperior" type="button" id="btn-scroll-main">COMEÇAR AGORA</button>
                    <button class="botaoInferior" type="button" id="btn-scroll-main2">COMEÇAR AGORA</button>
                </div>
                <div class="buttonSaibaMais">
                    <button class="saibaMais" type="button" id="btn-scroll-main3">Saiba mais</button>
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
                <span class="filtro" id="abrir-modal-filtros"><strong>Filtros <img src="img/seta-para-baixo-preta.png" alt="" class="img-filtros"></strong></span>
                <a class="limpar-filtros" id="limpar-filtros">Limpar Filtros</a>
            </div>
        </div>

        <!-- Modal de Filtros -->
        <div id="modal-filtros" class="modal-filtros" style="display:none;">
            <div class="modal-filtros-content">
                <button class="fechar-modal-filtros" title="Fechar" aria-label="Fechar">
                    <img src="img/x-fechar.png" alt="Fechar" style="width:22px;height:22px;" />
                </button>
                <h3 class="titulo-modal-filtros">Filtrar veículos</h3>
                <form id="form-filtros" method="get" action="index.php" onsubmit="aplicarFiltros(event)">
                    <div class="campo-modal-filtros">
                        <label for="filtro-ano-min">Ano mínimo</label>
                        <input type="number" id="filtro-ano-min" name="ano_min" min="1900" max="2025" placeholder="Ex: 2018">
                    </div>
                    <div class="campo-modal-filtros">
                        <label for="filtro-ano-max">Ano máximo</label>
                        <input type="number" id="filtro-ano-max" name="ano_max" min="1900" max="2025" placeholder="Ex: 2024">
                    </div>
                    <div class="campo-modal-filtros">
                        <label for="filtro-preco-min">Preço mínimo</label>
                        <input type="number" id="filtro-preco-min" name="preco_min" min="0" step="1000" placeholder="Ex: 100000">
                    </div>
                    <div class="campo-modal-filtros">
                        <label for="filtro-preco-max">Preço máximo</label>
                        <input type="number" id="filtro-preco-max" name="preco_max" min="0" step="1000" placeholder="Ex: 300000">
                    </div>
                    <div class="campo-modal-filtros">
                        <label for="filtro-cor">Cor</label>
                        <input type="text" id="filtro-cor" name="cor" placeholder="Ex: Preto, Branco, Azul...">
                    </div>
                    <div class="campo-modal-filtros">
                        <label for="filtro-estoque">Estoque</label>
                        <select id="filtro-estoque" name="estoque" class="select-filtro-estoque">
                            <option value="">Todos</option>
                            <option value="1">Tem estoque</option>
                            <option value="0">Sem estoque</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-aplicar-filtros">Aplicar Filtros</button>
                </form>
            </div>
        </div>

        <div class="carrossel-container">
            <div class="cards-container">
                <?php include 'php/card-veiculos.php'; ?>
            </div>
        </div>

        <div class="view-all">
            <a href="todos-modelos.php"><button>Ver todos os modelos</button></a>
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
            <a href="todas-ofertas.php"><button>Ver todas as ofertas</button></a>
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
    
    <script src="js/navbar-favoritos.js" defer></script>
    <script src="js/favoritar-card.js" defer></script>
    <script src="js/index.js" type="module"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    function scrollToMain() {
        var main = document.getElementById('main');
        if (main) {
            main.scrollIntoView({ behavior: 'smooth' });
        }
    }
    var btn1 = document.getElementById('btn-scroll-main');
    var btn2 = document.getElementById('btn-scroll-main2');
    var btn3 = document.getElementById('btn-scroll-main3');
    if (btn1) btn1.addEventListener('click', scrollToMain);
    if (btn2) btn2.addEventListener('click', scrollToMain);
    if (btn3) btn3.addEventListener('click', function() {
        var main2 = document.getElementById('buywithus');
        if (main2) {
            main2.scrollIntoView({ behavior: 'smooth' });
        }
    });
    // Submete o filtro ao mudar o select de estoque
    var selectEstoque = document.getElementById('filtro-estoque');
    var formFiltros = document.getElementById('form-filtros');
    if (selectEstoque && formFiltros) {
        selectEstoque.addEventListener('change', function() {
            formFiltros.dispatchEvent(new Event('submit', {cancelable:true}));
        });
    }
});

function aplicarFiltros(event) {
    event.preventDefault();
    const form = event.target;
    const params = new URLSearchParams(new FormData(form)).toString();
    // Atualiza a URL sem reload e recarrega os cards via AJAX
    history.replaceState(null, '', '?' + params);
    // Recarrega os cards de veículos e promoções via AJAX
    fetch('php/card-veiculos.php?' + params)
        .then(r => r.text())
        .then(html => {
            document.querySelector('.cards-container').innerHTML = html;
            // Após atualizar os cards, reativa os scripts de favoritar
            if (window.initFavoritarCard) window.initFavoritarCard();
        });
    fetch('php/card-promocoes.php?' + params)
        .then(r => r.text())
        .then(html => {
            document.querySelectorAll('.cards-container')[1].innerHTML = html;
            // Após atualizar os cards, reativa os scripts de favoritar
            if (window.initFavoritarCard) window.initFavoritarCard();
        });
    return false;
}
</script>
<script>
// Função para reativar o JS de favoritar após AJAX
window.initFavoritarCard = function() {
    if (typeof FavoritarCard !== 'undefined') {
        FavoritarCard.init && FavoritarCard.init();
    }
    // Ou reatacha eventos manualmente se for jQuery ou vanilla
    if (typeof attachFavoritarEvents === 'function') {
        attachFavoritarEvents();
    }
    // Ou reexecuta o script favoritar-card.js se for vanilla
    if (typeof favoritarCardInit === 'function') {
        favoritarCardInit();
    }
};
// Executa ao carregar a página
window.initFavoritarCard();
</script>
</body>
</html>