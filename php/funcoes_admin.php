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
    <link rel="icon" href="../img/logos/admin_colored.png">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div>
            <?php include 'sidebar.php'; ?>
        </div>

        <div class="content">
            <div class="section">
                <h2>Clientes</h2>
                <div class="buttons">
                    <div class="button-item"><a href="../registro.html">Cadastrar Cliente</a></div>
                    <div class="button-item"><a href="consultar_clientes.php">Consultar Cliente</a></div>
                    <div class="button-item"><a href="atualizar_senha.php">Redefinir senha</a></div>
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
                    <div class="button-item"><a href="cadastro_admin.php">Cadastrar Funcionário</a></div>
                    <div class="button-item"><a href="consultar_func_gerente.php">Consultar Funcionários</a></div>
                </div>
            </div>

            <div class="section">
                <h2>Relatórios</h2>
                <div class="buttons">
                    <div class="button-item"><a href="venda_manual.php">Fechar Venda</a></div>
                    <div class="button-item"><a href="gerar_tipo_relatorio.php">Gerar Relatório</a></div>
                    <div class="button-item"><a href="comissao_funcionario.php">Comissão por Funcionário</a></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
