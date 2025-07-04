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
    $linkPerfil = 'admin_dashboard.php';
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

$favorito = false;
$coracaoImg = "coracao-nao-salvo.png";
if (isset($_SESSION['usuarioId']) && $id_modelo > 0) {
    $usuarioId = $_SESSION['usuarioId'];
    $stmt_favorito = $conn->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
    $stmt_favorito->bind_param("ii", $usuarioId, $id_modelo);
    $stmt_favorito->execute();
    $stmt_favorito->store_result();
    $favorito = $stmt_favorito->num_rows > 0;
    $stmt_favorito->close();
    $coracaoImg = $favorito ? "coracao-salvo.png" : "coracao-nao-salvo.png";
}

// Buscar foto de perfil do usuário se logado
$fotoPerfilUsuario = '';
if ($usuarioLogado && isset($_SESSION['usuarioId'])) {
    $idUser = $_SESSION['usuarioId'];
    $sqlFoto = "SELECT foto_perfil FROM clientes WHERE id = ? LIMIT 1";
    $stmtFoto = $conn->prepare($sqlFoto);
    $stmtFoto->bind_param("i", $idUser);
    $stmtFoto->execute();
    $stmtFoto->bind_result($fotoPerfilUsuario);
    $stmtFoto->fetch();
    $stmtFoto->close();
    // Ajusta caminho se necessário
    if (!empty($fotoPerfilUsuario) && !file_exists($fotoPerfilUsuario)) {
        // Tenta caminho relativo a partir da pasta php
        if (file_exists("../" . $fotoPerfilUsuario)) {
            $fotoPerfilUsuario = "../" . $fotoPerfilUsuario;
        }
    }
}

// Buscar o primeiro veículo disponível (em estoque) para o modelo exibido
$veiculo_id_disponivel = null;
// Busca a quantidade de veículos disponíveis para o modelo
$sqlEstoque = "SELECT COUNT(*) FROM veiculos WHERE modelo_id = ? AND (status = 'disponivel' OR status IS NULL)";
$stmtEstoque = $conn->prepare($sqlEstoque);
$stmtEstoque->bind_param("i", $id_modelo);
$stmtEstoque->execute();
$stmtEstoque->bind_result($quantidadeEstoque);
$stmtEstoque->fetch();
$stmtEstoque->close();

if (!empty($quantidadeEstoque) && $quantidadeEstoque > 0) {
    // Se há estoque, pega o primeiro veículo disponível desse modelo
    $sqlVeiculo = "SELECT id FROM veiculos WHERE modelo_id = ? AND (status = 'disponivel' OR status IS NULL) LIMIT 1";
    $stmtVeiculo = $conn->prepare($sqlVeiculo);
    $stmtVeiculo->bind_param("i", $id_modelo);
    $stmtVeiculo->execute();
    $stmtVeiculo->bind_result($veiculo_id_disponivel);
    $stmtVeiculo->fetch();
    $stmtVeiculo->close();
}
// Se não houver veículo disponível, pega o primeiro do modelo (pode ser null)
if (!$veiculo_id_disponivel) {
    $sqlVeiculo = "SELECT id FROM veiculos WHERE modelo_id = ? LIMIT 1";
    $stmtVeiculo = $conn->prepare($sqlVeiculo);
    $stmtVeiculo->bind_param("i", $id_modelo);
    $stmtVeiculo->execute();
    $stmtVeiculo->bind_result($veiculo_id_disponivel);
    $stmtVeiculo->fetch();
    $stmtVeiculo->close();
}

