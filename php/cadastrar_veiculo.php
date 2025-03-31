<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: login.php");
    exit;
}

// Conectar ao banco de dados
$host = "localhost";
$user = "root";  // Alterar para o seu usuário do banco de dados
$pass = "";      // Alterar para a sua senha do banco de dados
$db = "sistema_bmw";  // Nome do seu banco de dados

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado para cadastrar o veículo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitização dos dados de entrada
    $modelo = mysqli_real_escape_string($conn, $_POST['modelo']);
    $ano = mysqli_real_escape_string($conn, $_POST['ano']);
    
    // Tratamento do preço
    $preco = str_replace([",", "."], ["", ""], $_POST['preco']);  // Remove a vírgula e ponto
    $preco = (float) $preco / 100; // Converte para o valor em decimal
    
    $numero_chassi = mysqli_real_escape_string($conn, $_POST['numero_chassi']);
    $estoque = (int) $_POST['estoque'];
    $cores = isset($_POST['cor']) ? implode(", ", $_POST['cor']) : '';

    // Validar preço
    if (!is_numeric($preco)) {
        $mensagem = "O preço deve ser um valor numérico válido.";
        $mensagem_tipo = "erro";
    } else {
        // Inserir os dados do veículo na tabela 'veiculos'
        $sql_insert = "INSERT INTO veiculos (modelo, fabricante, cor, ano, preco, numero_chassi) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $fabricante = "BMW";  // Fixando fabricante como BMW
        $stmt_insert->bind_param("ssssss", $modelo, $fabricante, $cores, $ano, $preco, $numero_chassi);
        
        if ($stmt_insert->execute()) {
            // Obter o ID do veículo inserido
            $veiculo_id = $stmt_insert->insert_id;

            // Inserir o estoque do veículo na tabela 'estoque'
            $sql_insert_estoque = "INSERT INTO estoque (veiculo_id, quantidade) VALUES (?, ?)";
            $stmt_insert_estoque = $conn->prepare($sql_insert_estoque);
            $stmt_insert_estoque->bind_param("ii", $veiculo_id, $estoque);
            
            if ($stmt_insert_estoque->execute()) {
                $mensagem = "Veículo cadastrado com sucesso!";
                $mensagem_tipo = "sucesso";
            } else {
                $mensagem = "Erro ao cadastrar o estoque.";
                $mensagem_tipo = "erro";
            }
        } else {
            $mensagem = "Erro ao cadastrar o veículo.";
            $mensagem_tipo = "erro";
        }

        $stmt_insert->close();
        $stmt_insert_estoque->close();
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
    <style>
.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 69px;
    margin-top: 10px;
}

.checkbox-field {
    display: flex;
    align-items: center; /* Alinha verticalmente no centro */
    justify-content: center; /* Alinha horizontalmente no centro */
    cursor: pointer;
    position: relative; /* Necessário para o posicionamento do checkmark */
}

.checkbox-field input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    width: 20px;
    height: 20px;
    background-color: rgba(16, 16, 16, 0.5);
    border-radius: 4px;
    box-shadow: 0px 0px 0px 2px rgb(255, 84, 0);
    transition: all 0.2s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
}

.checkbox-field input:checked ~ .checkmark {
    background-color: rgba(245, 24, 24, 0.5);
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 0.45em; /* Ajuste fino para centralizar */
    top: 0.25em;  /* Ajuste fino para centralizar */
    width: 0.25em;
    height: 0.5em;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
    left: 50%; /* Centraliza horizontalmente */
    top: 50%;  /* Centraliza verticalmente */
    transform: translate(-50%, -50%) rotate(45deg); /* Ajusta o ícone no centro */
}

.checkbox-field input:checked ~ .checkmark:after {
    display: block;
}
    .checkbox-field:nth-child(1) .checkmark span {
        margin-left: 94px;
    }
    .checkbox-field:nth-child(2) .checkmark span {
        margin-left: 94px;
    }
    .checkbox-field:nth-child(3) .checkmark span {
        margin-left: 94px;
    }
    .checkbox-field:nth-child(4) .checkmark span {
        margin-left: 94px;
    }
    .checkbox-field:nth-child(5) .checkmark span {
        margin-left: 94px;
    }
    .checkbox-field:nth-child(6) .checkmark span {
        margin-left: 110px;
    }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask-plugin/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <a href="consultar_veiculos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a> 
        <h2>Cadastrar Veículo</h2>
        
        <form action="cadastrar_veiculo.php" method="post">
            <!-- Modelo do veículo -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" name="modelo" required>
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

            <!-- Número do Chassi -->
            <div class="input-group">
                <label>Número do Chassi</label>
                <div class="input-wrapper">
                    <input type="text" name="numero_chassi" required maxlength="17" id="numero_chassi">
                    <img src="../img/chassi.png" alt="Ícone chassi">
                </div>
            </div>

            <!-- Quantidade em estoque -->
            <div class="input-group">
                <label>Quantidade em estoque</label>
                <div class="input-wrapper">
                    <input type="number" name="estoque" required min="1">
                    <img src="../img/estoque.png" alt="Ícone estoque">
                </div>
            </div>

            <div class="input-group">
                <label>Cores Disponíveis</label>
                <div class="checkbox-group">
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Preto">
                        <div class="checkmark"> 
                            <span>Preto</span>
                        </div> 
                    </label>
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Branco">
                        <div class="checkmark">
                            <span>Branco</span>
                        </div> 
                    </label>
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Azul">
                        <div class="checkmark">
                            <span>Azul</span>
                        </div> 
                    </label>
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Prata">
                        <div class="checkmark">
                            <span>Prata</span>
                        </div> 
                    </label>
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Verde">
                        <div class="checkmark">
                            <span>Verde</span>
                        </div> 
                    </label>
                    <label class="checkbox-field">
                        <input type="checkbox" name="cor[]" value="Vermelho">
                        <div class="checkmark">
                            <span>Vermelho</span>
                        </div> 
                    </label>
                </div>
            </div>

            <!-- Mensagem de erro ou sucesso -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
                <?php echo $mensagem; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Cadastrar Veículo
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
