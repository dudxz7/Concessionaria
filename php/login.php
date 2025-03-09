<?php
// Iniciar a sessão
session_start();

// Conectar com o banco de dados
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
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Validar o e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "E-mail inválido!";
        exit;
    }

    // Verificar se os campos não estão vazios
    if (empty($email) || empty($senha)) {
        echo "Por favor, preencha todos os campos!";
        exit;
    }

    // Preparar e executar a consulta SQL para verificar o usuário
    $sql = "SELECT id, nome_completo, email, senha, admin, estado FROM clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Verificar se o usuário foi encontrado
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nome_completo, $email_db, $senha_db, $admin, $estado);
        $stmt->fetch();

        // Verificar a senha
        if (password_verify($senha, $senha_db)) {
            // Regenerar o ID da sessão para segurança
            session_regenerate_id(true);

            // Login bem-sucedido
            $_SESSION['usuarioLogado'] = true;
            $_SESSION['usuarioId'] = $id;
            $_SESSION['usuarioNome'] = $nome_completo;
            $_SESSION['usuarioEmail'] = $email_db;
            $_SESSION['usuarioAdmin'] = $admin;
            $_SESSION['usuarioEstado'] = $estado;  // Adicionando estado na sessão

            // Redirecionamento para o painel de administração, se for admin
            if ($admin == 1) {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: ../index.php");  // Aqui redireciona para o index.php
                exit;
            }
        } else {
            // Senha incorreta
            echo "E-mail ou senha incorretos!";
        }
    } else {
        // Usuário não encontrado
        echo "E-mail ou senha incorretos!";
    }

    // Fechar a consulta
    $stmt->close();
}

// Fechar a conexão
$conn->close();
?>
