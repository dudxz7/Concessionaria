<?php
// Incluindo a conexão com o banco de dados
include('conexao.php');

// Verificando se o ID do veículo foi passado pela URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultando as informações do veículo e o estoque usando o ID
    $query = "
        SELECT v.*, e.quantidade 
        FROM veiculos v
        LEFT JOIN estoque e ON v.id = e.veiculo_id
        WHERE v.id = $id
    ";
    $result = mysqli_query($conn, $query);
    $veiculo = mysqli_fetch_assoc($result);

    // Verificando se a coluna 'cor' existe, se não, define como uma string vazia
    if (!isset($veiculo['cor'])) {
        $veiculo['cor'] = '';
    }
}

// Inicializa a variável de mensagem para evitar erro
$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $modelo = $_POST['modelo'];
    $ano = $_POST['ano'];
    $preco = $_POST['preco'];
    $numero_chassi = $_POST['numero_chassi'];
    $estoque = $_POST['estoque'];
    $cores = isset($_POST['cor']) ? implode(',', $_POST['cor']) : '';

    // Tratamento do preço
    $preco = str_replace([",", "."], ["", ""], $_POST['preco']);  // Remove a vírgula e ponto
    $preco = (float) $preco / 100; // Converte para o valor em decimal

    // Atualizando as informações do veículo na tabela 'veiculos'
    $update_query = "UPDATE veiculos SET modelo='$modelo', ano='$ano', preco='$preco', numero_chassi='$numero_chassi', cor='$cores' WHERE id = $id";

    // Atualizando o estoque na tabela 'estoque' (coluna 'quantidade')
    $update_estoque_query = "UPDATE estoque SET quantidade='$estoque' WHERE veiculo_id = $id";

    // Executando as atualizações
    if (mysqli_query($conn, $update_query) && mysqli_query($conn, $update_estoque_query)) {
        // Recarrega as informações atualizadas após o sucesso
        $veiculo['preco'] = $preco;
        $veiculo['quantidade'] = $estoque;
        $mensagem = "Veículo atualizado com sucesso!";
        $mensagem_tipo = "success";
    } else {
        $mensagem = "Erro ao atualizar o veículo!";
        $mensagem_tipo = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css">
    <link rel="icon" href="../img/logoofcbmw.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask-plugin/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <a href="consultar_veiculos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a> 
        <h2>Editar Veículo</h2>
        
        <form action="editar_veiculo.php?id=<?php echo $id; ?>" method="post">
            <!-- Modelo do veículo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" value="<?php echo $veiculo['modelo']; ?>" required>
                    <img src="../img/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Fabricante (fixo como BMW) -->
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
                    <input type="text" name="ano" value="<?php echo $veiculo['ano']; ?>" required maxlength="4" id="ano">
                    <img src="../img/ano.png" alt="Ícone ano">
                </div>
            </div>

            <!-- Preço -->
            <div class="input-group">
                <label>Preço</label>
                <div class="input-wrapper">
                    <input type="text" name="preco" value="<?php echo number_format($veiculo['preco'], 2, ',', '.'); ?>" required id="preco">
                    <img src="../img/preco.png" alt="Ícone preço">
                </div>
            </div>

            <!-- Número do Chassi -->
            <div class="input-group">
                <label>Número do Chassi</label>
                <div class="input-wrapper">
                    <input type="text" name="numero_chassi" value="<?php echo $veiculo['numero_chassi']; ?>" required maxlength="17" id="numero_chassi">
                    <img src="../img/chassi.png" alt="Ícone chassi">
                </div>
            </div>

            <!-- Quantidade em estoque -->
            <div class="input-group">
                <label>Quantidade em estoque</label>
                <div class="input-wrapper">
                    <input type="number" name="estoque" value="<?php echo $veiculo['quantidade']; ?>" required min="1" id="estoque">
                    <img src="../img/estoque.png" alt="Ícone estoque">
                </div>
            </div>

            <div class="input-group">
                <label>Cores Disponíveis</label>
                <div class="checkbox-group">
                    <?php 
                    // Verificando as cores e adicionando checked onde necessário
                    $cores_array = explode(',', $veiculo['cor']);
                    ?>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Preto', $cores_array) ? 'black' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Preto" <?php echo in_array('Preto', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Branco', $cores_array) ? 'white' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Branco" <?php echo in_array('Branco', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Azul', $cores_array) ? 'blue' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Azul" <?php echo in_array('Azul', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Prata', $cores_array) ? 'silver' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Prata" <?php echo in_array('Prata', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Verde', $cores_array) ? 'green' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Verde" <?php echo in_array('Verde', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                    <label class="checkbox-field" style="background-color: <?php echo in_array('Vermelho', $cores_array) ? 'red' : ''; ?>">
                        <input type="checkbox" name="cor[]" value="Vermelho" <?php echo in_array('Vermelho', $cores_array) ? 'checked' : ''; ?>>
                        <div class="checkmark"></div> 
                    </label>
                </div>
            </div>

            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Salvar Alterações
            </button>
            
        </form>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const precoInput = document.getElementById("preco");

        precoInput.addEventListener("input", function () {
             let valor = precoInput.value.replace(/\D/g, ""); // Remove tudo que não for número
             valor = (parseInt(valor, 10) / 100).toFixed(2); // Converte para decimal

             // Formata no padrão brasileiro (R$ 1.234,56)
            precoInput.value = valor.replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
    </script>
</body>
</html>
