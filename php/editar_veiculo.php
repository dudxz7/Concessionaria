<?php
include('conexao.php');

$veiculo = null;
$mensagem = '';
$mensagem_tipo = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "
        SELECT veiculos.id AS veiculo_id, veiculos.modelo_id, modelos.modelo
        FROM veiculos
        JOIN modelos ON veiculos.modelo_id = modelos.id
        WHERE veiculos.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $veiculo = $result->fetch_assoc();

    if (!$veiculo) {
        $mensagem = "Veículo não encontrado!";
        $mensagem_tipo = "error";
    }
} else {
    $mensagem = "ID do veículo não especificado!";
    $mensagem_tipo = "error";
}

// Busca os modelos disponíveis
$modelos_result = $conn->query("SELECT id, modelo FROM modelos");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $veiculo) {
    $novo_modelo_id = $_POST['modelo_id'];

    $update_query = "UPDATE veiculos SET modelo_id = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $novo_modelo_id, $id);

    if ($stmt->execute()) {
        $mensagem = "Modelo do veículo atualizado com sucesso!";
        $mensagem_tipo = "success";
        $veiculo['modelo_id'] = $novo_modelo_id;
    } else {
        $mensagem = "Erro ao atualizar o modelo!";
        $mensagem_tipo = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Veículo</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <a href="consultar_veiculos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Editar Veículo</h2>

        <?php if ($veiculo): ?>
        <form action="editar_veiculo.php?id=<?php echo $id; ?>" method="post">
            <div class="input-group">
                <label for="modelo_id">Modelo</label>
                <div class="input-wrapper">
                    <select name="modelo_id" required>
                        <?php if ($modelos_result && $modelos_result->num_rows > 0): ?>
                            <?php while ($modelo = $modelos_result->fetch_assoc()) { ?>
                                <option value="<?php echo $modelo['id']; ?>" <?php if ($modelo['id'] == $veiculo['modelo_id']) echo 'selected'; ?>>
                                    <?php echo $modelo['modelo']; ?>
                                </option>
                            <?php } ?>
                        <?php else: ?>
                            <option disabled>Nenhum modelo encontrado</option>
                        <?php endif; ?>
                    </select>
                    <img src="../img/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <?php if (!empty($mensagem)): ?>
                <div class="<?php echo $mensagem_tipo; ?>" style="display:block;">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Salvar Alterações
            </button>
        </form>
        <?php else: ?>
            <div class="<?php echo $mensagem_tipo; ?>" style="display:block;">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
