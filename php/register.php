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
    // O cargo deve ser o enviado pelo formulário (admin, gerente, funcionario, cliente)
    $cargo = isset($_POST['cargo']) ? $_POST['cargo'] : 'Cliente';

    // Campos extras (só existem se for funcionário ou gerente)
    $pis = isset($_POST['pis']) ? $_POST['pis'] : "";
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : "";

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

    // Após inserir em clientes, se for Funcionario, insere também em funcionarios
    if ($stmt->execute()) {
        // Se for funcionário, insere também na tabela funcionarios
        if ($cargo === 'Funcionario') {
            // Verifica se já existe PIS ou CPF na tabela funcionarios
            $sql_check_func = "SELECT id FROM funcionarios WHERE cpf = ? OR pis = ? OR email = ?";
            $stmt_check_func = $conn->prepare($sql_check_func);
            $stmt_check_func->bind_param("sss", $cpf, $pis, $email);
            $stmt_check_func->execute();
            $stmt_check_func->store_result();
            if ($stmt_check_func->num_rows === 0) {
                $sql_func = "INSERT INTO funcionarios (nome_completo, email, cpf, rg, telefone, pis, endereco, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_func = $conn->prepare($sql_func);
                $stmt_func->bind_param("sssssssss", $nome, $email, $cpf, $rg, $telefone, $pis, $endereco, $cidade, $estado);
                $stmt_func->execute();
                $stmt_func->close();
            }
            $stmt_check_func->close();
        }
        // Redirecionamento dinâmico por parâmetro
        $redir = $_POST['redir'] ?? $_GET['redir'] ?? null;
        if ($redir) {
            switch ($redir) {
                case '2':
                    header("Location: consultar_clientes.php");
                    exit();
                case '3':
                    header("Location: consultar_func_gerente.php");
                    exit();
                default:
                    header("Location: ../index.php");
                    exit();
            }
        } else {
            header("Location: ../login.html");
            exit();
        }
    } else {
        if ($conn->errno == 1062) {
            echo "Erro: CPF, RG, Telefone ou CNH já existem.";
        } else {
            echo "Erro ao criar a conta: " . $conn->error;
        }
    }

    $stmt->close();
}
// Se acessar via GET, redireciona para o formulário correto conforme redir
elseif (isset($_GET['redir'])) {
    switch ($_GET['redir']) {
        case '2':
            header("Location: cadastro_admin.php?redir=2"); // ou outro formulário de cadastro de cliente
            exit();
        case '3':
            header("Location: cadastro_admin.php?redir=3"); // ou outro formulário de cadastro de funcionário
            exit();
        default:
            header("Location: ../index.php");
            exit();
    }
} else {
    header("Location: ../login.html");
    exit();
}

$conn->close();
?>
