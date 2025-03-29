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

// Verificar se o ID do cliente foi passado via URL ou POST
if (isset($_GET['id'])) {
    $cliente_id = $_GET['id'];

    // Consultar os dados do cliente
    $sql = "SELECT nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, cargo, endereco, pis, senha FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $stmt->bind_result($nome_completo, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $cargo, $endereco, $pis, $senha_atual);

    // Recuperar os dados do cliente
    if ($stmt->fetch()) {
        // Aqui você já tem os dados do cliente nas variáveis
    } else {
        echo "Cliente não encontrado.";
    }

    $stmt->close();
} else {
    echo "ID do cliente não fornecido.";
}

// Verificar se o formulário foi enviado para editar os dados
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $telefone = $_POST['telefone'];
    $cnh = $_POST['cnh'];
    $senha = $_POST['senha'];  // Senha para edição, caso necessário
    $confirma_senha = $_POST['confirma_senha'];

    // Verificar se a senha foi confirmada
    if (!empty($senha) && $senha !== $confirma_senha) {
        $_SESSION['senha_error'] = "As senhas não coincidem."; // Definir erro de senha
    } else {
        // Se a senha estiver vazia, não vamos atualizar a senha no banco
        if (empty($senha)) {
            // Atualizar apenas os outros campos, sem mexer na senha
            $sql_update = "UPDATE clientes SET nome_completo = ?, email = ?, cpf = ?, rg = ?, cidade = ?, estado = ?, telefone = ?, cnh = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssssssi", $nome, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $cliente_id);
        } else {
            // Atualizar a senha junto com os outros campos
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);  // Gerar o hash da senha
            $sql_update = "UPDATE clientes SET nome_completo = ?, email = ?, cpf = ?, rg = ?, cidade = ?, estado = ?, telefone = ?, cnh = ?, senha = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssssssssi", $nome, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $senha_hash, $cliente_id);
        }

        if ($stmt_update->execute()) {
            $mensagem = "Dados atualizados com sucesso!";
            $mensagem_tipo = "sucesso";
        } else {
            $mensagem = "Erro ao atualizar os dados.";
            $mensagem_tipo = "erro";
        }

        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Dados Cliente</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <!-- Imagem da seta para voltar -->
        <a href="consultar_clientes.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a> 
        <h2>Editar Dados</h2>
        <form action="editar_cliente.php?id=<?php echo $cliente_id; ?>" method="post">
            <!-- Nome completo -->
            <div class="input-group">
                <label>Nome completo</label>
                <div class="input-wrapper">
                    <input type="text" name="nome" value="<?php echo $nome_completo; ?>" required>
                    <img src="../img/usersemfundo.png" alt="Ícone usuário">
                </div>
            </div>
            
            <!-- E-mail -->
            <div class="input-group">
                <label>E-mail</label>
                <div class="input-wrapper">
                    <input type="email" name="email" value="<?php echo $email; ?>" required>
                    <img src="../img/e-mail.png" alt="Ícone e-mail">
                </div>
            </div>
            
            <div class="row">
                <!-- CPF e RG -->
                <div class="input-group">
                    <label>CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="cpf" value="<?php echo $cpf; ?>" id="cpf" required>
                        <img src="../img/arquivo.png" alt="Ícone CPF">
                    </div>
                </div>
                <div class="input-group">
                    <label>RG</label>
                    <div class="input-wrapper">
                        <input type="text" name="rg" value="<?php echo $rg; ?>" id="rg" required>
                        <img src="../img/cartaozin.png" alt="Ícone RG">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Cidade e Estado -->
                <div class="input-group">
                    <label>Cidade</label>
                    <div class="input-wrapper">
                        <input type="text" name="cidade" value="<?php echo $cidade; ?>" id="cidade" required>
                        <img src="../img/construcao-da-cidade.png" alt="Ícone cidade">
                    </div>
                </div>
                <div class="input-group">
                    <label>Estado</label>
                    <div class="input-wrapper">
                        <input type="text" name="estado" value="<?php echo $estado; ?>" id="estado" required maxlength="2">
                        <img src="../img/lugar-colocar.png" alt="Ícone estado">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Telefone e CNH -->
                <div class="input-group">
                    <label>Telefone</label>
                    <div class="input-wrapper">
                        <input type="tel" name="telefone" value="<?php echo $telefone; ?>" id="telefone" required>
                        <img src="../img/telefone.png" alt="Ícone telefone">
                    </div>
                </div>
                <div class="input-group">
                    <label>CNH</label>
                    <div class="input-wrapper">
                        <input type="text" name="cnh" value="<?php echo $cnh; ?>" id="cnh">
                        <img src="../img/carteira-de-motorista.png" alt="Ícone CNH">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Senha e Confirmação de Senha -->
                <div class="input-group">
                    <label>Nova Senha</label>
                    <div class="input-wrapper">
                        <input type="password" name="senha" placeholder="******" id="senha">
                        <img src="../img/olhofechado.png" alt="Ícone senha" id="eyeIcon">
                    </div>
                </div>
                <div class="input-group">
                    <label>Confirme a senha</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirma_senha" placeholder="******" id="confirmaSenha">
                        <img src="../img/olhofechado.png" alt="Ícone confirmar senha" id="eyeIcon2">
                    </div>
                </div>
            </div>

            <!-- Mensagem de erro -->
            <div id="error-message" class="<?php echo !empty($mensagem) ? $mensagem_tipo : ''; ?>" <?php if (!empty($mensagem)) echo 'style="display:block;"'; ?>>
            <?php echo $mensagem; ?>
            </div>

            <button type="submit" class="btn">
                <img src="../img/verifica.png" alt="Ícone de check">
                Salvar alterações
            </button>
        </form>
    </div>

