<?php
session_start();
include('conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioCargo'])) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

// Permite apenas Funcionario, Gerente ou Admin
$cargosPermitidos = ['Gerente', 'Admin'];
if (!in_array($_SESSION['usuarioCargo'], $cargosPermitidos)) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

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
                <input type="text" id="modelo" name="modelo" required value="<?php echo htmlspecialchars($modelo['modelo']); ?>">
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
    const modeloInput = document.getElementById("modelo");
    const anoInput = document.getElementById("ano");
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="cor[]"]');
    const botao = document.querySelector(".btn");

    // Desativa o botão no início
    botao.disabled = true;
    botao.style.opacity = "0.5";
    botao.style.cursor = "not-allowed";

    // Armazena os valores originais
    const valorOriginal = {
        modelo: modeloInput.value,
        ano: anoInput.value,
        preco: precoInput.value,
        cores: Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value).sort().join(',')
    };

    function verificarAlteracoes() {
        const modeloAlterado = modeloInput.value !== valorOriginal.modelo;
        const anoAlterado = anoInput.value !== valorOriginal.ano;
        const precoAlterado = precoInput.value !== valorOriginal.preco;
        const coresSelecionadas = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value).sort().join(',');
        const coresAlteradas = coresSelecionadas !== valorOriginal.cores;

        if (modeloAlterado || anoAlterado || precoAlterado || coresAlteradas) {
            botao.disabled = false;
            botao.style.opacity = "1";
            botao.style.cursor = "pointer";
        } else {
            botao.disabled = true;
            botao.style.opacity = "0.5";
            botao.style.cursor = "not-allowed";
        }
    }

    // Formatação do campo preço
    precoInput.addEventListener("input", function () {
        let valor = precoInput.value.replace(/\D/g, "");
        valor = (parseInt(valor, 10) / 100).toFixed(2);
        precoInput.value = valor.replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        verificarAlteracoes();
    });

    modeloInput.addEventListener("input", verificarAlteracoes);
    anoInput.addEventListener("input", verificarAlteracoes);

    checkboxes.forEach(cb => {
        cb.addEventListener("change", verificarAlteracoes);
    });
});
</script>
</body>
</html>
