<?php
// Conexão com o banco de dados
$host = "localhost"; // Seu servidor MySQL
$user = "root"; // Usuário do MySQL
$pass = ""; // Senha do MySQL
$db = "sistema_bmw"; // Nome do banco de dados

$conn = new mysqli($host, $user, $pass, $db);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $telefone = $_POST['telefone'];
    $cnh = $_POST['cnh'];
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirma_senha) {
        echo "As senhas não coincidem!";
        exit;
    }

    // Verificar se o e-mail já existe
    $sql = "SELECT * FROM clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Erro: E-mail já cadastrado. Tente com outro e-mail.";
        exit;
    }

    // Verificar se o CPF já existe
    $sql = "SELECT * FROM clientes WHERE cpf = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Erro: CPF já cadastrado. Tente novamente com um CPF diferente.";
        exit;
    }

    // Criptografar a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Preparar e executar a consulta SQL para inserir os dados
    $sql = "INSERT INTO clientes (nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, senha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $nome, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $senha_hash);

    // Verificar se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        echo "Conta criada com sucesso!";
        header("Location: login.html"); // Redireciona para o login.html
        exit();
    } else {
        if ($conn->errno == 1062) { // Código de erro para violação de unicidade
            echo "Erro: CPF, RG, Telefone ou CNH já existem. Tente novamente com dados diferentes.";
        } else {
            echo "Erro ao criar a conta: " . $conn->error;
        }
    }

    // Fechar a conexão
    $stmt->close();
}

$conn->close();
?>
