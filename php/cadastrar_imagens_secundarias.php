<?php
session_start();

if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "sistema_bmw");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$usuarioId = $_SESSION['usuarioId'] ?? null;
if ($usuarioId) {
    $sql = "SELECT cargo FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_assoc();
    if (!$dados || !in_array($dados['cargo'], ['Admin', 'Gerente'])) {
        echo "<h2>Acesso Negado</h2><p>Você não tem permissão para acessar esta página.</p>";
        exit;
    }
    $stmt->close();
} else {
    echo "Usuário não identificado.";
    exit;
}

$mensagem = '';
$mensagem_tipo = '';

$modelo_id_get = isset($_GET['modelo_id']) ? intval($_GET['modelo_id']) : ($_POST['modelo_id'] ?? null);
$modeloSelecionado = '';
$coresDisponiveis = [];

if ($modelo_id_get) {
    $stmt = $conn->prepare("SELECT modelo, cor FROM modelos WHERE id = ?");
    $stmt->bind_param("i", $modelo_id_get);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $modeloSelecionado = $row['modelo'];
        $coresDisponiveis = array_map('trim', explode(',', $row['cor']));
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo_id = intval($_POST['modelo_id']);
    $cor = trim($_POST['cor']);
    $imagens = $_FILES['imagens'];

    // Buscar o nome do modelo com base no modelo_id vindo via POST
    $stmt = $conn->prepare("SELECT modelo FROM modelos WHERE id = ?");
    $stmt->bind_param("i", $modelo_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $modeloSelecionado = $row['modelo'];
    } else {
        $mensagem = "Modelo não encontrado.";
        $mensagem_tipo = "erro";
        exit;
    }
    $stmt->close();

    // Gerar slugs
    $modelo_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($modeloSelecionado));
    $cor_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($cor));

    // Caminho da pasta
    $pasta_base = __DIR__ . "/../img/modelos/cores/{$modelo_slug}/{$cor_slug}/";

    // Criação da pasta, se necessário
    if (!is_dir($pasta_base)) {
        if (!mkdir($pasta_base, 0777, true)) {
            $mensagem = "Erro ao criar a pasta para o modelo: {$modelo_slug}, cor: {$cor_slug}.";
            $mensagem_tipo = "erro";
            exit;
        }
    }

    // Upload das imagens
    for ($i = 0; $i < count($imagens['name']); $i++) {
        if ($imagens['error'][$i] === UPLOAD_ERR_OK) {
            $tmp_name = $imagens['tmp_name'][$i];
            $nome_arquivo = basename($imagens['name'][$i]);
            $destino = $pasta_base . $nome_arquivo;

            if (move_uploaded_file($tmp_name, $destino)) {
                // Inserção no banco
                $stmt = $conn->prepare("INSERT INTO imagens_secundarias (modelo_id, imagem, cor) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $modelo_id, $nome_arquivo, $cor);
                $stmt->execute();
                $stmt->close();
            } else {
                $mensagem = "Erro ao mover o arquivo: {$nome_arquivo}.";
                $mensagem_tipo = "erro";
                break;
            }
        } else {
            $mensagem = "Erro no upload do arquivo: {$imagens['name'][$i]}.";
            $mensagem_tipo = "erro";
            break;
        }
    }

    if (empty($mensagem)) {
        $mensagem = "Imagens enviadas com sucesso!";
        $mensagem_tipo = "sucesso";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Upload Imagens Secundárias</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/drag-and-drop.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }

        .preview-box {
            width: 100px;
            text-align: center;
            font-size: 13px;
            color: #333;
        }

        .preview-box img {
            width: 100%;
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Upload de Imagens Secundárias</h2>

        <form action="cadastrar_imagens_secundarias.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="modelo_id" value="<?= htmlspecialchars($modelo_id_get) ?>">

            <!-- Modelo (não editável) -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" value="<?= htmlspecialchars($modeloSelecionado) ?>" readonly>
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Cor -->
            <div class="input-group">
                <label>Cor</label>
                <div class="input-wrapper">
                    <?php if (count($coresDisponiveis) === 1): ?>
                        <input type="text" name="cor" value="<?= htmlspecialchars($coresDisponiveis[0]) ?>" readonly>
                    <?php else: ?>
                        <select name="cor" required>
                            <option value="">Selecione uma cor</option>
                            <?php
                            $corSelecionada = $_POST['cor'] ?? '';
                            foreach ($coresDisponiveis as $cor) {
                                $selecionado = ($corSelecionada === $cor) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($cor) . "\" $selecionado>" . htmlspecialchars($cor) . "</option>";
                            }
                            ?>

                        </select>
                    <?php endif; ?>
                    <img src="../img/veiculos/cor.png" alt="Ícone cor">
                </div>
            </div>

            <!-- Upload de Imagens -->
            <div class="input-group">
                <label>Imagens Secundárias</label>
                <div class="drop-zone" id="drop-zone">
                    <img src="../img/upload-na-nuvem.png" alt="Ícone Upload" class="upload-icon" id="upload-icon">
                    <p id="drop-text">Arraste imagens aqui ou <span class="browse-btn">selecione arquivos</span></p>
                    <input type="file" name="imagens[]" accept="image/*" multiple required id="file-input">
                    <div id="preview-container" class="preview-container"></div>
                </div>
            </div>

            <!-- Instrução sobre a ordem das imagens -->
            <div class="input-group">
                <label style="margin-bottom: 15px;">Ordem recomendada das imagens</label>
                <p style="font-size: 14px; color: #555; margin-top: -10px; margin-bottom: 20px;">
                    As imagens devem seguir um giro de <strong>360° ao redor do carro</strong>, começando pela mesma
                    posição da imagem principal. A última imagem pode ser do <strong>interior do carro</strong>
                    (opcional).
                </p>
            </div>

            <!-- Mensagem -->
            <?php if (!empty($mensagem)): ?>
                <div id="error-message" class="<?= $mensagem_tipo ?>" style="display:block;">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Enviar Imagens
            </button>
        </form>
    </div>

    <script src="../js/multiplas-drag-and-drop.js"></script>
</body>

</html>