$botaoComprarHabilitado = ($quantidadeEstoque > 0 && $veiculo_id_disponivel && $veiculo_id_disponivel > 0);
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

        .heart-counter-navbar {
            position: absolute;
            top: -6px;
            right: 50px;
            background-color: #2f4eda;
            color: white;
            font-size: 12px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
            font-family: 'Poppins', sans-serif;
            width: 14px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 2;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        .heart-counter-navbar.oculto {
            display: none;
        }

        .modal-360 {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
        }

        .modal-360.active {
            display: flex;
        }

        .modal-360-content {
            position: relative;
            max-width: 90vw;
            max-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #img360 {
            max-width: 100vw;
            max-height: 80vh;
            user-select: none;
            background-color: #d9d9d9 !important;
            border-radius: 12px;
            box-shadow: 0 4px 32px #000a;
        }

        .modal-360-indicators {
            position: absolute;
            bottom: 32px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
        }

        .modal-360-indicators .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #fff3;
            border: 1.5px solid #fff8;
        }

        .modal-360-indicators .dot.active {
            background: #2f4eda;
            border-color: #fff;
        }
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
            <a href="favoritos.php" style="position:relative;">
                <img src="../img/navbar/heart.png" alt="Favoritos" class="heart-icon-navbar">
                <?php
                // Heart counter igual ao index.php
                if ($usuarioLogado && isset($_SESSION['usuarioId'])) {
                    $idUser = $_SESSION['usuarioId'];
                    $sqlFav = "SELECT COUNT(*) FROM favoritos WHERE usuario_id = ?";
                    $stmtFav = $conn->prepare($sqlFav);
                    $stmtFav->bind_param("i", $idUser);
                    $stmtFav->execute();
                    $stmtFav->bind_result($qtdFav);
                    $stmtFav->fetch();
                    $stmtFav->close();
                    if ($qtdFav > 0) {
                        echo '<span class="heart-counter-navbar">' . $qtdFav . '</span>';
                    }
                }
                ?>
            </a>
            <div class="login">
                <?php if ($usuarioLogado): ?>
                    <!-- Se o usuário estiver logado, mostra a foto de perfil se existir -->
                    <a href="<?php echo $linkPerfil; ?>">
                        <?php if (!empty($fotoPerfilUsuario) && file_exists($fotoPerfilUsuario)): ?>
                            <img src="<?php echo $fotoPerfilUsuario; ?>" alt="Perfil"
                                style="width:38px;height:38px;object-fit:cover;border-radius:50%;vertical-align:middle;">
                        <?php else: ?>
                            <img src="../img/navbar/usercomcontorno.png" alt="Perfil">
                        <?php endif; ?>
                    </a>
                    <a href="<?php echo $linkPerfil; ?>"><span><?php echo htmlspecialchars($nomeUsuario); ?></span></a>
                <?php else: ?>
                    <!-- Se não estiver logado, mostra o link para login -->
                    <a href="../login.html">
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
                    <button type="button" class="btn-favoritar" data-modelo-id="<?= (int) $id_modelo ?>"
                        style="background:none;border:none;padding:0;cursor:pointer;">
                        <img src="../img/coracoes/<?= $coracaoImg ?>" alt="Favoritar" class="heart-icon"
                            draggable="false"> Favoritar
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

                <?php if ($botaoComprarHabilitado): ?>
                    <button class="buy-button" id="btn-comprar" data-id="<?= (int) $veiculo_id_disponivel ?>"
                        data-modelo-id="<?= (int) $id_modelo ?>">Compre agora</button>
                <?php else: ?>
                    <button class="buy-button" id="btn-comprar" disabled
                        style="opacity:0.6;cursor:not-allowed;">Indisponível</button>
                <?php endif; ?>
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
                        <img src="../img/acessorios-veiculos/sensor-de-estacionamento.svg" alt="Sensor de estacionamento" class="accessory-icon">
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
                        <a href="#" id="abrirItensVerificados">Abrir itens verificados</a>
                    </div>
                </div>
            </div>

        </section>

        <section class="container-visita">
            <h2>Conheça esse carro em nossa loja</h2>
            <div class="conteudo-visita">
                <div class="mapa">
                    <iframe src="https://www.google.com/maps?q=Rua+J%C3%BAlio+Siqueira+390+Fortaleza+CE&output=embed"
                        width="450" height="200" style="border:0;border-radius:12px;box-shadow:0 2px 12px #0001;"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="info">

                    <!-- ENDEREÇO -->
                    <div class="linha-info">
                        <img class="icone" src="../img/mapa/endereco.svg" alt="Localização">
                        <div>
                            <strong>Endereço</strong><br>
                            <a href="https://maps.google.com/?q=Rua+J%C3%BAlio+Siqueira+390+Fortaleza+CE"
                                target="_blank">Rua Júlio Siqueira, 390 - Fortaleza/CE</a>
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

    <!-- Modal 360° -->
    <div id="modal360" class="modal-360" style="display:none;">
        <div class="modal-360-content">
            <img id="img360" src="" alt="BMW 360°" draggable="false" />
        </div>
        <div class="modal-360-indicators" id="modal360Indicators"></div>
    </div>

    <!-- Modal Itens Verificados -->
    <div class="modal" id="modalItensVerificados">
        <div class="modal-content" style="max-width:700px;text-align:left;">
            <span class="close" id="fecharItensVerificados"
                style="position:absolute;font-size:28px;cursor:pointer;">&times;</span>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:10px;">
                <img src="../img/certificado/360-itens-verificados.webp" alt="360 verificados"
                    style="width:48px;height:48px;">
                <div>
                    <h2 style="margin:0;font-size:1.5em;">360 Itens verificados.</h2>
                    <p style="margin:0;font-size:1em;color:#444;">Esta é uma lista de todos os itens que o nosso time de
                        especialistas verificaram neste carro para garantir que você rode com tranquilidade.</p>
                </div>
            </div>
            <hr>
            <div id="conteudoItensVerificados">
                <strong style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <img src="../img/sobre-veiculos/suv-sobre.svg" alt="Carroceria" style="width:22px;height:22px;">
                    Carroceria
                </strong>
                <ul>
                    <li>Batente da porta dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Batente da porta dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Batente da porta traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Batente da porta traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Batente do porta-malas <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Break light <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Emblema dianteiro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Emblema traseiro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol de neblina dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Farol de neblina dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Farol direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Grade frontal <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Grade frontal central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Grade frontal inferior <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Higienização da carroceria <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna de neblina traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Lanterna de neblina traseira esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lateral traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lateral traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Painel corta-fogo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Palheta do limpador dianteiro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Palheta do limpador traseiro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Para-barro dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Para-barro dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Para-barro traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Para-barro traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Refletor dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Refletor dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Refletor traseiro direito <span style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Refletor traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Soleira da porta dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Soleira da porta dianteira esquerda <span style="color:green;font_weight:bold;">&#10003;</span>
                    </li>
                    <li>Soleira da porta traseira direita <span style="color:green;font_weight:bold;">&#10003;</span>
                    </li>
                    <li>Soleira da porta traseira esquerda <span style="color:green;font_weight:bold;">&#10003;</span>
                    </li>
                    <li>Soleira do painel traseiro <span style="color:green;font_weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/direcao-eletrica.svg" alt="Direção"
                        style="width:22px;height:22px;"> Direção
                </strong>
                <ul>
                    <li>Bomba de direção hidráulica (vazamentos) <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Caixa de direção (vazamentos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Folga no volante <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mangueiras de direção hidráulica (vazamentos) <span
                            style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Terminal de direção direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Terminal de direção esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/eletricidade.png" alt="Elétrica e Acionamento"
                        style="width:22px;height:22px;"> Elétrica e Acionamento
                </strong>
                <ul>
                    <li>Acionamento da buzina <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento de abertura do capô <span style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Acionamento de abertura do porta-malas <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento de abertura do tanque de combustível <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do ar condicionado <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do desembaçador do vidro traseiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do esguicho dianteiro e traseiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do limpador do para-brisa e do vidro traseiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do teto solar <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do vidro dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do vidro dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do vidro traseiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamento do vidro traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acionamentos da chave do carro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Ajuste de temperatura do ar <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Ajuste de velocidade do ar <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Alto-falante dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Alto-falante dianteiro esquerdo <span style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Alto-falante traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Alto-falante traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Antena <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco dianteiro direito - Acionamentos <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco dianteiro esquerdo - Acionamentos <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco traseiro direito - Acionamentos <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco traseiro esquerdo - Acionamentos <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bateria <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Botão de partida <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Botões do volante <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Botões multimídia <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cabos da bateria <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Chave de ignição <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Chave de seta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Chave do limpador <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Espelho retrovisor direito - regulagem <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Espelho retrovisor esquerdo - regulagem <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Funcionamento do ar condicionado <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Funcionamento do multimídia <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Maçaneta interna da porta dianteira direita <span
                            style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Maçaneta interna da porta dianteira esquerda <span
                            style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Maçaneta interna da porta traseira direita <span
                            style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Maçaneta interna da porta traseira esquerda <span
                            style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Medidor do nível do combustível <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Painel de instrumentos <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tomada 12 V <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Trava central das portas <span style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Trava de segurança infantil - direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Trava de segurança infantil - esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Velocímetro <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/freio-abs-acc.svg" alt="Freios"
                        style="width:22px;height:22px;"> Freios
                </strong>
                <ul>
                    <li>Acionamento do pedal de freio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Central do ABS <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cilindro de freio traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Cilindro de freio traseiro esquerdo <span style="color:green;font_weight:bold;">&#10003;</span>
                    </li>
                    <li>Cilindro mestre de freio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Conduíte de freio dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Conduíte de freio dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Conduíte de freio traseiro direito <span style="color:green;font_weight:bold;">&#10003;</span>
                    </li>
                    <li>Conduíte de freio traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Disco de freio dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Disco de freio dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Disco de freio traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Disco de freio traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Nível de fluído de freio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pastilha de freio dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pastilha de freio dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pastilha de freio traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pastilha de freio traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pinça de freio dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pinça de freio dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pinça de freio traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pinça de freio traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Regulagem do freio de mão <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Servo freio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa do reservatório do fluído de freio <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/sobre-veiculos/placa-sobre.svg" alt="Identificação veicular / Documentos"
                        style="width:22px;height:22px;"> Identificação veicular / Documentos
                </strong>
                <ul>
                    <li>Chassi (Identificação) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Manual de Condução <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Manual de Garantia <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Manual de Manutenção <span style="color:green;font_weight:bold;">&#10003;</span></li>
                    <li>Para-brisa (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Placa dianteira <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Placa traseira <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro dianteiro direito (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro dianteiro esquerdo (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro traseiro central (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro traseiro direito (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro traseiro esquerdo (VIS) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/farol-de-neblina-acc.svg" alt="Iluminação"
                        style="width:22px;height:22px;"> Iluminação
                </strong>
                <ul>
                    <li>Break light - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cabine banco traseiro - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cabine motorista - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol de neblina direito - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol de neblina esquerdo - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol direito - Luz alta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol direito - Luz baixa <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol direito - Luz de lanterna <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol direito - Luz de seta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol esquerdo - Luz alta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol esquerdo - Luz baixa <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Farol esquerdo - Luz de lanterna <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Farol esquerdo - Luz de seta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna de neblina direita - Luz <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Lanterna de neblina esquerda - Luz <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Lanterna direita - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna direita - Luz de freio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna direita - Luz de ré <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna direita - Luz de seta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna esquerda - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna esquerda - Luz de freio <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Lanterna esquerda - Luz de ré <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Lanterna esquerda - Luz de seta <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Placa traseira - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Porta-malas - Luz <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Repetidor direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Repetidor esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/banco-de-motorista-acc.svg" alt="Interior"
                        style="width:22px;height:22px;">
                    Interior
                </strong>
                <ul>
                    <li>Acabamento do banco dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acabamento do banco dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Acabamento do banco traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Acabamento do banco traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Ajustador do cinto de segurança direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Ajustador do cinto de segurança esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Apoio de braço / Console <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bagagito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco dianteiro direito (geral) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Banco dianteiro esquerdo (geral) <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Banco traseiro (geral) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Carpete dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Carpete dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Carpete traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Carpete traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cinto de segurança dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Cinto de segurança dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cinto de segurança traseiro central <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Cinto de segurança traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Cinto de segurança traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Coifa da manopla do câmbio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Console Central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Difusor de ar central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Difusor de ar direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Difusor de ar esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Difusor de ar traseiro central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Encosto de cabeça dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Encosto de cabeça dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Encosto de cabeça traseiro central <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Encosto de cabeça traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Encosto de cabeça traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Espelho do quebra-sol direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Espelho do quebra-sol esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Forro da porta dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Forro da porta dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Forro da porta traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Forro da porta traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Forro da tampa traseira <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Guarnição de porta dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Guarnição de porta dianteira esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Guarnição de porta traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Guarnição de porta traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Guarnição do Porta-malas <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Higienização do interior <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Kit de ferramentas <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Manopla do câmbio <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pedal de embreagem - Revestimento <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pedal de freio - Revestimento <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pedal do acelerador - Revestimento <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Porta-copos <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Porta-luvas <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Quebra-sol direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Quebra-sol esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Rede de carga do compartimento de bagagem <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Retrovisor central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Revestimento interno do teto <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Suporte do bagagito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tapete dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tapete dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tapete do porta-malas <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tapete traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tapete traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Trava do estepe <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vedação da porta dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Vedação da porta dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Vedação da porta traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Vedação da porta traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Volante - Revestimento <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/sobre-veiculos/motor-sobre.svg" alt="Motor" style="width:22px;height:22px;"> Motor
                </strong>
                <ul>
                    <li>Bomba d´água (vazamentos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bujão do carter (vazamentos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Cabo de velas / bobina <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Capa da correia dentada <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Carter (vazamentos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Correia auxiliar <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Correia dentada <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxim do motor <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxins do escapamento <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Defletor de calor <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Escapamento final <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Escapamento inicial <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Escapamento intermediário <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Flexível do escapamento <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Hidrovácuo (funcionamento) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Higienização do motor <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Linhas de combustível (vazamentos aparentes) <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mangueiras de combustível (vazamentos) <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Motor (barulho e vibrações anormais) <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Óleo do motor (nível e alterações aparentes) <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Protetor do carter <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Radiador <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Reservatório de direção hidráulica <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Sistema de arrefecimento (vazamentos) <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tanque de combustível <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vela de ignição <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/rodas-acc.svg" alt="Pneus, Rodas e Rolamentos"
                        style="width:22px;height:22px;"> Pneus, Rodas e Rolamentos
                </strong>
                <ul>
                    <li>Calota dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Calota dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Calota traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Calota traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Parafusos da roda dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Parafusos da roda dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Parafusos da roda do estepe <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Parafusos da roda traseira direita <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Parafusos da roda traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Pneu dianteiro direito (danos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu dianteiro direito com medida original <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu dianteiro esquerdo (danos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu dianteiro esquerdo com medida original <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu estepe (danos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu traseiro direito (danos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu traseiro direito com medida original <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu traseiro esquerdo (danos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneu traseiro esquerdo com medida original <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneus com mesma profundidade no eixo dianteiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneus com mesma profundidade no eixo traseiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneus de mesmo modelo no eixo dianteiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneus de mesmo modelo no eixo traseiro <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pneus sem remodelagem <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Profundidade do estepe <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Profundidade do pneu dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Profundidade do pneu dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Profundidade do pneu traseiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Profundidade do pneu traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Roda dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Roda dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Roda do estepe <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Roda traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Roda traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Rolamento dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Rolamento dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Rolamento traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Rolamento traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa central da roda dianteira direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa central da roda dianteira esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa central da roda do estepe <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa central da roda traseira direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Tampa central da roda traseira esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/ar-condicionado-acc.svg"" alt=" Revisão"
                        style="width:22px;height:22px;">
                    Revisão
                </strong>
                <ul>
                    <li>Filtro de ar <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Filtro de ar condicionado <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Filtro de combustível <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Filtro de óleo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Nível de água do radiador <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Nível de óleo motor <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Realização das revisões <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Reservatório de água do para-brisa <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/sobre-veiculos/suv-sobre.svg" alt="Suspensão" style="width:22px;height:22px;">
                    Suspensão
                </strong>
                <ul>
                    <li>Agregado da suspensão <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Amortecedor dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Amortecedor dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Amortecedor traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Amortecedor traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bandeja dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bandeja dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bandeja traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bandeja traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Batente do amortecedor traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Batente do amortecedor dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Batente do amortecedor dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Batente do amortecedor traseiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bieleta / Barra estabilizadora direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Bieleta / Barra estabilizadora esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa do amortecedor dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa do amortecedor dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa do amortecedor traseiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa do amortecedor traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxim do amortecedor dianteiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxim do amortecedor dianteiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxim do amortecedor traseiro direito <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coxim do amortecedor traseiro esquerdo <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mola dianteira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mola dianteira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mola traseira direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Mola traseira esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pivô dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pivô dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pivô traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Pivô traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/transmissao-manual.png" alt="Transmissão"
                        style="width:22px;height:22px;"> Transmissão
                </strong>
                <ul>
                    <li>Acionamento do pedal de embreagem <span style="color:green;font-weight:bold;">&#10003;</span>
                    </li>
                    <li>Caixa de marcha (vazamentos) <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa externa da homocinética esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa externa da homocinética direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa interna da homocinética direita <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Coifa interna da homocinética esquerda <span
                            style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Embreagem <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Transmissão automática <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Transmissão manual <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Trava da homocinética direita <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Trava da homocinética esquerda <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
                <strong style="display:flex;align-items:center;gap:8px;margin:18px 0 8px 0;">
                    <img src="../img/acessorios-veiculos/porta-acc.svg" alt="Vidros elétricos"
                        alt="Vidros e alterações de características" style="width:22px;height:22px;"> Vidros e
                    alterações de características
                </strong>
                <ul>
                    <li>Capota de fibra <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Capota marítima <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>GNV <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm para-brisa <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Insulfilm vidro vigia <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Para-brisa <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro dianteiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro dianteiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro traseiro direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro traseiro esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro vigia <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro do retrovisor direito <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro do retrovisor esquerdo <span style="color:green;font-weight:bold;">&#10003;</span></li>
                    <li>Vidro do retrovisor central <span style="color:green;font-weight:bold;">&#10003;</span></li>
                </ul>
            </div>
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
                    const modeloSlug = '<?= $modelo_slug ?>';
                    const caminhoBase = `../img/modelos/cores/${modeloSlug}/${corSelecionada.toLowerCase()}/`;
                    const extensoes = ['webp', 'png', 'jpg', 'jpeg'];
                    const maxImagens = 12; // Limite máximo de imagens por cor
                    const thumbnailRow = document.querySelector('.thumbnail-row');
                    // Remove todas as miniaturas atuais
                    thumbnailRow.innerHTML = '';
                    let miniaturasValidas = 0;
                    let primeiraImagem = null;
                    let imagensCarregadas = 0;
                    // Tenta carregar até maxImagens possíveis
                    for (let i = 1; i <= maxImagens; i++) {
                        let encontrada = false;
                        for (let ext of extensoes) {
                            const nomeArquivo = i + '.' + ext;
                            const url = caminhoBase + nomeArquivo;
                            const testImg = new Image();
                            testImg.onload = function () {
                                if (!encontrada) {
                                    encontrada = true;
                                    // Cria miniatura
                                    const thumb = document.createElement('img');
                                    thumb.src = url;
                                    thumb.className = 'thumb';
                                    thumb.setAttribute('data-cor', corSelecionada.toLowerCase());
                                    thumb.alt = 'Imagem do modelo';
                                    thumb.onclick = function () {
                                        document.getElementById('imagem-principal').src = url;
                                    };
                                    thumbnailRow.appendChild(thumb);
                                    // Primeira imagem válida vira principal
                                    if (miniaturasValidas === 0) {
                                        primeiraImagem = url;
                                        document.getElementById('imagem-principal').src = url;
                                    }
                                    miniaturasValidas++;
                                }
                            };
                            testImg.onerror = function () {
                                imagensCarregadas++;
                                // Se terminou de tentar todas e nenhuma válida, mostra padrão
                                if (imagensCarregadas === maxImagens * extensoes.length && miniaturasValidas === 0) {
                                    document.getElementById('imagem-principal').src = '../img/modelos/padrao.webp';
                                }
                            };
                            testImg.src = url;
                        }
                    }
                    // Se nenhuma miniatura válida após um tempo, mostra imagem padrão
                    setTimeout(function () {
                        if (miniaturasValidas === 0) {
                            document.getElementById('imagem-principal').src = '../img/modelos/padrao.webp';
                        }
                    }, 500);
                    // Desmarca os outros checkboxes
                    document.querySelectorAll('.color-checkbox').forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // Troca a imagem principal ao clicar na miniatura

        // Envia a cor selecionada para a página de pagamento
        const btnComprar = document.getElementById('btn-comprar');
        if (btnComprar) {
            btnComprar.addEventListener('click', function () {
                const corSelecionada = document.querySelector('.color-checkbox:checked');
                const cor = corSelecionada ? encodeURIComponent(corSelecionada.value) : '';
                const modeloId = btnComprar.getAttribute('data-modelo-id'); // Corrigido para pegar o modelo_id
                if (!cor) {
                    alert('Selecione uma cor antes de comprar!');
                    return;
                }
                // Verificação de estoque via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'verifica_estoque.php?id=' + encodeURIComponent(modeloId), true); // Corrigido para enviar modelo_id
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var data = JSON.parse(xhr.responseText);
                                if (data && data.disponivel) {
                                    window.location.href = 'pagamento.php?id=' + encodeURIComponent(modeloId) + '&cor=' + cor;
                                } else {
                                    alert('Este veículo está sem estoque no momento.');
                                }
                            } catch (e) {
                                alert('Erro ao processar resposta do servidor.');
                                console.error('Erro ao processar resposta:', xhr.responseText, e);
                            }
                        } else {
                            alert('Erro ao verificar estoque. Tente novamente.');
                        }
                    }
                };
                xhr.send();
            });
        }

        // --- Script ISOLADO do 360° Modal (agora pega imagens até max-1 igual ao PHP original) ---
        document.addEventListener('DOMContentLoaded', function () {
            let imagens360 = [];
            let idxAtual = 0;
            let isDragging = false;
            let startX = 0;
            let lastIdx = 0;
            let img360 = null;
            let modal360 = null;
            let indicators = null;
            let dragAccum = 0;
            const PIXELS_PER_FRAME = 15; // Quantidade de pixels para cada "frame" de arrasto

            // Função para buscar as imagens da cor selecionada (igual carrossel, mas só até max-1)


            function buscarImagens360(cor) {
                const modeloSlug = '<?= $modelo_slug ?>';
                const caminhoBase = `../img/modelos/cores/${modeloSlug}/${cor.toLowerCase()}/`;
                const extensoes = ['webp', 'png', 'jpg', 'jpeg'];
                const maxImagens = 12;
                let imgs = [];
                let carregadas = 0;
                let totalValidas = 0;
                let promises = [];
                for (let i = 1; i <= maxImagens; i++) {
                    for (let ext of extensoes) {
                        const url = caminhoBase + i + '.' + ext;
                        promises.push(new Promise(resolve => {
                            const testImg = new Image();
                            testImg.onload = function () {
                                resolve(url);
                            };
                            testImg.onerror = function () {
                                resolve(null);
                            };
                            testImg.src = url;
                        }));
                    }
                }
                // Só retorna as imagens válidas, e remove a última (imagens.length - 1)
                return Promise.all(promises).then(results => {
                    let validas = results.filter(Boolean);
                    if (validas.length > 1) {
                        validas = validas.slice(0, validas.length - 1);
                    }
                    return validas;
                });
            }

            async function showModal360(startIdx = 0) {
                // Pega a cor atualmente selecionada
                const corCheckbox = document.querySelector('.color-checkbox:checked');
                let cor = corCheckbox ? corCheckbox.getAttribute('data-cor') : '<?= $corPrincipal ?>';
                imagens360 = await buscarImagens360(cor);
                if (!imagens360.length) return;
                idxAtual = startIdx;
                modal360 = document.getElementById('modal360');
                img360 = document.getElementById('img360');
                indicators = document.getElementById('modal360Indicators');
                modal360.classList.add('active');
                modal360.style.display = 'flex';
                updateImg360();
                renderIndicators();
            }
            function closeModal360() {
                document.getElementById('modal360').classList.remove('active');
                document.getElementById('modal360').style.display = 'none';
            }
            function updateImg360() {
                if (img360 && imagens360[idxAtual]) {
                    img360.src = imagens360[idxAtual];
                    updateIndicators();
                }
            }
            function renderIndicators() {
                if (!indicators) return;
                indicators.innerHTML = '';
                for (let i = 0; i < imagens360.length; i++) {
                    const dot = document.createElement('span');
                    dot.className = 'dot' + (i === idxAtual ? ' active' : '');
                    indicators.appendChild(dot);
                }
            }
            function updateIndicators() {
                if (!indicators) return;
                Array.from(indicators.children).forEach((dot, i) => {
                    dot.className = 'dot' + (i === idxAtual ? ' active' : '');
                });
            }
            // Mouse/touch events
            function onDragStart(e) {
                isDragging = true;
                startX = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
                lastIdx = idxAtual;
                dragAccum = 0;
            }
            function onDragMove(e) {
                if (!isDragging) return;
                const x = e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX;
                const delta = x - startX;
                dragAccum = delta;
                if (Math.abs(dragAccum) >= PIXELS_PER_FRAME) {
                    let steps = Math.floor(dragAccum / PIXELS_PER_FRAME);
                    let novoIdx = (lastIdx - steps) % imagens360.length;
                    if (novoIdx < 0) novoIdx += imagens360.length;
                    if (novoIdx !== idxAtual) {
                        idxAtual = novoIdx;
                        updateImg360();
                    }
                }
            }
            function onDragEnd() {
                if (!isDragging) return;
                lastIdx = idxAtual;
                dragAccum = 0;
                isDragging = false;
            }
            // Sempre reatribui o click na imagem principal (caso DOM mude)
            function bindImagemPrincipalClick() {
                var imgPrincipal = document.getElementById('imagem-principal');
                if (imgPrincipal) {
                    imgPrincipal.removeEventListener('click', showModal360Handler);
                    imgPrincipal.addEventListener('click', showModal360Handler);
                }
            }
            async function showModal360Handler(e) {
                e.preventDefault();
                await showModal360(0);
            }
            bindImagemPrincipalClick();
            // Também reatribui após troca de cor/miniaturas
            document.querySelectorAll('.color-checkbox').forEach(function (cb) {
                cb.addEventListener('change', function () {
                    bindImagemPrincipalClick();
                });
            });
            var closeBtn = document.getElementById('closeModal360');
            if (closeBtn) closeBtn.addEventListener('click', closeModal360);
            var modalEl = document.getElementById('modal360');
            if (modalEl) {
                modalEl.addEventListener('mousedown', onDragStart);
                modalEl.addEventListener('mousemove', onDragMove);
                modalEl.addEventListener('mouseup', onDragEnd);
                modalEl.addEventListener('mouseleave', onDragEnd);
                modalEl.addEventListener('touchstart', onDragStart);
                modalEl.addEventListener('touchmove', onDragMove);
                modalEl.addEventListener('touchend', onDragEnd);
                modalEl.addEventListener('click', function (e) {
                    if (e.target === this) closeModal360();
                });
            }
        });

        // Abrir modal de itens verificados
        document.getElementById('abrirItensVerificados').onclick = function (e) {
            e.preventDefault();
            document.getElementById('modalItensVerificados').style.display = 'flex';
        };
        document.getElementById('fecharItensVerificados').onclick = function () {
            document.getElementById('modalItensVerificados').style.display = 'none';
        };
        window.onclick = function (event) {
            var modal = document.getElementById('modalItensVerificados');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>

</html>