<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: login.php");
    exit;
}

// Conectar ao banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Definir a quantidade de clientes por página
$clientes_por_pagina = 10;

// Verificar a página atual
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $clientes_por_pagina;

// Filtragem por busca
$filtro = "";
$param = "%";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filtro = $_GET['search'];
    $sql = "SELECT id, nome_completo, email, telefone, registrado_em FROM clientes 
            WHERE nome_completo LIKE ? OR email LIKE ? 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "%$filtro%";
    $stmt->bind_param("ssii", $param, $param, $offset, $clientes_por_pagina);
    $stmt->execute();
    $result = $stmt->get_result();

    // Contar total de registros filtrados
    $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE nome_completo LIKE ? OR email LIKE ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("ss", $param, $param);
    $stmt_total->execute();
    $total_result = $stmt_total->get_result()->fetch_assoc();
    $total_clientes = $total_result['total'];
} else {
    // Consulta geral sem filtro
    $sql = "SELECT id, nome_completo, email, telefone, registrado_em FROM clientes LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $clientes_por_pagina);
    $stmt->execute();
    $result = $stmt->get_result();

    // Contar total de clientes
    $sql_total = "SELECT COUNT(*) as total FROM clientes";
    $total_clientes = $conn->query($sql_total)->fetch_assoc()['total'];
}

// Calcular o total de páginas
$total_paginas = ceil($total_clientes / $clientes_por_pagina);
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Clientes</title>
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="../videos/overlay_azul.mp4" type="video/mp4">
                Seu navegador não suporta vídeos.
            </video>
            <div class="profile-icon"><?php echo strtoupper(substr($_SESSION['usuarioNome'], 0, 1)); ?></div>
            <p><strong><?php echo htmlspecialchars($_SESSION['usuarioNome']); ?></strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuarioEmail']); ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='../perfil.php'">
                    <img src="../img/usersembarra.png" alt="Minha Conta">
                    <span>Minha conta</span>
                </div>
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'">
                    <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                    <span>Esqueceu a Senha</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_clientes.php'">
                    <img src="../img/lupa.png" alt="Consultar clientes">
                    <span>Consultar Clientes</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'">
                    <img src="../img/carro_de_frente.png" alt="Consultar Veículos">
                    <span>Consultar veículos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'">
                    <img src="../img/promocoes.png" alt="Consultar promoções">
                    <span>Consultar promoções</span>
                </div>
                <div class="icon-item" onclick="window.location.href='logout.php'">
                    <img src="../img/sairr.png" alt="Sair">
                    <span>Sair</span>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="content">
            <h2 class="btn-shine">Consulta de Clientes</h2>
            
            <a href="../registro.html" class="btn-novo-cliente">
                <img src="../img/adicionar-usuario.png" alt="Cadastrar Cliente" class="img-btn">
                Cadastrar Cliente Novo
            </a>

            <form method="GET" action="">
                <input type="text" name="search" class="input" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($filtro); ?>">
                <button type="submit">
                <img src="../img/lupa.png" alt="Buscar" class="icone-lupa">
                </button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Registrado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nome_completo']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['telefone']; ?></td>
                            <td><?php echo $row['registrado_em']; ?></td>
                            <td>
                                <a class="a-btn" href="editar_cliente.php?id=<?php echo $row['id']; ?>">
                                    <img src="../img/editar.png" alt="Editar" class="btn-editar">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <div class="paginacao">
                <!-- Exibição da Página Atual -->
                <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
                <!-- Botão Anterior -->
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>">
                    <img src="../img/setinha-esquerda.png" alt="Anterior" class="seta-img">
                    </a>
                <?php endif; ?>
                <!-- Botão Próximo -->
                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>">
                    <img src="../img/setinha.png" alt="Próximo" class="seta-img">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
