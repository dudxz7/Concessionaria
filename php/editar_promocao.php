<?php
include('conexao.php');
session_start();

if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$cargo_usuario = $_SESSION['usuarioCargo'];
if (!in_array($cargo_usuario, ['Gerente', 'Admin'])) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

if (isset($_GET['id'])) {
    $promo_id = $_GET['id'];

    $query = "SELECT * FROM promocoes WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $promo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $promo = $result->fetch_assoc();
        $modelo_id = $promo['modelo_id'];
        $desconto = $promo['desconto'];
        $preco_com_desconto = $promo['preco_com_desconto'];
        $data_limite = explode(" ", $promo['data_limite'])[0];
        $hora_limite = explode(" ", $promo['data_limite'])[1] ?? '00:00';

        // Buscar o preço original do modelo
        $query_preco = "SELECT preco FROM modelos WHERE id = ?";
        $stmt_preco = $conn->prepare($query_preco);
        $stmt_preco->bind_param("i", $modelo_id);
        $stmt_preco->execute();
        $result_preco = $stmt_preco->get_result();
        $preco_original = $result_preco->fetch_assoc()['preco'] ?? 0;
    } else {
        echo "Promoção não encontrada!";
        exit;
    }
} else {
    echo "ID da promoção não fornecido!";
    exit;
}

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo_id = $_POST['modelo_id'];
    $desconto = $_POST['desconto'];
    $data_limite = $_POST['data_limite'];
    $hora_limite = $_POST['hora_limite'] ?? '00:00';
    $preco_original = floatval($_POST['preco_original']);

    $data_hora_limite = $data_limite . ' ' . $hora_limite;
    $preco_com_desconto = $preco_original - ($preco_original * ($desconto / 100));

    $update = $conn->prepare("UPDATE promocoes SET modelo_id = ?, desconto = ?, preco_com_desconto = ?, data_limite = ? WHERE id = ?");
    $update->bind_param("idssi", $modelo_id, $desconto, $preco_com_desconto, $data_hora_limite, $promo_id);

    if ($update->execute()) {
        header("Location: consultar_promocoes.php");
        exit;
    } else {
        $mensagem = "Erro ao atualizar a promoção!";
        $mensagem_tipo = "error";
    }
}

$query_modelos = "SELECT id, modelo, preco FROM modelos";
$modelos_result = $conn->query($query_modelos);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Promoção</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        input[type="date"], input[type="time"] {
            padding: 10px;
            padding-left: 20px;
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
        }

        .valor-final {
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="consultar_promocoes.php" class="back-button">
        <img src="../img/seta-esquerda24.png" alt="Voltar">
    </a>
    <h2>Editar Promoção</h2>

    <form action="editar_promocao.php?id=<?php echo $promo_id; ?>" method="post" id="formPromocao">
        <div class="input-group">
            <label for="modelo_id">Modelo</label>
            <div class="input-wrapper">
                <select name="modelo_id" id="modelo_id" required>
                    <option value="" disabled>Selecione o modelo</option>
                    <?php while ($row = $modelos_result->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"
                                <?php echo ($row['id'] == $modelo_id) ? 'selected' : ''; ?>
                                data-preco="<?php echo $row['preco']; ?>">
                            <?php echo htmlspecialchars($row['modelo']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <img src="../img/veiculos/carro.png" alt="Ícone modelo">
            </div>
        </div>

        <div class="input-group">
            <label for="desconto">Desconto (%)</label>
            <div class="input-wrapper">
                <input type="number" name="desconto" id="desconto" min="0" max="100" step="0.01"
                       value="<?php echo $desconto; ?>" required>
                <img src="../img/veiculos/preco.png" alt="Ícone desconto">
            </div>
        </div>

        <div class="input-group">
            <label for="data_limite">Data Limite</label>
            <div class="input-wrapper">
                <input type="date" name="data_limite" id="data_limite" value="<?php echo $data_limite; ?>" required>
            </div>
        </div>

        <div class="input-group">
            <label for="hora_limite">Hora Limite</label>
            <div class="input-wrapper">
                <input type="time" name="hora_limite" id="hora_limite" value="<?php echo $hora_limite; ?>" required>
            </div>
        </div>

        <div class="valor-final" id="valor-final">
            Calculando...
        </div>

        <input type="hidden" name="preco_original" value="<?php echo $preco_original; ?>" id="preco_original">

        <?php if (!empty($mensagem)): ?>
            <div class="<?php echo $mensagem_tipo; ?>" style="display:block;">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn">
            <img src="../img/registro/verifica.png" alt="Ícone de check">
            Atualizar Promoção
        </button>
    </form>
</div>

<script src="../js/atualizar_promo.js"></script>
</body>
</html>
