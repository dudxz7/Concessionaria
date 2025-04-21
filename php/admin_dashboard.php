<?php
session_start();

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioAdmin'] !== 1) {
    // Redireciona para o index ou login se não for admin
    header("Location: ../index.php");
    exit();
}

// Recupera dados da sessão
$nome_completo = $_SESSION['nome_completo'] ?? 'Administrador';
$email = $_SESSION['email'] ?? 'admin@gmail.com';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" href="../img/admin_colored.png">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div>
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="content">
            <a href="../index.php" class="back-button">
                <img src="../img/seta-esquerdabranca.png" alt="Voltar">
            </a>
            <h2>Área Administrativa</h2>
            <p id="descricao">Escolha uma das opções abaixo para gerenciar o sistema.</p>
        </div>
    </div>
</body>
</html>
