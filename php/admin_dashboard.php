<?php
session_start();

// Verificar se o usuário está logado e se é administrador
if (!isset($_SESSION['usuarioId']) || $_SESSION['usuarioAdmin'] !== 1) {
    header("Location: ../login.html");
    exit();
}

include 'conexao.php';

// Função para exibir os usuários
function exibirUsuarios($conn) {
    $sql = "SELECT id, nome_completo, email, cpf, telefone, rg, cidade, estado, cnh, admin FROM clientes";
    $result = $conn->query($sql);

    // Verificar se ocorreu algum erro na consulta
    if (!$result) {
        die("Erro na consulta: " . $conn->error);
    }

    // Exibir a tabela de usuários
    if ($result->num_rows > 0) {
        echo "<table border='1' class='tabela-usuarios'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>RG</th>
                        <th>Cidade</th>
                        <th>Estado</th>
                        <th>CNH</th>
                        <th>Admin</th>
                    </tr>
                </thead>
                <tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nome_completo']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['cpf']}</td>
                    <td>{$row['telefone']}</td>
                    <td>{$row['rg']}</td>
                    <td>{$row['cidade']}</td>
                    <td>{$row['estado']}</td>
                    <td>{$row['cnh']}</td>
                    <td>" . ($row['admin'] == 1 ? 'Sim' : 'Não') . "</td>
                </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "Nenhum usuário encontrado.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../img/admin_colored.png">
</head>
<body>
    <h1>Painel de Administração</h1>
    <h2>Lista de Usuários</h2>

    <?php
    exibirUsuarios($conn);
    $conn->close();
    ?>

    <br>
    <a href="logout.php">Sair</a>
</body>
</html>
