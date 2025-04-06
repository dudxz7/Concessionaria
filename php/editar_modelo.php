<?php
include('conexao.php');

// Atualizar modelo, se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $modelo = $_POST['modelo'];
    $fabricante = $_POST['fabricante'];
    $ano = $_POST['ano'];
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']);
    $cores = isset($_POST['cor']) ? implode(',', $_POST['cor']) : '';

    $stmt = $conn->prepare("UPDATE modelos SET modelo = ?, fabricante = ?, ano = ?, preco = ?, cor = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $modelo, $fabricante, $ano, $preco, $cores, $id);

    if ($stmt->execute()) {
        header("Location: consultar_modelos.php?msg=atualizado");
        exit;
    } else {
        $mensagem = "Erro ao atualizar modelo.";
        $mensagem_tipo = "erro";
    }
}

// Carregar dados para exibição do formulário
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: consultar_modelos.php');
    exit;
}

$id = $_GET['id'] ?? $_POST['id'];
$stmt = $conn->prepare("SELECT * FROM modelos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Modelo não encontrado!";
    exit;
}

$modelo = $result->fetch_assoc();
$coresSelecionadas = explode(',', $modelo['cor']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Modelo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css">
    <link rel="icon" href="../img/logoofcbmw.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask-plugin/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
<div class="container">
    <a href="consultar_modelos.php" class="back-button">
        <img src="../img/seta-esquerda24.png" alt="Voltar">
    </a> 
    <h2>Editar Modelo</h2>
    
    <form action="editar_modelo.php" method="post">
        <input type="hidden" name="id" value="<?php echo $modelo['id']; ?>">

        <div class="input-group">
            <label>Modelo</label>
            <div class="input-wrapper">
                <input type="text" name="modelo" required value="<?php echo htmlspecialchars($modelo['modelo']); ?>">
                <img src="../img/carro.png" alt="Ícone modelo">
            </div>
        </div>

        <div class="input-group">
            <label>Fabricante</label>
            <div class="input-wrapper">
                <input type="text" name="fabricante" value="BMW" readonly>
                <img src="../img/fabricante.png" alt="Ícone fabricante">
            </div>
        </div>

        <div class="input-group">
            <label>Ano</label>
            <div class="input-wrapper">
                <input type="text" name="ano" required maxlength="4" id="ano" value="<?php echo $modelo['ano']; ?>">
                <img src="../img/ano.png" alt="Ícone ano">
            </div>
        </div>

        <div class="input-group">
            <label>Preço</label>
            <div class="input-wrapper">
                <input type="text" name="preco" required id="preco" value="<?php echo number_format($modelo['preco'], 2, ',', '.'); ?>">
                <img src="../img/preco.png" alt="Ícone preço">
            </div>
        </div>

        <div class="input-group">
            <label>Cores Disponíveis</label>
            <div class="checkbox-group">
                <?php
                $cores_disponiveis = ["Preto", "Branco", "Azul", "Prata", "Verde", "Vermelho"];
                foreach ($cores_disponiveis as $cor) {
                    $checked = in_array($cor, $coresSelecionadas) ? 'checked' : '';
                    echo '<label class="checkbox-field">
                            <input type="checkbox" name="cor[]" value="'.$cor.'" '.$checked.'>
                            <div class="checkmark"></div>
                          </label>';
                }
                ?>
            </div>
        </div>

        <!-- Mensagem de erro ou sucesso -->
        <?php if (!empty($mensagem)) : ?>
        <div id="error-message" class="<?php echo $mensagem_tipo; ?>" style="display:block;">
            <?php echo $mensagem; ?>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn">
            <img src="../img/verifica.png" alt="Ícone de check">
            Atualizar Modelo
        </button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const precoInput = document.getElementById("preco");

        precoInput.addEventListener("input", function () {
            let valor = precoInput.value.replace(/\D/g, "");
            valor = (parseInt(valor, 10) / 100).toFixed(2);
            precoInput.value = valor.replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
</script>
</body>
</html>
