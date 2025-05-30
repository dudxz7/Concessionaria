<?php
session_start();

// Verificar se o usuário está logado
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;

// Nome do usuário
$nomeUsuario = "";
if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomes = explode(" ", trim($_SESSION['usuarioNome']));
    $nomeUsuario = $nomes[0] ?? "";
}

// Verificar se é admin
$linkPerfil = '../perfil.php';
if ($usuarioLogado && isset($_SESSION['usuarioAdmin']) && $_SESSION['usuarioAdmin'] == 1) {
    $linkPerfil = 'php/admin_dashboard.php';
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
    "TO" => "Palmas"
];

$estado = $_SESSION['usuarioEstado'] ?? "";
$capital = $capitais[$estado] ?? "Cidade - Estado";
$capital .= $estado ? " - $estado" : "";

// Conexão com banco
include 'conexao.php';

// ID do modelo
$id_modelo = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Variáveis padrão
$imagemCarro = 'padrao.webp';
$modelo = '';
$modelo_slug = '';
$anoModelo = 'Ano Indefinido';
$quilometragem = '0 Km';
$cores_disponiveis = [];
$precoOriginal = 0;
$precoComDesconto = 0;
$temPromocao = false;
$dataFimPromo = null;
$corPrincipal = '';

$sql = "SELECT cor_principal FROM detalhes_modelos WHERE modelo_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_modelo);
$stmt->execute();
$stmt->bind_result($corPrincipal);
$stmt->fetch();
$stmt->close();

if ($id_modelo > 0) {
    // Buscar dados do modelo
    $sqlModelo = "SELECT modelo, ano, cor, preco FROM modelos WHERE id = ?";
    $stmt = $conn->prepare($sqlModelo);
    $stmt->bind_param("i", $id_modelo);
    $stmt->execute();
    $stmt->bind_result($modeloBanco, $anoBanco, $corBanco, $precoBanco);
    if ($stmt->fetch()) {
        $modelo = $modeloBanco;
        $anoModelo = ($anoBanco - 1) . "/" . $anoBanco;
        $cores_disponiveis = explode(",", $corBanco);
        $precoOriginal = $precoBanco;
        $modelo_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($modelo));

        // ✅ Coloca a cor principal no início
        if (!empty($corPrincipal) && in_array($corPrincipal, $cores_disponiveis)) {
            $cores_disponiveis = array_diff($cores_disponiveis, [$corPrincipal]);
            array_unshift($cores_disponiveis, $corPrincipal);
        }
    }
    $stmt->close();

    // Imagem principal da cor principal
    $sqlImg = "SELECT i.imagem, i.cor 
               FROM imagens_secundarias i
               INNER JOIN detalhes_modelos d ON i.modelo_id = d.modelo_id
               WHERE i.modelo_id = ? AND i.cor = d.cor_principal
               ORDER BY i.ordem ASC
               LIMIT 1";
    $stmt = $conn->prepare($sqlImg);
    $stmt->bind_param("i", $id_modelo);
    $stmt->execute();
    $stmt->bind_result($imagemBanco, $corPrincipal);
    if ($stmt->fetch() && !empty($imagemBanco)) {
        $corPrincipalSlug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($corPrincipal));
        $imagemCarro = "cores/{$modelo_slug}/{$corPrincipalSlug}/{$imagemBanco}";
    }
    $stmt->close();

    // Promoção
    $sqlPromo = "SELECT preco_com_desconto, data_limite FROM promocoes WHERE modelo_id = ? AND status = 'Ativa' AND data_limite >= CURDATE() ORDER BY data_limite DESC LIMIT 1";
    $stmt = $conn->prepare($sqlPromo);
    $stmt->bind_param("i", $id_modelo);
    $stmt->execute();
    $stmt->bind_result($precoDesconto, $dataLimite);
    if ($stmt->fetch()) {
        $temPromocao = true;
        $precoComDesconto = $precoDesconto;
        $dataFimPromo = $dataLimite;
    }
    $stmt->close();

    // Recomendação de veículos similares
    $precoReferencia = $temPromocao ? $precoComDesconto : $precoOriginal;
    $sqlRecomendados = "
    SELECT 
        m.id, 
        m.modelo, 
        m.preco,
        d.cor_principal
    FROM modelos m
    LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
    WHERE m.id != ?
    ORDER BY ABS(m.preco - ?) ASC 
    LIMIT 3
