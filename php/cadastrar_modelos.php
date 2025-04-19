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

// Verificar o cargo do usuário
$usuarioId = $_SESSION['usuarioId'] ?? null;

if ($usuarioId) {
    $sqlCargo = "SELECT cargo FROM clientes WHERE id = ?";
    $stmtCargo = $conn->prepare($sqlCargo);
    $stmtCargo->bind_param("i", $usuarioId);
    $stmtCargo->execute();
    $resultadoCargo = $stmtCargo->get_result();
    $dadosCargo = $resultadoCargo->fetch_assoc();

    if (!$dadosCargo || ($dadosCargo['cargo'] !== 'Admin' && $dadosCargo['cargo'] !== 'Gerente')) {
        echo "<h2>Acesso Negado</h2>";
        echo "<p>Você não tem permissão para acessar esta página.</p>";
        exit;
    }
    $stmtCargo->close();
} else {
    echo "Usuário não identificado.";
    exit;
}

// Lógica de cadastro
$mensagem = '';
$mensagem_tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = trim($_POST['modelo']);
    $fabricante = trim($_POST['fabricante']);
    $ano = intval($_POST['ano']);
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']); // Converte para decimal

    if (!isset($_POST['cor']) || empty($_POST['cor'])) {
        $mensagem = "Selecione pelo menos uma cor.";
        $mensagem_tipo = "erro";
    } else {
        $cores = implode(',', $_POST['cor']);

        try {
            // Verifica se já existe um modelo com o mesmo nome
            $check_stmt = $conn->prepare("SELECT id FROM modelos WHERE modelo = ?");
            $check_stmt->bind_param("s", $modelo);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $mensagem = "Já existe um modelo com esse nome.";
                $mensagem_tipo = "erro";
                $check_stmt->close();
            } else {
                $check_stmt->close();

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
            }
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
    <style>
    input#input-fabricante[readonly], input#input-fabricante:disabled {
        pointer-events: none;
        background-color: #f2f2f2;
        color: gray;
    }
    .input-group {
        cursor: not-allowed;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
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
                    <input type="text" name="fabricante" id="input-fabricante" value="BMW" readonly>
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
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>"
                <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Cadastrar Modelo
            </button>
        </form>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const precoInput = document.getElementById("preco");
        const anoInput = document.getElementById("ano");

        anoInput.addEventListener("input", function() {
            this.value = this.value.replace(/\D/g, "");
        });

        precoInput.addEventListener("input", function() {
            let valor = precoInput.value.replace(/\D/g, "");
            valor = (parseInt(valor, 10) / 100).toFixed(2);
            precoInput.value = valor.replace(".", ",").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        });
    });
    </script>
</body>

</html>