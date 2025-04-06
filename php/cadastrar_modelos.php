<?php
// INÍCIO DO PHP
$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once '../php/conexao.php'; // <-- Certifique-se de que esse caminho está certo

    $modelo = trim($_POST['modelo']);
    $fabricante = trim($_POST['fabricante']);
    $ano = intval($_POST['ano']);
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']); // Converte para decimal no padrão do banco

    // Verifica se pelo menos uma cor foi selecionada
    if (!isset($_POST['cor']) || empty($_POST['cor'])) {
        $mensagem = "Selecione pelo menos uma cor.";
        $mensagem_tipo = "erro";
    } else {
        $cores = implode(',', $_POST['cor']); // Junta as cores em uma string separada por vírgula

        try {
            $stmt = $conn->prepare("INSERT INTO modelos (modelo, fabricante, cor, ano, preco) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $modelo, $fabricante, $cores, $ano, $preco);

            if ($stmt->execute()) {
                $mensagem = "Modelo cadastrado com sucesso!";
                $mensagem_tipo = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar modelo.";
                $mensagem_tipo = "erro";
            }

            $stmt->close();
        } catch (Exception $e) {
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
            $mensagem_tipo = "erro";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Modelo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css">
    <link rel="icon" href="../img/logoofcbmw.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask-plugin/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <a href="../perfil.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a> 
        <h2>Cadastrar Modelo</h2>
        
        <form action="cadastrar_modelos.php" method="post">
            <!-- Modelo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" required>
                    <img src="../img/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Fabricante -->
            <div class="input-group">
                <label>Fabricante</label>
                <div class="input-wrapper">
                    <input type="text" name="fabricante" value="BMW" readonly>
                    <img src="../img/fabricante.png" alt="Ícone fabricante">
                </div>
            </div>

            <!-- Ano -->
            <div class="input-group">
                <label>Ano</label>
                <div class="input-wrapper">
                    <input type="text" name="ano" required maxlength="4" id="ano">
                    <img src="../img/ano.png" alt="Ícone ano">
                </div>
            </div>

            <!-- Preço -->
            <div class="input-group">
                <label>Preço</label>
                <div class="input-wrapper">
                    <input type="text" name="preco" required id="preco">
                    <img src="../img/preco.png" alt="Ícone preço">
                </div>
            </div>

            <!-- Cores Disponíveis -->
            <div class="input-group">
                <label>Cores Disponíveis</label>
                <div class="checkbox-group">
                    <?php
                    $cores_disponiveis = ["Preto", "Branco", "Azul", "Prata", "Verde", "Vermelho"];
                    foreach ($cores_disponiveis as $cor) {
                        echo '<label class="checkbox-field">
                                <input type="checkbox" name="cor[]" value="'.$cor.'">
                                <div class="checkmark"></div> 
                              </label>';
                    }
                    ?>
                </div>
            </div>

            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Cadastrar Modelo
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