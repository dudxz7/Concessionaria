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
$linkPerfil = '../perfil.php';
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BMW 118i 2024: Preço, versões e mais! Bmw motors</title>
    <link rel="icon" href="../img/logos/logoofcbmw.png" />
    <link rel="stylesheet" href="../css/pagina-veiculo.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <style>
    .navbar {
        background-color: black;
        position: fixed;
        z-index: 11;
    }

    .footer {
        z-index: 1000;
    }
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">
                <img src="../img/logos/logoofcbmw.png" alt="Logo BMW">
            </a>
            <a href="../index.php" id="textlogo">BMW</a>
        </div>

        <div class="location">
            <img src="../img/navbar/pin-de-localizacao.png" alt="Ícone de localização">
            <div class="location-text">
                <span>Pesquisando ofertas em</span>
                <u><strong id="user-location"><?php echo htmlspecialchars($capital); ?> e Região</strong></u>
            </div>
        </div>

        <div class="nav-icons">
            <a href="../carrinho.php">
                <img src="../img/navbar/heart.png" alt="Favoritos" class="heart-icon-navbar">
            </a>
            <div class="login">
                <?php if ($usuarioLogado): ?>
                <!-- Se o usuário estiver logado, mostra o nome -->
                <a href="<?php echo $linkPerfil; ?>">
                    <img src="../img/navbar/usercomcontorno.png" alt="Perfil">
                </a>
                <a href="<?php echo $linkPerfil; ?>"><span><?php echo htmlspecialchars($nomeUsuario); ?></span></a>
                <?php else: ?>
                <!-- Se não estiver logado, mostra o link para login -->
                <a href="login.html">
                    <img src="img/navbar/usercomcontorno.png" alt="Login">
                </a>
                <a href="../login.html"><span>Entrar</span></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="container">

        <!-- Seção da Imagem e Carrossel -->
        <!-- Seção da Imagem e Carrossel -->
        <section class="car-section">
            <img src="../img/modelos/carro1.webp" alt="BMW 118i" class="car-image">

            <!-- Imagem âncora à esquerda -->
            <div class="anchor-left">
                <img src="../img/new-arrow-slider-left.svg" alt="Seta esquerda" class="arrow-left">
            </div>

            <!-- Imagem âncora à direita -->
            <div class="anchor-right">
                <img src="../img/new-arrow-slider-right.svg" alt="Seta direita" class="arrow-right">
            </div>

            <div class="thumbnail-row">
                <img src="../img/modelos/carro1.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/2.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/3.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/4.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/5.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/6.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/7.webp" alt="" class="thumb">
                <img src="../img/modelos/detalhar-modelos/8.webp" alt="" class="thumb">
            </div>

            <!-- Card lateral -->
                <aside class="side-card">
                    <div class="header">
                        <h2><strong>BMW</strong> <span>118i</span></h2>
                        <button class="favorite">
                            <img src="../img/coracoes/coracao-nao-salvo.png" alt="Favorito" class="favorite-icon" />
                            Favoritar
                        </button>

                    </div>

                    <p class="year-km">2023/2024 · <span>0 Km</span></p>

                    <hr class="section-divider" />

                    <div class="location-card">
                        <img src="../img/mapa/localizacao-consorcio.webp" alt="Local" />
                        <p>
                            <span>Esse carro está disponível em</span><br>
                            <strong>TODO O BRASIL!</strong>
                        </p>
                        <span class="arrow">→</span>
                    </div>

                    <hr class="section-divider" />

                    <label>Escolha a cor</label>
                    <div class="color-options">
                        <div class="color-circle" style="background: #000;"></div>
                        <div class="color-circle" style="background: #1c1cb9;"></div>
                        <div class="color-circle" style="background: #d5d5d5;"></div>
                    </div>

                    <hr class="section-divider" />

                    <select class="funcionario-select">
                        <option>Selecione um funcionário</option>
                        <!-- opções aqui -->
                    </select>

                    <hr class="section-divider" />

                    <div class="price-section">
                        <p class="old-price">R$ 320.950</p>
                        <p class="new-price">R$ 290.950 <img src="../img/em-formacao.png" alt="Engrenagem"
                                class="gear-icon"></p>
                        <a href="#" class="payment-link" id="abrirModal">Formas de pagamento</a>
                    </div>

                    <button class="buy-button">Compre agora</button>
                    <button class="visit-button">Agendar visita</button>
                </aside>

            <!-- Selo verde -->
            <div class="badge">
                <img src="../img/garantia-2.png"> Garantia de 1 ano grátis
            </div>

            <!-- Seção "Sobre este veículo" -->
            <div class="about-vehicle">
                <h3>Sobre este veículo</h3>
            </div>

            <!-- Sobre o veículo -->
            <div class="features">
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/kilometragem-sobre.svg" alt="Quilometragem">
                    <span>0 km</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/calendario-sobre.svg" alt="Ano">
                    <span>2022/2021</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/gasolina-sobre.svg" alt="Combustível">
                    <span>Elétrico/gasolina</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/automatico-sobre.svg" alt="Câmbio">
                    <span>Automático</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/motor-sobre.svg" alt="Motor">
                    <span>Motor 3.0</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/placa-sobre.svg" alt="Final Placa">
                    <span>Final placa 2</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/portas-sobre.svg" alt="Portas">
                    <span>4 portas</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/cor-de-fabrica-sobre.svg" alt="Cor">
                    <span>Branco</span>
                </div>
                <div class="feature-box">
                    <img src="../img/sobre-veiculos/suv-sobre.svg" alt="Tipo">
                    <span>SUV</span>
                </div>
            </div>

            <!-- Seção "Sobre este veículo" -->
            <div class="about-acc">
                <h3>Acessórios e outros</h3>
            </div>

            <!-- Acessórios -->
            <div class="accessories">
                <div class="column">
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/airbag-acc.svg" alt="Airbag do motorista"
                            class="accessory-icon">
                        <p>Airbag do motorista</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/alarm-acc.svg" alt="Alarme" class="accessory-icon">
                        <p>Alarme</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/ar-condicionado-acc.svg" alt="Ar-condicionado"
                            class="accessory-icon">
                        <p>Ar-condicionado</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/banco-de-motorista-acc.svg"
                            alt="Banco do motorista com ajuste de altura" class="accessory-icon">
                        <p>Banco do motorista com ajuste de altura</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/comando-de-voz.svg"
                            alt="Comando de áudio e telefone no volante" class="accessory-icon">
                        <p>Comando de áudio e telefone no volante</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/controle-speed-acc.svg"
                            alt="Controle automático de velocidade" class="accessory-icon">
                        <p>Controle automático de velocidade</p>
                    </div>
                    <!-- Novos acessórios - Coluna 1 -->
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/desembacador-traseiro-acc.svg" alt="Desembaçador traseiro"
                            class="accessory-icon">
                        <p>Desembaçador traseiro</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/direcao-eletrica.svg" alt="Direção hidráulica"
                            class="accessory-icon">
                        <p>Direção hidráulica</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/farol-de-neblina-acc.svg" alt="Farol de neblina"
                            class="accessory-icon">
                        <p>Farol de neblina</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/gps-acc.svg" alt="GPS" class="accessory-icon">
                        <p>GPS</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/limpador-traseiro.svg" alt="Limpador traseiro"
                            class="accessory-icon">
                        <p>Limpador traseiro</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/porta-copos-acc.svg" alt="Porta-copos"
                            class="accessory-icon">
                        <p>Porta-copos</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/retrovisor-eletrico.svg" alt="Retrovisores elétricos"
                            class="accessory-icon">
                        <p>Retrovisores elétricos</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/sensor-de-chuva.svg" alt="Sensor de chuva"
                            class="accessory-icon">
                        <p>Sensor de chuva</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/teto-solar-acc.svg" alt="Teto solar"
                            class="accessory-icon">
                        <p>Teto solar</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/travas-eletricas.svg" alt="Travas elétricas"
                            class="accessory-icon">
                        <p>Travas elétricas</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/direcao-eletrica.svg" alt="Volante com Regulagem de Altura"
                            class="accessory-icon">
                        <p>Volante com Regulagem de Altura</p>
                    </div>
                </div>

                <!-- Coluna 2 -->
                <div class="column">
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/airbag-acc.svg" alt="Airbag duplo" class="accessory-icon">
                        <p>Airbag duplo</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/ar-qeunte-acc.svg" alt="Ar quente" class="accessory-icon">
                        <p>Ar quente</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/ar-condicionado-acc.svg" alt="Ar-Condicionado Digital"
                            class="accessory-icon">
                        <p>Ar-Condicionado Digital</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/banco-de-couro-acc.svg" alt="Bancos de couro"
                            class="accessory-icon">
                        <p>Bancos de couro</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/computacao-de-bordo-acc.svg" alt="Computador de bordo"
                            class="accessory-icon">
                        <p>Computador de bordo</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/tracao-acc.svg" alt="Controle de tração"
                            class="accessory-icon">
                        <p>Controle de tração</p>
                    </div>
                    <!-- Novos acessórios - Coluna 2 -->
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/direcao-eletrica.svg" alt="Direção elétrica"
                            class="accessory-icon">
                        <p>Direção elétrica</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/banco-de-couro-acc.svg" alt="Encosto de cabeça traseiro"
                            class="accessory-icon">
                        <p>Encosto de cabeça traseiro</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/freio-abs-acc.svg" alt="Freio ABS" class="accessory-icon">
                        <p>Freio ABS</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/kit-musica-acc.svg" alt="Kit Multimídia"
                            class="accessory-icon">
                        <p>Kit Multimídia</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/para-choques-na-cor-do-veiculo-acc.svg"
                            alt="Pára-choques na cor do veículo" class="accessory-icon">
                        <p>Pára-choques na cor do veículo</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/retrovisor-acc.svg" alt="Retrovisor fotocrômico"
                            class="accessory-icon">
                        <p>Retrovisor fotocrômico</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/rodas-acc.svg" alt="Rodas de liga leve"
                            class="accessory-icon">
                        <p>Rodas de liga leve</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/sensor-de-estacionamento.svg"
                            alt="Sensor de estacionamento" class="accessory-icon">
                        <p>Sensor de estacionamento</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/tracao-acc.svg" alt="Tração 4x4" class="accessory-icon">
                        <p>Tração 4x4</p>
                    </div>
                    <div class="accessory-item">
                        <img src="../img/acessorios-veiculos/porta-acc.svg" alt="Vidros elétricos"
                            class="accessory-icon">
                        <p>Vidros elétricos</p>
                    </div>
                </div>
            </div>



            <!-- Botão fora da div de acessórios -->
            <a href="#" class="more"><strong>Mostrar mais</strong>
                <img src="../img/seta-para-baixo.png" class="arrow-icon">
            </a>


        </section>

        <section class="qualidade-box">
            <div class="certificado-container">
                <div class="certificado-topo">
                    <div class="texto-esquerda">
                        <h2>Certificado de <span class="verde">qualidade.</span></h2>
                        <ul>
                            <li><img src="../img/certificado/check-certify.svg" alt="check"> Garantia de procedência
                            </li>
                            <li><img src="../img/certificado/check-certify.svg" alt="check"> Garantia de procedência
                            </li>
                            <li><img src="../img/certificado/check-certify.svg" alt="check"> Garantia de procedência
                            </li>
                        </ul>
                    </div>
                    <div class="imagem-carro">
                        <img src="../img/certificado/certificado-de-qualidade.webp" alt="Carro certificado">
                    </div>
                </div>

                <div class="caixa-verificacao">
                    <div class="selo">
                        <img src="../img/certificado/360-itens-verificados.webp" alt="Selo 360 verificado">
                    </div>
                    <div class="texto-verificacao">
                        <strong>360 Itens verificados</strong>
                        <p>Este carro passou por uma inspeção de itens para garantir que você rode com tranquilidade.
                        </p>
                    </div>
                    <div class="link-verificacao">
                        <a href="#">Abrir itens verificados</a>
                    </div>
                </div>
            </div>

        </section>

        <section class="container-visita">
            <h2>Conheça esse carro em nossa loja</h2>
            <div class="conteudo-visita">
                <div class="mapa">
                    <img src="../img/mapa/mapa-consorcio.webp" alt="Mapa da loja">
                </div>
                <div class="info">

                    <!-- ENDEREÇO -->
                    <div class="linha-info">
                        <img class="icone" src="../img/mapa/endereco.svg" alt="Localização">
                        <div>
                            <strong>Endereço</strong><br>
                            <a href="#">Avenida General Carneiro - 2523 SOROCABA / SP</a>
                        </div>
                    </div>

                    <!-- TELEFONE -->
                    <div class="linha-info telefone">
                        <img class="icone" src="../img/mapa/ligar-mapa.svg" alt="Telefone">
                        <div class="telefone-content">
                            <strong>Ligar</strong><br>
                            <div class="numero-telefone">0800 200 2000</div>
                        </div>
                    </div>

                    <!-- HORÁRIOS -->
                    <div class="linha-info horario">
                        <img class="icone" src="../img/mapa/horario-mapa.svg" alt="Horário">
                        <div class="info-texto"><strong>Horários de funcionamento</strong></div>
                        <img class="icone-direita" src="../img/seta-para-baixo-preta.png" alt="Detalhes">
                    </div>

                    <div class="horarios-detalhados">
                        <p><strong>Segunda à sexta:</strong> 08:00 - 19:00</p>
                        <p><strong>Sábado:</strong> 08:00 - 18:00</p>
                        <p><strong>Domingo:</strong> 09:00 - 16:00</p>
                        <p><strong>Feriados:</strong> 09:00 - 16:00</p>
                        <p>Recomendamos consultar a loja sobre os horários diferenciados de funcionamento (feriados,
                            etc).</p>
                    </div>

                    <!-- ESTOQUE -->
                    <div class="linha-info">
                        <img class="icone" src="../img/mapa/estoque-mapa.svg" alt="Estoque">
                        <div class="info-texto"><strong>Estoque da loja</strong></div>
                        <img class="icone-direita" src="../img/seta-diagonal-direita.png" alt="Ver Estoque">
                    </div>

                </div>
            </div>

            <div class="agendar-box">
                <div class="left">
                    <img src="../img/calendario-verde.svg" alt="Calendário" class="icone">
                    <div>
                        <strong>Agende uma visita</strong><br>
                        <span>Conheça esse carro de pertinho.</span>
                    </div>
                </div>
                <button class="btn-agendar">Agendar visita</button>
            </div>
        </section>
        <section class="recomendados">
            <h3>Você também pode gostar...</h3>
            <div class="carros-recomendados">
                <div class="card-carro">
                    <img src="../img/modelos/carro1.webp" alt="BMW 218i Azul">
                    <h4>BMW 218i</h4>
                    <p>Preço: R$ 320.950,00</p>
                </div>
                <div class="card-carro">
                    <img src="../img/modelos/carro2.webp" alt="BMW 218i Preto">
                    <h4>BMW 218i</h4>
                    <p>Preço: R$ 320.950,00</p>
                </div>
                <div class="card-carro">
                    <img src="../img/modelos/carro3.webp" alt="BMW 218i Branco">
                    <h4>BMW 218i</h4>
                    <p>Preço: R$ 320.950,00</p>
                </div>
            </div>
        </section>


    </main>
    <footer class="footer">
        <!-- Faixa colorida com logo no meio -->
        <div class="footer-strip">
            <div class="footer-logo-top">
                <img src="../img/logos/logoofcbmw.png" alt="BMW Logo" />
            </div>
        </div>

        <div class="footer-container">

            <div class="footer-top">

                <div class="footer-logo">
                    <img src="../img/logos/BMW_M_100px_white.png" alt="BMW Logo">
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
                            <img src="../img/redes-sociais/email-branco.png" alt="Email ícone">
                            suporte@bmwmotors.com
                        </li>
                        <li>
                            <img src="../img/tel-branco.png" alt="Telefone ícone">
                            (11) 1234-5678
                        </li>
                        <li>
                            <img src="../img/pin-de-localizacao-branco.png" alt="Endereço ícone">
                            Av. BMW, 123 - São Paulo
                        </li>
                        <li>
                            <img src="../img/cards/relogio-branco.png" alt="Relógio ícone">
                            Seg à Sex 09:00 às 19:00
                        </li>
                    </ul>
                </div>

                <div class="footer-social">
                    <h4>Redes sociais</h4>
                    <div class="social-icons">
                        <a href="#" class="instagram"><img src="../img/redes-sociais/insta-branco.png"
                                alt="Instagram"></a>
                        <a href="#" class="whatsapp"><img src="../img/redes-sociais/whatsapp-branco.png"
                                alt="WhatsApp"></a>
                        <a href="#" class="facebook"><img src="../img/redes-sociais/face-branco.png" alt="Facebook"></a>
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

    <!-- Modal -->
    <div class="modal" id="modalPagamento">
        <div class="modal-content">
            <img src="../img/x-fechar.png" alt="Fechar" class="fechar-modal" id="fecharModal">

            <h2>Formas de pagamento</h2>
            <p class="subtitulo">Você escolhe a forma que mais combina com você!</p>

            <div class="cards-pagamento">
                <div class="card">
                    <img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Pix">
                    <p>Pix</p>
                </div>
                <div class="card">
                    <img src="../img/formas-de-pagamento/boleto_mb.png" alt="Boleto">
                    <p>Boleto Bancário</p>
                </div>
                <div class="card">
                    <img src="../img/formas-de-pagamento/creditcard_mb.png" alt="Cartão">
                    <p>Cartão de Crédito</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/veiculo-carrossel.js"></script>
    <script src="../js/mostrar-mais.js"></script>
    <script src="../js/horario-detalhado.js"></script>
    <script src="../js/corrigindo-aside.js"></script>
    <script src="../js/modal-payments.js"></script>
    <script>
    function trocarImagem(icone, srcHover) {
        const img = icone.querySelector("img");
        const originalSrc = img.src;

        icone.addEventListener("mouseenter", () => {
            img.src = srcHover;
        });

        icone.addEventListener("mouseleave", () => {
            img.src = originalSrc;
        });
    }

    // Rodar só depois que carregar tudo
    document.addEventListener("DOMContentLoaded", () => {
        trocarImagem(document.querySelector(".instagram"), "../img/redes-sociais/insta-colorido.png");
        trocarImagem(document.querySelector(".whatsapp"), "../img/redes-sociais/whatsapp-colorido.png");
        trocarImagem(document.querySelector(".facebook"), "../img/redes-sociais/facebook-colorido.png");
    });
    </script>
</body>
</html>