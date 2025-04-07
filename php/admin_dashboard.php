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
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="../videos/overlay_branca.mp4" type="video/mp4">
                Seu navegador não suporta vídeos.
            </video>
            <div class="profile-icon"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></div>
            <p><strong><?php echo $nome_completo; ?></strong></p>
            <p><?php echo $email; ?></p>

            <div class="icons">
                <div class="icon-item" onclick="window.location.href='admin_dashboard.php'">
                    <img src="../img/casa.png" alt="Minha Conta">
                    <span>Dashboard</span>
                </div>
                <div class="icon-item" onclick="window.location.href='cadastro_admin.php'">
                    <img src="../img/novo-usuario.png" alt="Cadastro do admin">
                    <span>Cadastrar</span>
                </div>
                <div class="icon-item" onclick="window.location.href='funcoes.php'">
                    <img src="../img/referencia.png" alt="Funções">
                    <span>Funções</span>
                </div>
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'">
                    <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                    <span>Esqueceu a Senha</span>
                </div>
                <div class="icon-item" onclick="window.location.href='logout.php'">
                    <img src="../img/sairr.png" alt="Sair">
                    <span>Sair</span>
                </div>
            </div>
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
