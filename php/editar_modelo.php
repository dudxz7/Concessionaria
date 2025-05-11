<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

// Conexão
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o usuário é Admin ou Gerente
$usuarioId = $_SESSION['usuarioId'] ?? null;

if ($usuarioId) {
    $sqlCargo = "SELECT cargo FROM clientes WHERE id = ?";
    $stmtCargo = $conn->prepare($sqlCargo);
    $stmtCargo->bind_param("i", $usuarioId);
    $stmtCargo->execute();
    $resultadoCargo = $stmtCargo->get_result();
    $dadosCargo = $resultadoCargo->fetch_assoc();

    if (!$dadosCargo || ($dadosCargo['cargo'] !== 'Admin' && $dadosCargo['cargo'] !== 'Gerente')) {
        echo "<h2>Acesso Negado</h2>";
        echo "<p>Você não tem permissão para acessar esta página.</p>";
        exit;
    }
    $stmtCargo->close();
} else {
    echo "Usuário não identificado.";
    exit;
}

// Buscar informações do modelo
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID do modelo não especificado.";
    exit;
}

$modelo_id = intval($_GET['id']);

// Busca dados atuais do modelo
$sqlModelo = "SELECT modelos.*, detalhes_modelos.descricao, detalhes_modelos.imagem 
              FROM modelos 
              LEFT JOIN detalhes_modelos ON modelos.id = detalhes_modelos.modelo_id 
              WHERE modelos.id = ?";
$stmtModelo = $conn->prepare($sqlModelo);
$stmtModelo->bind_param("i", $modelo_id);
$stmtModelo->execute();
$resultado = $stmtModelo->get_result();
$modelo = $resultado->fetch_assoc();

if (!$modelo) {
    echo "Modelo não encontrado.";
    exit;
}
$imagemAtual = $modelo['imagem'] ?? '';

