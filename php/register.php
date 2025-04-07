<?php
// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);

// Verifica conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Dados do formulário
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
    $cargo = $_POST['cargo'];

    // Campos extras (só existem se for funcionário ou gerente)
    $pis = !empty($_POST['pis']) ? $_POST['pis'] : null;
    $endereco = !empty($_POST['endereco']) ? $_POST['endereco'] : null;

    // Verifica se as senhas coincidem
    if ($senha !== $confirma_senha) {
        echo "As senhas não coincidem!";
        exit;
    }

    // Verifica se o e-mail já existe
    $sql = "SELECT * FROM clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Erro: E-mail já cadastrado.";
        exit;
    }

    // Verifica se o CPF já existe
    $sql = "SELECT * FROM clientes WHERE cpf = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "Erro: CPF já cadastrado.";
        exit;
    }

    // Criptografa a senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere no banco
    $sql = "INSERT INTO clientes (nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, senha, cargo, pis, endereco) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssss", $nome, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $senha_hash, $cargo, $pis, $endereco);

    if ($stmt->execute()) {
        echo "Conta criada com sucesso!";
        header("Location: ../index.php");
        exit();
    } else {
        if ($conn->errno == 1062) {
            echo "Erro: CPF, RG, Telefone ou CNH já existem.";
        } else {
            echo "Erro ao criar a conta: " . $conn->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>
