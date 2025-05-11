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
    $descricao = trim($_POST['descricao']);

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

                // Inserir no modelos
                $stmt = $conn->prepare("INSERT INTO modelos (modelo, fabricante, cor, ano, preco) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $modelo, $fabricante, $cores, $ano, $preco);

                if ($stmt->execute()) {
                    $modelo_id = $conn->insert_id; // ID do modelo cadastrado

                    // Upload da imagem
                    $imagem_nome = null;
                    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                        $imagem_tmp = $_FILES['imagem']['tmp_name'];
                        $imagem_nome = basename($_FILES['imagem']['name']);
                        $caminho_destino = '../img/modelos/' . $imagem_nome;

                        if (!is_dir('../img/modelos/')) {
                            mkdir('../img/modelos/', 0777, true);
                        }

                        if (!move_uploaded_file($imagem_tmp, $caminho_destino)) {
                            $mensagem = "Erro ao fazer upload da imagem.";
                            $mensagem_tipo = "erro";
                        }
                    } else {
                        $mensagem = "Nenhuma imagem enviada.";
                        $mensagem_tipo = "erro";
                    }

                    // Inserir na tabela detalhes_modelos
                    $stmtDetalhes = $conn->prepare("INSERT INTO detalhes_modelos (modelo_id, descricao, imagem) VALUES (?, ?, ?)");
                    $stmtDetalhes->bind_param("iss", $modelo_id, $descricao, $imagem_nome);
                    $stmtDetalhes->execute();
                    $stmtDetalhes->close();

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
    <link rel="icon" href="../img/logos/logoofcbmw.png">
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

    .drop-zone {
        border: 2px dashed rgb(0, 0, 0);
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        position: relative;
        transition: border-color 0.3s ease;
        background-color: #f2f2f2;
    }

    .drop-zone:hover {
        border-color: #2f4eda;
    }

    #drop-zone input[type="file"] {
        display: none;
    }

    #drop-text {
        color: #555;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .browse-btn {
        color: #0071c5;
        text-decoration: underline;
        cursor: pointer;
    }

    .upload-icon {
        width: 48px;
        height: 48px;
        margin-bottom: 10px;
    }

    #preview {
        max-width: 100%;
        max-height: 100px;
        border-radius: 6px;
        margin-top: 0px;
        display: none;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    </style>
</head>
<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Cadastrar Modelo</h2>

        <form action="cadastrar_modelos.php" method="post" enctype="multipart/form-data">
            <!-- Modelo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" required>
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Fabricante -->
            <div class="input-group">
                <label>Fabricante</label>
                <div class="input-wrapper">
                    <input type="text" name="fabricante" id="input-fabricante" value="BMW" readonly>
                    <img src="../img/veiculos/fabricante.png" alt="Ícone fabricante">
                </div>
            </div>

            <!-- Ano -->
            <div class="input-group">
                <label>Ano</label>
                <div class="input-wrapper">
                    <input type="text" name="ano" required maxlength="4" id="ano">
                    <img src="../img/registro/ano.png" alt="Ícone ano">
                </div>
            </div>

            <!-- Preço -->
            <div class="input-group">
                <label>Preço</label>
                <div class="input-wrapper">
                    <input type="text" name="preco" required id="preco">
                    <img src="../img/veiculos/preco.png" alt="Ícone preço">
                </div>
            </div>

            <!-- Descrição -->
            <div class="input-group">
                <label>Descrição</label>
                <div class="input-wrapper">
                    <input type="text" name="descricao" maxlength="62" required>
                    <img src="../img/veiculos/escrevendo.png" alt="Ícone descrição">
                </div>
            </div>

            <!-- Imagem com Drag and Drop -->
            <div class="input-group">
                <label>Imagem Principal</label>
                <div class="drop-zone" id="drop-zone">
                    <img src="../img/upload-na-nuvem.png" alt="Ícone Upload" class="upload-icon" id="upload-icon">
                    <p id="drop-text">Arraste uma imagem aqui ou <span class="browse-btn">selecione um arquivo</span>
                    </p>
                    <input type="file" name="imagem" accept="image/*" required id="file-input">
                    <img id="preview" src="#" alt="Pré-visualização" style="display: none;">
                    <p id="file-name" style="display: none; margin-top: 10px; color: #333;"></p>
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

            <!-- Mensagem -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>"
                <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem ?? ''; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Cadastrar Modelo
            </button>
        </form>
    </div>
    <script src="../js/drag-and-drop.js"></script>
    <script src="../js/esconder-icone.js"></script>
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

        // Preview da imagem
        const inputImagem = document.querySelector('input[name="imagem"]');
        const previewImg = document.getElementById('preview');

        inputImagem.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.style.display = "none";
                previewImg.src = "#";
            }
        });
    });
    </script>
</body>
</html>