";
    $stmt = $conn->prepare($sqlRecomendados);
    $stmt->bind_param("id", $id_modelo, $precoReferencia);
    $stmt->execute();
    $resultRecomendados = $stmt->get_result();

    $veiculosRecomendados = [];
    while ($row = $resultRecomendados->fetch_assoc()) {
        $idRec = $row['id'];
        $modeloRec = $row['modelo'];
        $precoRec = $row['preco'];
        $corPrincipalRec = $row['cor_principal'];

        $modeloSlugRec = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($modeloRec));
        $corSlugRec = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($corPrincipalRec));
        $imagemRec = 'padrao.webp'; // valor padrão

        // Buscar imagem da cor principal (ordem 0 é a principal)
        $sqlImg = "
        SELECT imagem 
        FROM imagens_secundarias 
        WHERE modelo_id = ? AND cor = ? 
        ORDER BY ordem ASC 
        LIMIT 1
    ";
        $stmtImg = $conn->prepare($sqlImg);
        $stmtImg->bind_param("is", $idRec, $corPrincipalRec);
        $stmtImg->execute();
        $stmtImg->bind_result($imagemBanco);
        if ($stmtImg->fetch() && !empty($imagemBanco)) {
            $imagemRec = "../img/modelos/cores/{$modeloSlugRec}/{$corSlugRec}/{$imagemBanco}";
        } else {
            $imagemRec = "../img/modelos/padrao.webp"; // fallback real
        }
        $stmtImg->close();

        $veiculosRecomendados[] = [
            'id' => $idRec,
            'modelo' => $modeloRec,
            'preco' => $precoRec,
            'imagem_path' => $imagemRec,
            'cor' => $corPrincipalRec
        ];
    }
    $stmt->close();
}

// Cor selecionada, via POST ou GET, ou cor principal por padrão
$corSelecionada = '';
if (!empty($_POST['cor'])) {
    // Pode vir como array (checkbox múltiplo), pegar o primeiro
    if (is_array($_POST['cor'])) {
        $corSelecionada = trim($_POST['cor'][0]);
    } else {
        $corSelecionada = trim($_POST['cor']);
    }
} elseif (!empty($_GET['cor'])) {
    $corSelecionada = trim($_GET['cor']);
}
if (empty($corSelecionada)) {
    $corSelecionada = $corPrincipal; // padrão para cor principal
}
$corSelecionadaLower = strtolower($corSelecionada);

$imagens = [];
$sqlImagens = "SELECT imagem, cor, ordem FROM imagens_secundarias WHERE modelo_id = ? ORDER BY ordem ASC";
$stmt = $conn->prepare($sqlImagens);
$stmt->bind_param("i", $id_modelo);
$stmt->execute();
$resultImagens = $stmt->get_result();

while ($row = $resultImagens->fetch_assoc()) {
    // Filtrar por cor, se quiser (pode ignorar ou ajustar conforme sua lógica)
    $corImagem = strtolower(trim($row['cor']));
    if (empty($corSelecionadaLower) || $corImagem === $corSelecionadaLower) {
        $cor_slug = preg_replace('/[^a-z0-9\-]/i', '-', $corImagem);
        $imagemPath = "../img/modelos/cores/{$modelo_slug}/{$cor_slug}/" . $row['imagem'];

        if (file_exists($imagemPath)) {
            $imagens[] = [
                'path' => $imagemPath,
                'ordem' => $row['ordem']
            ];
        }
    }
}

// Extrai só o "último ano " ex a var $anoModelo = "2022/2023" vira "2023" tlgd só isso mrm 
$anos = explode('/', $anoModelo);
$anoFinal = $anos[1];

$stmt->close();