// Atualização
$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoModelo = trim($_POST['modelo']);
    $novoAno = intval($_POST['ano']);
    $novoPreco = str_replace(['.', ','], ['', '.'], $_POST['preco']);
    $novaDescricao = trim($_POST['descricao']);
    $coresSelecionadas = isset($_POST['cor']) ? implode(',', $_POST['cor']) : '';

    $novaImagem = $imagemAtual; // Assume que não mudou

    // Se o usuário enviou nova imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nomeTemp = $_FILES['imagem']['tmp_name'];
        $nomeImagem = basename($_FILES['imagem']['name']); // Usa o nome original do arquivo
        $caminhoDestino = '../img/modelos/' . $nomeImagem;

        if (move_uploaded_file($nomeTemp, $caminhoDestino)) {
            // Se quiser, você pode excluir a imagem antiga aqui:
            if (!empty($imagemAtual) && file_exists('../img/modelos/' . $imagemAtual)) {
                unlink('../img/modelos/' . $imagemAtual);
            }
            $novaImagem = $nomeImagem;
        }
    }

    // Atualizar tabela modelos
    $stmtUpdate = $conn->prepare("UPDATE modelos SET modelo = ?, cor = ?, ano = ?, preco = ? WHERE id = ?");
    $stmtUpdate->bind_param("ssidi", $novoModelo, $coresSelecionadas, $novoAno, $novoPreco, $modelo_id);
    $sucessoModelo = $stmtUpdate->execute();
    $stmtUpdate->close();

    // Agora atualizar ou inserir em detalhes_modelos
    if ($sucessoModelo) {
        $sqlCheck = "SELECT id FROM detalhes_modelos WHERE modelo_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $modelo_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $existeDetalhes = $resultCheck->num_rows > 0;
        $stmtCheck->close();

        if ($existeDetalhes) {
            // Se existe, faz UPDATE
            $stmtDetalhes = $conn->prepare("UPDATE detalhes_modelos SET descricao = ?, imagem = ? WHERE modelo_id = ?");
            $stmtDetalhes->bind_param("ssi", $novaDescricao, $novaImagem, $modelo_id);
        } else {
            // Se não existe, faz INSERT
            $stmtDetalhes = $conn->prepare("INSERT INTO detalhes_modelos (descricao, imagem, modelo_id) VALUES (?, ?, ?)");
            $stmtDetalhes->bind_param("ssi", $novaDescricao, $novaImagem, $modelo_id);
        }
        $sucessoDetalhes = $stmtDetalhes->execute();
        $stmtDetalhes->close();
    }

    if ($sucessoModelo && isset($sucessoDetalhes) && $sucessoDetalhes) {
        header("Location: consultar_modelos.php");
        exit;
    } else {
        $mensagem = "Erro ao atualizar modelo.";
        $mensagem_tipo = "erro";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Editar Modelo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css">
    <link rel="stylesheet" href="../css/drag-and-drop.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>

<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Editar Modelo</h2>

        <form action="" method="post" enctype="multipart/form-data">
            <!-- Modelo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" required
                        value="<?php echo htmlspecialchars($modelo['modelo']); ?>">
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Fabricante -->
            <div class="input-group">
                <label>Fabricante</label>
                <div class="input-wrapper">
                    <input type="text" name="fabricante" id="input-fabricante" value="BMW" readonly>
                    <img src="../img/veiculos/fabricante.png" alt="Ícone fabricante">
                </div>
            </div>

            <!-- Ano -->
            <div class="input-group">
                <label>Ano</label>
                <div class="input-wrapper">
                    <input type="text" name="ano" required maxlength="4" id="ano"
                        value="<?php echo htmlspecialchars($modelo['ano']); ?>">
                    <img src="../img/registro/ano.png" alt="Ícone ano">
                </div>
            </div>

            <!-- Preço -->
            <div class="input-group">
                <label>Preço</label>
                <div class="input-wrapper">
                    <input type="text" name="preco" required id="preco"
                        value="<?php echo number_format($modelo['preco'], 2, ',', '.'); ?>">
                    <img src="../img/veiculos/preco.png" alt="Ícone preço">
                </div>
            </div>

            <!-- Descrição -->
            <div class="input-group">
                <label>Descrição</label>
                <div class="input-wrapper">
                    <input type="text" name="descricao" maxlength="62" required
                        value="<?php echo htmlspecialchars($modelo['descricao']); ?>">
                    <img src="../img/veiculos/escrevendo.png" alt="Ícone descrição">
                </div>
            </div>

            <!-- Imagem Atual / Pré-visualização com Upload -->
            <div class="input-group">
                <label>Imagem Atual / Alterar Imagem</label>
                <div class="drop-zone" id="drop-zone">
                    <?php if (!empty($modelo['imagem'])) : ?>
                    <img id="preview" src="../img/modelos/<?php echo htmlspecialchars($modelo['imagem']); ?>"
                        alt="Pré-visualização">
                    <p id="file-name" style="margin-top: 10px; color: #333;">
                        <?php echo htmlspecialchars($modelo['imagem']); ?></p>
                    <?php else : ?>
                    <img src="../img/upload-na-nuvem.png" alt="Ícone Upload" class="upload-icon" id="upload-icon">
                    <p id="drop-text">Arraste uma imagem aqui ou <span class="browse-btn">selecione um arquivo</span>
                    </p>
                    <img id="preview" src="#" alt="Pré-visualização" style="display: none;">
                    <p id="file-name" style="margin-top: 10px; color: #333;"></p>
                    <?php endif; ?>
                    <input type="file" name="imagem" accept="image/*" id="file-input">
                    <p id="file-name" style="margin-top: 10px; color: ;"></p>
                </div>
            </div>


            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>"
                <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Atualizar Modelo
            </button>
        </form>
    </div>

    <script src="../js/drag-and-drop.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const precoInput = document.getElementById("preco");
        const anoInput = document.getElementById("ano");

        anoInput.addEventListener("input", function() {
            this.value = this.value.replace(/\D/g, "");
        });

        precoInput.addEventListener("input", function() {
            let valor = precoInput.value.replace(/\D/g, "");
            valor = (parseInt(valor, 10) / 100).toFixed(2);
            precoInput.value = valor.replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
    </script>
</body>

</html>