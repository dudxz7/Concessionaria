<?php
session_start();

if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

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

// Inicializa variáveis do formulário
$modelo = "";
$fabricante = "BMW";
$ano = "";
$preco = "";
$descricao = "";
$coresSelecionadas = [];
$mensagem = '';
$mensagem_tipo = '';

// Pega o ID do modelo via GET (obrigatório)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID do modelo inválido.";
    exit;
}
$modeloId = intval($_GET['id']);

// Ao carregar a página, busca dados para preencher formulário
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT m.modelo, m.fabricante, m.cor, m.ano, m.preco, d.descricao 
            FROM modelos m
            LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
            WHERE m.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $modeloId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows === 1) {
        $dados = $resultado->fetch_assoc();
        $modelo = $dados['modelo'];
        $fabricante = $dados['fabricante'];
        $ano = $dados['ano'];
        // Ajusta o preço para formato brasileiro com vírgula
        $preco = number_format($dados['preco'], 2, ',', '.');
        $descricao = $dados['descricao'] ?? "";
        $coresSelecionadas = explode(',', $dados['cor']);
    } else {
        echo "Modelo não encontrado.";
        exit;
    }
    $stmt->close();
}

// Ao enviar o formulário, atualiza os dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = trim($_POST['modelo']);
    $fabricante = trim($_POST['fabricante']);
    $ano = intval($_POST['ano']);
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']); // de R$1.234,56 para 1234.56
    $descricao = trim($_POST['descricao']);

    if (!isset($_POST['cor']) || empty($_POST['cor'])) {
        $mensagem = "Selecione pelo menos uma cor.";
        $mensagem_tipo = "erro";
    } else {
        $coresSelecionadas = $_POST['cor'];
        $cores = implode(',', $coresSelecionadas);

        try {
            // Verifica se existe outro modelo com mesmo nome (excluindo este modelo)
            $check_stmt = $conn->prepare("SELECT id FROM modelos WHERE modelo = ? AND id <> ?");
            $check_stmt->bind_param("si", $modelo, $modeloId);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $mensagem = "Já existe um modelo com esse nome.";
                $mensagem_tipo = "erro";
                $check_stmt->close();
            } else {
                $check_stmt->close();

                // Atualiza dados na tabela modelos
                $stmt = $conn->prepare("UPDATE modelos SET modelo = ?, fabricante = ?, cor = ?, ano = ?, preco = ? WHERE id = ?");
                $stmt->bind_param("sssidi", $modelo, $fabricante, $cores, $ano, $preco, $modeloId);

                if ($stmt->execute()) {
                    $stmt->close();

                    // Atualiza descrição na tabela detalhes_modelos
                    // Se não existir, insere
                    $stmtDetExiste = $conn->prepare("SELECT modelo_id FROM detalhes_modelos WHERE modelo_id = ?");
                    $stmtDetExiste->bind_param("i", $modeloId);
                    $stmtDetExiste->execute();
                    $resultadoDet = $stmtDetExiste->get_result();

                    if ($resultadoDet->num_rows > 0) {
                        // Atualiza descrição
                        $stmtDetExiste->close();
                        $stmtDetalhes = $conn->prepare("UPDATE detalhes_modelos SET descricao = ? WHERE modelo_id = ?");
                        $stmtDetalhes->bind_param("si", $descricao, $modeloId);
                        $stmtDetalhes->execute();
                        $stmtDetalhes->close();
                    } else {
                        // Insere nova descrição
                        $stmtDetExiste->close();
                        $stmtDetalhes = $conn->prepare("INSERT INTO detalhes_modelos (modelo_id, descricao) VALUES (?, ?)");
                        $stmtDetalhes->bind_param("is", $modeloId, $descricao);
                        $stmtDetalhes->execute();
                        $stmtDetalhes->close();
                    }

                    $mensagem = "Modelo atualizado com sucesso!";
                    $mensagem_tipo = "sucesso";
                } else {
                    $mensagem = "Erro ao atualizar modelo.";
                    $mensagem_tipo = "erro";
                }
            }
        } catch (Exception $e) {
            $mensagem = "Erro no banco de dados: " . $e->getMessage();
            $mensagem_tipo = "erro";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Modelo</title>
    <link rel="stylesheet" href="../css/registro.css" />
    <link rel="stylesheet" href="../css/checkbox-cor-veiculos.css" />
    <link rel="icon" href="../img/logos/logoofcbmw.png" />
    <style>
    input#input-fabricante[readonly],
    input#input-fabricante:disabled {
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
            <img src="../img/seta-esquerda24.png" alt="Voltar" />
        </a>
        <h2>Editar Modelo</h2>

        <form action="editar_modelo.php?id=<?php echo $modeloId; ?>" method="post">
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" required value="<?php echo htmlspecialchars($modelo); ?>" />
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo" />
                </div>
            </div>

            <div class="input-group">
                <label>Fabricante</label>
                <div class="input-wrapper">
                    <input type="text" name="fabricante" id="input-fabricante"
                        value="<?php echo htmlspecialchars($fabricante); ?>" readonly />
                    <img src="../img/veiculos/fabricante.png" alt="Ícone fabricante" />
                </div>
            </div>

            <div class="input-group">
                <label>Ano</label>
                <div class="input-wrapper">
                    <input type="text" name="ano" required maxlength="4" id="ano"
                        value="<?php echo htmlspecialchars($ano); ?>" />
                    <img src="../img/registro/ano.png" alt="Ícone ano" />
                </div>
            </div>

            <div class="input-group">
                <label>Preço</label>
                <div class="input-wrapper">
                    <input type="text" name="preco" required id="preco"
                        value="<?php echo htmlspecialchars($preco); ?>" />
                    <img src="../img/veiculos/preco.png" alt="Ícone preço" />
                </div>
            </div>

            <div class="input-group">
                <label>Descrição</label>
                <div class="input-wrapper">
                    <input type="text" name="descricao" maxlength="62" required
                        value="<?php echo htmlspecialchars($descricao); ?>" />
                    <img src="../img/veiculos/escrevendo.png" alt="Ícone descrição" />
                </div>
            </div>

            <div class="input-group">
                <label>Cores Disponíveis</label>
                <div class="checkbox-group">
                    <?php
                    $cores_disponiveis = ["Preto", "Branco", "Azul", "Prata", "Verde", "Vermelho"];
                    foreach ($cores_disponiveis as $cor) {
                    echo '<label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="'.$cor.'" '.(in_array($cor, $coresSelecionadas) ? 'checked' : '').'>
                            <div class="checkmark"></div>
                        </label>';
}
?>
                </div>
            </div>

            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>"
                <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check" />
                Salvar Alterações
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