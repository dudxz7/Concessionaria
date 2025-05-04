<?php
include('conexao.php');
session_start();

if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$cargo_usuario = $_SESSION['usuarioCargo'];
if (!in_array($cargo_usuario, ['Gerente', 'Admin'])) {
    echo "<h2>Acesso Negado</h2><p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

$query = "SELECT id, modelo, preco FROM modelos";
$result = $conn->query($query);

// Recupera os modelos que já têm promoções ativas
$promoted = [];
$promo_names = [];
$qr = $conn->query("
    SELECT DISTINCT p.modelo_id, m.modelo
      FROM promocoes p
      JOIN modelos m ON p.modelo_id=m.id
      WHERE p.data_limite > NOW()
");
while ($r = $qr->fetch_assoc()) {
    $promoted[] = $r['modelo_id'];
    $promo_names[$r['modelo_id']] = $r['modelo'];
}

$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $desconto = floatval($_POST['desconto']);
    $data = $_POST['data_limite_data'];
    $hora = $_POST['data_limite_hora'];
    $data_limite = "$data $hora:00";
    $modelo_ids = $_POST['modelo_id']; // array

    $erros = [];
    $duplicados = []; // Armazenar os modelos duplicados para mensagem única

    foreach ($modelo_ids as $modelo_id) {
        if ($modelo_id === 'all') continue;

        // Verifica se o modelo já possui promoção ativa
        if (in_array($modelo_id, $promoted)) {
            $duplicados[] = $promo_names[$modelo_id];
            continue;
        }

        // Recupera o preço do modelo
        $stmt = $conn->prepare("SELECT preco FROM modelos WHERE id = ?");
        $stmt->bind_param("i", $modelo_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) {
            $erros[] = "Modelo com ID $modelo_id não encontrado.";
            continue;
        }
        $preco_original = floatval($row['preco']);
        $preco_com_desconto = $preco_original - ($preco_original * ($desconto / 100));
        
        // Insere a promoção
        $insert = $conn->prepare("
            INSERT INTO promocoes (modelo_id, desconto, preco_com_desconto, data_limite)
            VALUES (?, ?, ?, ?)
        ");
        $insert->bind_param("idss", $modelo_id, $desconto, $preco_com_desconto, $data_limite);
        if (!$insert->execute()) {
            $erros[] = "Erro ao cadastrar promoção para modelo ID $modelo_id.";
        }
    }

    // Verifica se existem duplicações e exibe a mensagem
    if (!empty($duplicados)) {
        if (count($duplicados) === 1) {
            $mensagem = "Já existe promoção para o modelo {$duplicados[0]}.";
        } else {
            $listagem = implode(', ', $duplicados);
            $mensagem = "Já existe promoção para os modelos: {$listagem}.";
        }
        $mensagem_tipo = "error";
    } elseif (empty($erros)) {
        header("Location: consultar_promocoes.php");
        exit;
    } else {
        $mensagem = implode("<br>", $erros);
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
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
    input[type="date"],
    input[type="time"] {
        padding: 10px;
        padding-left: 20px;
        width: 100%;
        max-width: 600px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 20px;
        font-size: 16px;
    }

    .select2-container .select2-selection--multiple {
        border-radius: 20px;
        padding: 10px;
        min-height: 48px;
        font-size: 16px;
        background-color: #f2f2f2;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #2f4eda;
        border: none;
        color: white;
        font-weight: 500;
        margin: 4px 6px 4px 0;
        border-radius: 30px;
        transition: background 0.3s ease;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 6px;
        font-weight: bold;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ff4d4d;
        background-color: black;
    }

    .select2-results__option {
        background-color: #ffffff;
        color: #666666;
    }

    .select2-results__option--selected {
        background-color: #8b8b8b !important;
        color: white;
    }

    .select2-results__option--selectable:hover {
        background-color: #f0f0f0;
        color: #000000;
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
                <label for="modelo_id">Modelos</label>
                <div class="input-wrapper">
                    <select name="modelo_id[]" id="modelo_id" multiple class="select2" required>
                        <option value="all">Selecionar todos os modelos</option>
                        <?php
                        $result->data_seek(0);
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <option value="<?= $row['id'] ?>" data-preco="<?= $row['preco'] ?>">
                            <?= htmlspecialchars($row['modelo']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <img src="../img/carro.png" alt="Ícone modelo">
                </div>
            </div>
            <div class="input-group">
                <label for="desconto">Desconto (%)</label>
                <div class="input-wrapper">
                    <input type="number" placeholder="digite a porcentagem" name="desconto" id="desconto" min="0" max="100" step="1" required>
                    <img src="../img/preco.png" alt="Ícone desconto">
                </div>
            </div>

            <div class="input-group">
                <label for="data_limite_data">Data Limite</label>
                <div class="input-wrapper">
                    <input type="date" name="data_limite_data" id="data_limite_data" required>
                </div>
            </div>
            <div class="input-group">
                <label for="data_limite_hora">Hora Limite</label>
                <div class="input-wrapper">
                    <input type="time" name="data_limite_hora" id="data_limite_hora" required>
                </div>
            </div>

            <div id="mensagem-desconto" style="margin:15px 0; font-weight:bold; font-size:18px; color: #2f4eda;">
                <?php if ($mensagem): ?>
                <span class="<?= $mensagem_tipo; ?>"><?= $mensagem; ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone check"> Salvar Promoção
            </button>
        </form>
    </div>

    <script src="../js/cadastrar_promo.js"></script>
</body>
</html>
