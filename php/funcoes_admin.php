<?php
session_start();

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioAdmin'] !== 1) {
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
    <title>Funções Admin</title>
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
                    <img src="../img/casa.png" alt="Dashboard">
                    <span>Dashboard</span>
                </div>
                <div class="icon-item" onclick="window.location.href='cadastro_admin.php'">
                    <img src="../img/novo-usuario.png" alt="Cadastrar">
                    <span>Cadastrar</span>
                </div>
                <div class="icon-item" onclick="window.location.href='funcoes_admin.php'">
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
            <a href="admin_dashboard.php" class="back-button">
                <img src="../img/seta-esquerdabranca.png" alt="Voltar">
            </a>

            <div class="section">
                <h2>Clientes</h2>
                <div class="buttons">
                    <div class="button-item"><a href="cadastrar_clientes.php">Cadastrar Cliente</a></div>
                    <div class="button-item"><a href="consultar_clientes.php">Consultar Cliente</a></div>
                    <div class="button-item"><a href="alterar_clientes.php">Alterar Cliente</a></div>
                </div>
            </div>

            <div class="section">
                <h2>Veículos</h2>
                <div class="buttons">
                    <div class="button-item"><a href="cadastrar_modelos.php">Cadastrar Modelo</a></div>
                    <div class="button-item"><a href="consultar_modelos.php">Consultar Modelos</a></div>
                    <div class="button-item"><a href="cadastrar_veiculo.php">Cadastrar Veículo</a></div>
                    <div class="button-item"><a href="consultar_veiculos.php">Consultar Veículos</a></div>
                </div>
            </div>

            <div class="section">
                <h2>Promoções</h2>
                <div class="buttons">
                    <div class="button-item"><a href="cadastrar_promocao.php">Cadastrar Promoção</a></div>
                    <div class="button-item"><a href="consultar_promocao.php">Consultar Promoções</a></div>
                </div>
            </div>

            <div class="section">
                <h2>Funcionários</h2>
                <div class="buttons">
                    <div class="button-item"><a href="cadastrar_funcionario.php">Cadastrar Funcionário</a></div>
                    <div class="button-item"><a href="consultar_funcionarios.php">Consultar Funcionários</a></div>
                </div>
            </div>

            <div class="section">
                <h2>Relatórios</h2>
                <div class="buttons">
                    <div class="button-item"><a href="fechar_venda.php">Fechar Venda</a></div>
                    <div class="button-item"><a href="gerar_relatorio.php">Gerar Relatório</a></div>
                    <div class="button-item"><a href="comissao_funcionario.php">Comissão por Funcionário</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