<script src="../js/validacoes-registro.js"></script>
<script>
// Referências aos elementos
const eyeIcon = document.getElementById("eyeIcon");
const senhaInput = document.getElementById("senha");
const confirmaSenhaInput = document.getElementById("confirmaSenha");
const eyeIcon2 = document.getElementById("eyeIcon2");
const errorMessage = document.getElementById("error-message");

// Função para alternar visibilidade da senha
function togglePasswordVisibility(inputElement, eyeElement) {
    if (inputElement.type === "password") {
        inputElement.type = "text";  // Torna a senha visível
        eyeElement.src = "../img/olhoaberto.png";  // Ícone de olho aberto
    } else {
        inputElement.type = "password";  // Torna a senha oculta
        eyeElement.src = "../img/olhofechado.png";  // Ícone de olho fechado
    }
}

// Função para verificar se as senhas coincidem 
function checkPasswordsMatch() {
    if (senhaInput.value !== confirmaSenhaInput.value) {
        errorMessage.textContent = "As senhas não coincidem.";
        errorMessage.classList.remove("sucesso");
        errorMessage.classList.add("erro");
        errorMessage.style.display = "block";
    } else {
        errorMessage.textContent = "";
        errorMessage.style.display = "none";
    }
}

// Adicionar evento de input para verificar a cada digitação
senhaInput.addEventListener("input", checkPasswordsMatch);
confirmaSenhaInput.addEventListener("input", checkPasswordsMatch);

// Evento para o primeiro ícone de olho (senha)
eyeIcon.addEventListener("click", function () {
    togglePasswordVisibility(senhaInput, eyeIcon);
    togglePasswordVisibility(confirmaSenhaInput, eyeIcon2);
});

// Evento para o segundo ícone de olho (confirmar senha)
eyeIcon2.addEventListener("click", function () {
    togglePasswordVisibility(confirmaSenhaInput, eyeIcon2);
    togglePasswordVisibility(senhaInput, eyeIcon);
});

</script>
</body>
</html>
