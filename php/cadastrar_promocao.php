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

// Buscar modelos (não veículos)
$query = "SELECT id, modelo, preco FROM modelos";
$result = $conn->query($query);

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo_id = $_POST['modelo_id'];
    $desconto = $_POST['desconto'];
    $data_limite = $_POST['data_limite'];
    $preco_original = $_POST['preco_original']; // Preço original enviado do front-end

    // Calculando o preço com o desconto
    $preco_original = isset($_POST['preco_original']) ? floatval($_POST['preco_original']) : 0;
    $preco_com_desconto = $preco_original - ($preco_original * ($desconto / 100));

    // Gravar no banco (mudamos para modelo_id)
    $insert = $conn->prepare("INSERT INTO promocoes (modelo_id, desconto, preco_com_desconto, data_limite) VALUES (?, ?, ?, ?)");
    $insert->bind_param("idss", $modelo_id, $desconto, $preco_com_desconto, $data_limite);

    if ($insert->execute()) {
        header("Location: consultar_promocoes.php");
        exit;
    } else {
        $mensagem = "Erro ao cadastrar a promoção!";
        $mensagem_tipo = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Promoção</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logoofcbmw.png">
    <style>
        input[type="date"] {
            padding: 10px;
            padding-left: 20px; /* move o texto mais pra direita */
            width: 100%;
            max-width: 600px; /* controla o tamanho máximo */
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="consultar_promocoes.php" class="back-button">
        <img src="../img/seta-esquerda24.png" alt="Voltar">
    </a>
    <h2>Cadastrar Promoção</h2>

    <form action="cadastrar_promocao.php" method="post" id="formPromocao">
        <div class="input-group">
            <label for="modelo_id">Modelo</label>
            <div class="input-wrapper">
                <select name="modelo_id" id="modelo_id" required>
                    <option value="" disabled selected>Selecione o modelo</option>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <option value="<?php echo $row['id']; ?>" data-preco="<?php echo $row['preco']; ?>">
                                <?php echo htmlspecialchars($row['modelo']); ?>
                            </option>
                        <?php } ?>
                    <?php else: ?>
                        <option disabled>Nenhum modelo encontrado</option>
                    <?php endif; ?>
                </select>
                <img src="../img/carro.png" alt="Ícone modelo">
            </div>
        </div>

        <div class="input-group">
            <label for="desconto">Desconto (%)</label>
            <div class="input-wrapper">
                <input type="number" name="desconto" id="desconto" min="0" max="100" step="1" required>
                <img src="../img/preco.png" alt="Ícone desconto">
            </div>
        </div>

        <div class="input-group">
            <label for="data_limite">Data Limite</label>
            <div class="input-wrapper">
                <input type="date" name="data_limite" id="data_limite" required>
            </div>
        </div>

        <div id="valor-final" style="margin-top: 15px; font-weight: bold; font-size: 18px;">
            Valor final: R$ 0,00
        </div>

        <!-- Preço original escondido (será enviado via POST) -->
        <input type="hidden" name="preco_original" id="preco_original">

        <?php if (!empty($mensagem)): ?>
            <div class="<?php echo $mensagem_tipo; ?>" style="display:block;">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn">
            <img src="../img/verifica.png" alt="Ícone de check">
            Salvar Promoção
        </button>
    </form>
</div>

<script src="../js/cadastrar_promo.js"></script>
</body>
</html>