// --- Pix session auto-redirect logic ---
// REMOVIDO: Não redireciona mais automaticamente para o Pix aqui. O usuário deve clicar em 'Compre agora'.

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $modelo; ?> <?php echo $anoFinal ?>: Preço, versões e mais! Bmw motors</title>
    <link rel="icon" href="../img/logos/logoofcbmw.png" />
    <link rel="stylesheet" href="../css/pagina-veiculo.css" />
    <link rel="stylesheet" href="../css/navbar.css" />
    <link rel="stylesheet" href="../css/footer.css" />
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css">
    <style>
        .navbar {
            background-color: black;
            position: fixed;
            z-index: 11;
        }

        .footer {
            z-index: 1000;
        }

        /* img {
        filter: hue-rotate(-210deg) brightness(1.2) saturate(1.5);
        } */
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <nav class="navbar">
        <div class="logo">
            <a href="../index.php">
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
                        <img src="../img/navbar/usercomcontorno.png" alt="Login">
                    </a>
                    <a href="../login.html"><span>Entrar</span></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container">

        <!-- Seção da Imagem e Carrossel -->
        <section class="car-section">
            <!-- Imagem principal grande -->
            <img id="imagem-principal" src="../img/modelos/<?= htmlspecialchars($imagemCarro); ?>" alt="BMW"
                class="car-image" />

            <!-- Setas do carrossel -->
            <div class="anchor-left">
                <img src="../img/new-arrow-slider-left.svg" alt="Seta esquerda" class="arrow-left" />
            </div>
            <div class="anchor-right">
                <img src="../img/new-arrow-slider-right.svg" alt="Seta direita" class="arrow-right" />
            </div>

            <div class="thumbnail-row">
                <?php foreach ($imagens as $img): ?>
                    <?php
                    // Evita erro se path ou cor estiverem ausentes
                    $path = isset($img['path']) ? htmlspecialchars($img['path']) : '';
                    $cor = isset($img['cor']) ? strtolower(trim($img['cor'])) : '';
                    ?>
                    <img src="<?= $path ?>" alt="Imagem do modelo" class="thumb" data-cor="<?= $cor ?>" />
                <?php endforeach; ?>
            </div>

            <!-- Card lateral -->
            <aside class="side-card">
                <div class="header">
                    <h2><span><?= htmlspecialchars($modelo) ?></span></h2>
                    <button class="favorite">
                        <img src="../img/coracoes/coracao-nao-salvo.png" alt="Favorito" class="favorite-icon" />
                        Favoritar
                    </button>

                </div>

                <p class="year-km"><?= htmlspecialchars($anoModelo) ?> ·
                    <span><?= htmlspecialchars($quilometragem) ?></span>
                </p>

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

                <!-- Cores Disponíveis -->
                <div class="input-group">
                    <div class="checkbox-group">
                        <?php
                        $cores_atual = isset($_POST['cor']) ? $_POST['cor'] : [];

                        // Exibir as cores disponíveis (já ordenadas, com a principal primeiro)
                        foreach ($cores_disponiveis as $cor) {
                            $cor = trim($cor);
                            $checked = in_array($cor, $cores_atual) ? 'checked' : '';
                            echo '<label class="checkbox-field">
                <input type="checkbox" name="cor[]" value="' . htmlspecialchars($cor) . '" class="color-checkbox" ' . $checked . ' data-cor="' . htmlspecialchars($cor) . '">
                <div class="checkmark" style="background-color: ' . htmlspecialchars($cor) . ';"></div>
            </label>';
                        }
                        ?>
                    </div>
                </div>

                <hr class="section-divider" />

                <div class="price-section">
                    <?php if ($temPromocao): ?>
                        <p class="old-price">R$ <?= number_format($precoOriginal, 2, ',', '.') ?></p>
                        <p class="new-price">
                            R$ <?= number_format($precoComDesconto, 2, ',', '.') ?>
                            <img src="../img/em-formacao.png" alt="Engrenagem" class="gear-icon" id="gearIcon">
                        </p>
                    <?php else: ?>
                        <p class="new-price">R$ <?= number_format($precoOriginal, 2, ',', '.') ?></p>
                    <?php endif; ?>
                    <a href="#" class="payment-link" id="abrirModal">Formas de pagamento</a>
                </div>

                <button class="buy-button" id="btn-comprar" data-id="<?= $id_modelo ?>">Compre agora</button>
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
                    <span><?= htmlspecialchars($anoModelo) ?></span>
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
                <?php if (!empty($corBanco)): ?>
                    <div class="feature-box">
                        <img src="../img/sobre-veiculos/cor-de-fabrica-sobre.svg" alt="Cor">
                        <span><?= htmlspecialchars($corBanco) ?></span>
                    </div>
                <?php endif; ?>

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
                <?php foreach ($veiculosRecomendados as $veiculo): ?>
                    <div class="card-carro">
                        <a href="pagina_veiculo.php?id=<?= $veiculo['id'] ?>">
                            <img src="<?= $veiculo['imagem_path'] ?>" alt="<?= $veiculo['modelo'] ?>">
                            <h4><?= $veiculo['modelo'] ?></h4>
                            <p>Preço: R$ <?= number_format($veiculo['preco'], 2, ',', '.') ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
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
                    <img src="../img/formas-de-pagamento/icons8-foto-500.png" alt="Pix">
                    <p>Pix</p>
                </div>
                <div class="card">
                    <img src="../img/formas-de-pagamento/boletov2.png" alt="Boleto">
                    <p>Boleto Bancário</p>
                </div>
                <div class="card">
                    <img src="../img/formas-de-pagamento/cartao-do-banco.png" alt="Cartão">
                    <p>Cartão de Crédito</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de cronômetro -->
    <div id="modalPromo" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('modalPromo').style.display='none'">
                <img src="../img/x-fechar.png" alt="Fechar" style="width: 20px; cursor: pointer;">
            </span>
            <h2>Promoção acaba em:</h2>
            <div id="countdown" data-fim="<?= str_replace(' ', 'T', $dataFimPromo) ?>"
                style="font-size: 24px; margin-top: 10px;"></div>
        </div>
    </div>

    <script src="../js/veiculo-carrossel.js"></script>
    <script src="../js/only-one-selected.js"></script>
    <script src="../js/mostrar-mais.js"></script>
    <script src="../js/horario-detalhado.js"></script>
    <script src="../js/corrigindo-aside.js"></script>
    <script src="../js/modal-payments.js"></script>
    <script src="../js/modal-cronometro.js"></script>
    <script src="../js/variar-cores.js"></script>
    <script src="../js/trocar-imagem-footer.js"></script>
    <script>
        function updateCarrossel(corSelecionada) {
            const modeloSlug = '<?= $modelo_slug ?>';
            const caminhoBase = `../img/modelos/cores/${modeloSlug}/${corSelecionada.toLowerCase()}/`;

            // Troca as miniaturas
            const imagensSecundarias = document.querySelectorAll('.thumbnail-row img.thumb');
            const extensoes = ['webp', 'png', 'jpg', 'jpeg'];

            imagensSecundarias.forEach((img, idx) => {
                const nomeBase = img.getAttribute('src').split('/').pop().split('.')[0];
                let encontrada = false;
                for (const ext of extensoes) {
                    const novaImagem = caminhoBase + nomeBase + '.' + ext;
                    const imgTeste = new Image();
                    imgTeste.src = novaImagem;
                    imgTeste.onload = function () {
                        if (!encontrada) {
                            img.setAttribute('src', novaImagem);
                            // Troca a imagem principal pela primeira miniatura encontrada
                            if (idx === 0) {
                                document.getElementById('imagem-principal').src = novaImagem;
                            }
                            encontrada = true;
                        }
                    };
                }
            });
        }

        // Troca a imagem principal ao clicar na cor
        document.querySelectorAll('.color-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    const corSelecionada = this.getAttribute('data-cor');
                    updateCarrossel(corSelecionada);

                    // Desmarca os outros checkboxes
                    document.querySelectorAll('.color-checkbox').forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // Troca a imagem principal ao clicar na miniatura
        document.querySelectorAll('.thumbnail-row img.thumb').forEach((img) => {
            img.addEventListener('click', function () {
                document.getElementById('imagem-principal').src = this.src;
            });
        });

        // Envia a cor selecionada para a página de pagamento
        const btnComprar = document.getElementById('btn-comprar');
        if (btnComprar) {
            btnComprar.addEventListener('click', function() {
                // Pega a cor selecionada (primeiro checkbox marcado)
                const corSelecionada = document.querySelector('.color-checkbox:checked');
                const cor = corSelecionada ? encodeURIComponent(corSelecionada.value) : '';
                const id = btnComprar.getAttribute('data-id');
                if (!cor) {
                    alert('Selecione uma cor antes de comprar!');
                    return;
                }
                // Verifica se já existe sessão Pix válida para este veículo/cor
                fetch('verifica_pix_session.php?id=' + encodeURIComponent(id) + '&cor=' + cor)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.pix_valido) {
                            window.location.href = `realizar_pagamento_pix.php?id=${id}&cor=${cor}`;
                        } else {
                            // Sempre inclui &redir=1 ao redirecionar para pagamento.php
                            window.location.href = `pagamento.php?id=${id}&cor=${cor}&redir=1`;
                        }
                    })
                    .catch(() => {
                        // fallback: vai para pagamento normal, sempre com &redir=1
                        window.location.href = `pagamento.php?id=${id}&cor=${cor}&redir=1`;
                    });
            });
        }
    </script>
</body>

</html>