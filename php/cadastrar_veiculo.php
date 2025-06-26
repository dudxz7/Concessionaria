<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

// Conectar ao banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar modelos existentes para o select
$modelos_result = $conn->query("SELECT id, modelo, ano FROM modelos ORDER BY modelo");

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $modelo_id = (int) $_POST['modelo_id'];
    $estoque = (int) $_POST['estoque'];

    $sucesso = true;

    for ($i = 0; $i < $estoque; $i++) {
        // Primeiro, insere o veículo com chassi temporário
        $chassi_temp = 'TEMP'; // Temporário só pra passar pela inserção
        $stmt = $conn->prepare("INSERT INTO veiculos (modelo_id, numero_chassi) VALUES (?, ?)");
        $stmt->bind_param("is", $modelo_id, $chassi_temp);

        if ($stmt->execute()) {
            $veiculo_id = $stmt->insert_id;

            // Gera o chassi final com base no ID do veículo
            $chassi_final = 'BMW0000000000' . str_pad($veiculo_id, 4, '0', STR_PAD_LEFT);

            // Atualiza o número de chassi com o valor correto
            $update_stmt = $conn->prepare("UPDATE veiculos SET numero_chassi = ? WHERE id = ?");
            $update_stmt->bind_param("si", $chassi_final, $veiculo_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            $sucesso = false;
            break;
        }

        $stmt->close();
    }

    if ($sucesso) {
        $mensagem = "Todos os veículos foram cadastrados com sucesso!";
        $mensagem_tipo = "sucesso";
        echo "<script>window.location.href = 'consultar_veiculos.php';</script>";
    } else {
        $mensagem = "Ocorreu um erro ao cadastrar os veículos.";
        $mensagem_tipo = "erro";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veículo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
    #estoque_id {
        background: #f2f2f2 !important;
        border-radius: 20px !important;
        padding: 10px !important;
        border: 1.5px solid #d1d1d1 !important;
        font-size: 1rem !important;
        outline: none !important;
        transition: border 0.2s !important;
    }
    #estoque_id:focus {
        border-color: #2f4eda !important;
    }
    /* Força o visual do select2 para ficar igual ao input */
    .select2-container--default .select2-selection--single {
        background: #f2f2f2 !important;
        border-radius: 20px !important;
        padding: 10px !important;
        border: 1.5px solid #d1d1d1 !important;
        font-size: 1rem !important;
        outline: none !important;
        transition: border 0.2s !important;
        height: auto !important;
        min-height: 40px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default .select2-selection--single.select2-selection--focus {
        border-color: #2f4eda !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #222 !important;
        line-height: normal !important;
        padding-left: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 10px !important;
    }
    </style>
</head>
<body>
    <div class="container">
        <a href="consultar_veiculos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Cadastrar Veículo</h2>

        <form action="cadastrar_veiculo.php" method="post">
            <!-- Seleção de Modelo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <select name="modelo_id" id="modelo_id" required>
                        <option value="">Selecione um modelo</option>
                        <?php while ($modelo = $modelos_result->fetch_assoc()) { ?>
                            <option value="<?= $modelo['id'] ?>">
                                <?= $modelo['modelo'] ?> - <?= $modelo['ano'] ?>
                            </option>
                        <?php } ?>
                    </select>
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Estoque -->
            <div class="input-group">
                <label>Quantidade em Estoque</label>
                <div class="input-wrapper">
                    <input type="number" id="estoque_id" name="estoque" required min="1">
                    <img src="../img/veiculos/estoque.png" alt="Ícone estoque">
                </div>
            </div>

            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Cadastrar Veículo
            </button>
        </form>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Select2 para forçar dropdown para baixo
        $('#modelo_id').select2({
            dropdownParent: $('#modelo_id').parent(),
            dropdownPosition: 'below',
            width: '100%'
        });

        const selectInput = document.getElementById("modelo_id");
        const estoqueInput = document.getElementById("estoque_id");
        const botao = document.querySelector(".btn");

        // Desativa o botão no início
        botao.disabled = true;
        botao.style.opacity = "0.5";
        botao.style.cursor = "not-allowed";

        // Armazena os valores originais
        const valorOriginal = {
            select: selectInput.value,
            estoque: estoqueInput.value
        };

        function verificarAlteracoes() {
            const selectAlterado = selectInput.value !== valorOriginal.select;
            const estoqueAlterado = estoqueInput.value !== valorOriginal.estoque;

            if (selectAlterado && estoqueAlterado) {
                botao.disabled = false;
                botao.style.opacity = "1";
                botao.style.cursor = "pointer";
            } else {
                botao.disabled = true;
                botao.style.opacity = "0.5";
                botao.style.cursor = "not-allowed";
            }
        }

        selectInput.addEventListener("input", verificarAlteracoes);
        estoqueInput.addEventListener("input", verificarAlteracoes);
    });
    </script>
</body>
</html>