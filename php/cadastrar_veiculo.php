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

            // Insere o estoque (1 por veículo individual)
            $stmt_estoque = $conn->prepare("INSERT INTO estoque (veiculo_id, quantidade) VALUES (?, 1)");
            $stmt_estoque->bind_param("i", $veiculo_id);
            if (!$stmt_estoque->execute()) {
                $sucesso = false;
                break;
            }
            $stmt_estoque->close();
        } else {
            $sucesso = false;
            break;
        }

        $stmt->close();
    }

    if ($sucesso) {
        $mensagem = "Todos os veículos foram cadastrados com sucesso!";
        $mensagem_tipo = "sucesso";
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
    <link rel="icon" href="../img/logoofcbmw.png">
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
                    <select name="modelo_id" required>
                        <option value="">Selecione um modelo</option>
                        <?php while ($modelo = $modelos_result->fetch_assoc()) { ?>
                            <option value="<?= $modelo['id'] ?>">
                                <?= $modelo['modelo'] ?> - <?= $modelo['ano'] ?>
                            </option>
                        <?php } ?>
                    </select>
                    <img src="../img/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Estoque -->
            <div class="input-group">
                <label>Quantidade em Estoque</label>
                <div class="input-wrapper">
                    <input type="number" name="estoque" required min="1">
                    <img src="../img/estoque.png" alt="Ícone estoque">
                </div>
            </div>

            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Cadastrar Veículo
            </button>
        </form>
    </div>
</body>
</html>
