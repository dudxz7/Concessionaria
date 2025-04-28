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

        <!-- Cards de carros -->
        <div class="cards-container">
            <?php include 'php/card-veiculos.php'; ?>
            <!-- Aqui é onde os cards serão gerados dinamicamente -->


            <div class="view-all">
                <button>Ver todos os modelos</button>
            </div>

            <!-- Promoções -->
            <div class="chamado-promocoes">
                <img src="img/promocoes-azul.png" alt="Ícone de Promoções">
                <h2>Confira Ofertas Especiais da BMW em </h2>
                <span><?php echo htmlspecialchars($capital); ?></span>
            </div>

            <!-- Card com promoção -->
            <div class="card">
                <div class="favorite-icon">
                    <img src="img/coracao-nao-salvo.png" alt="Favoritar" class="heart-icon" draggable="false">
                </div>
                <img src="img/modelos/carro4.webp" alt="BMW 330E">
                <h2>BMW 330E</h2>
                <p>2.0 16V TURBO HÍBRIDO M SPORT</p>
                <p><img src="img/calendario.png" alt="Calendário"> 2023/2024 <img src="img/painel-de-controle.png"
                        alt="Painel de Controle"> 0 Km</p>
                <div class="rating">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela-neutra.png" alt="estrela">
                    <span class="nota">(1.010)</span>
                </div>
                <div class="preco-promocao">
                    <h2 class="preco-antigo">R$ 454.950</h2>
                    <div class="preco-novo">
                        <h2>R$ 414.950</h2>
                        <span class="desconto">-10%</span>
                    </div>
                </div>
                <button class="btn-send">Estou interessado</button>
            </div>

        </div>

        <div class="view-all">
            <button>Ver todas as ofertas</button>
        </div>

    </div>


    <section class="porque-comprar">
        <h2>Porque comprar conosco?</h2>
        <div class="beneficios-container">
            <div class="beneficio">
                <img src="img/verificacao.png" alt="Experiência">
                <h3>Experiência e conhecimento</h3>
                <p>Mais de uma década entendendo o que move você. Nossos especialistas dominam cada detalhe do universo
                    automotivo. Com a gente, você não só compra um carro, você faz a escolha certa, com quem entende do
                    assunto.</p>
            </div>
            <div class="beneficio">
                <img src="img/transparencia.png" alt="Transparência">
                <h3>Transparência e honestidade</h3>
                <p>A confiança é a base de qualquer relação de sucesso. Trabalhamos com total transparência, garantindo
                    que você saiba exatamente o que está adquirindo. Nosso compromisso é ser claro e honesto em todas as
                    etapas da sua compra, sem surpresas</p>
            </div>
            <div class="beneficio">
                <img src="img/distintivo.png" alt="Qualidade">
                <h3>Qualidade garantida</h3>
                <p>Oferecemos apenas veículos de altíssima qualidade, com garantia de desempenho e durabilidade. Cada
                    carro passa por rigorosos testes antes de ser oferecido. Com a gente, você tem a certeza de que está
                    adquirindo o melhor do mercado.</p>
            </div>
            <div class="beneficio">
                <img src="img/agente-de-atendimento-ao-cliente.png" alt="Atendimento">
                <h3>Atendimento excepcional</h3>
                <p>Nossa missão é entender suas necessidades para oferecer a melhor solução. Cada cliente recebe a
                    atenção exclusiva para encontrar o carro ideal. A experiência de compra é única, pois personalizamos
                    o atendimento para você.</p>
            </div>
        </div>
    </section>

    <section class="feedback-section">
        <div class="avaliacao">
            <p>
                Avaliação 5,0
                <img src="img/estrela.png" alt="Estrela">
                <img src="img/estrela.png" alt="Estrela">
                <img src="img/estrela.png" alt="Estrela">
                <img src="img/estrela.png" alt="Estrela">
                <img src="img/estrela.png" alt="Estrela">
            </p>
        </div>

        <h2 class="chamado-feedback">Veja o que estão <span class="destaque">falando sobre nós!</span></h2>
        <p class="mensagem-sub">Mensagens de feedback espontâneas recebidas em nosso canal de suporte</p>


        <div class="feedback-cards-container">
            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
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
                        <img src="img/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>

            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
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
                        <img src="img/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>
            <!-- Feedback de mulher (comprou um carro) -->
            <div class="feedback-card">
                <div class="img-canto-superior">
                    <img src="img/pontos.png" alt="Imagem no Canto Superior Direito">
                </div>
                <div class="perfil">
                    <img src="img/foto-default.svg" alt="Foto de perfil" class="foto-usuario">
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
                        <img src="img/gostar.png" alt="Curtir">
                        <span>Gostei</span>
                    </div>
                    <div class="acao">
                        <img src="img/compartilhar.png" alt="Compartilhar">
                        <span>Compartilhar</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section">
        <h2 class="faq-title">Perguntas e respostas (FAQ)</h2>

        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <span>1. Quais formas de pagamento vocês aceitam?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Abrir">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Aceitamos pagamento via transferência bancária, boleto e financiamento. Consulte nossas
                        condições.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <span>2. Posso agendar um test drive?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Abrir">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Sim! Você pode agendar seu test drive entrando em contato pelo nosso site ou telefone.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <span>3. Os veículos têm garantia?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Fechar">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Sim! Todos os nossos veículos, novos ou seminovos, passam por uma rigorosa inspeção e são
                        entregues com garantia. Os prazos e coberturas variam conforme o modelo.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <span>4. Posso financiar um veículo mesmo com nome sujo?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Abrir">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Depende da análise de crédito feita pelas instituições financeiras. Entre em contatomais
                        informações.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <span>5. Vocês aceitam meu carro usado como parte do pagamento?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Abrir">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Sim! Fazemos avaliação do seu usado e ele pode ser usado como entrada para um novo veículo.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <span>7. Vocês vendem carros seminovos ou usados?</span>
                    <button class="faq-toggle">
                        <img src="img/mais.png" alt="Abrir">
                    </button>
                </div>
                <div class="faq-answer">
                    <p>Sim! Trabalhamos com veículos seminovos de excelente procedência e revisados.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="suporte-section">
        <h2 class="suporte-title">Ainda ficou com dúvidas ?</h2>
        <p class="suporte-subtitle">
            Nosso time de <strong>SUPORTE</strong> está pronto para te atender com agilidade<br>
            através do Whatsapp, é só tocar no botão abaixo.
        </p>

        <a href="#" target="_blank" class="suporte-button">
            <img src="img/whatsapp-preto.png" alt="WhatsApp">
            FALAR COM O SUPORTE
        </a>
    </section>

    <footer class="footer">
        <!-- Faixa colorida com logo no meio -->
        <div class="footer-strip">
            <div class="footer-logo-top">
                <img src="img/logoofcbmw.png" alt="BMW Logo" />
            </div>
        </div>

        <div class="footer-container">

            <div class="footer-top">

                <div class="footer-logo">
                    <img src="img/BMW_M_100px_white.png" alt="BMW Logo">
                    <p>BMW Motors: Inovação, desempenho e paixão por dirigir.</p>
                </div>

                <div class="footer-links">
                    <h4>Links rápidos</h4>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#main">Veículos</a></li>
                        <li><a href="#">Serviços</a></li>
                        <li><a href="#">Sobre nós</a></li>
                        <li><a href="#">Contato</a></li>
                    </ul>
                </div>

                <div class="footer-contact">
                    <h4>Contato</h4>
                    <ul>
                        <li>
                            <img src="img/email-branco.png" alt="Email ícone">
                            suporte@bmwmotors.com
                        </li>
                        <li>
                            <img src="img/tel-branco.png" alt="Telefone ícone">
                            (11) 1234-5678
                        </li>
                        <li>
                            <img src="img/pin-de-localizacao-branco.png" alt="Endereço ícone">
                            Av. BMW, 123 - São Paulo
                        </li>
                        <li>
                            <img src="img/relogio-branco.png" alt="Relógio ícone">
                            Seg à Sex 09:00 às 19:00
                        </li>
                    </ul>
                </div>

                <div class="footer-social">
                    <h4>Redes sociais</h4>
                    <div class="social-icons">
                        <a href="#" class="instagram"><img src="img/insta-branco.png" alt="Instagram"></a>
                        <a href="#" class="whatsapp"><img src="img/whatsapp-branco.png" alt="WhatsApp"></a>
                        <a href="#" class="facebook"><img src="img/face-branco.png" alt="Facebook"></a>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                <p>
                    © 2025 BMW Motors. Todos os direitos reservados.
                    <a href="#">Termos de Uso</a>
                    | <a href="#">Política de Privacidade</a>
                </p>
            </div>

        </div>
    </footer>
    <script src="js/index.js" type="module"></script>
</body>
</html>