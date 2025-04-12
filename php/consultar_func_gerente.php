<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
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

// Buscar informações do usuário logado
$usuario_id = $_SESSION['usuarioId'];
$sql = "SELECT nome_completo, email, cargo FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nome_completo, $email, $cargo_usuario);
$stmt->fetch();
$stmt->free_result();

// Verificar permissão de acesso
if (!in_array($cargo_usuario, ['Gerente', 'Admin'])) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

// Paginação
$clientes_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $clientes_por_pagina;

// Filtros
$letra_filtro = isset($_GET['letra']) ? $_GET['letra'] : '';
$filtro = isset($_GET['search']) ? $_GET['search'] : '';

// Buscar funcionários conforme filtro e cargo
if ($letra_filtro) {
    $param = "$letra_filtro%";
    $sql = "SELECT id, nome_completo, email, telefone, cargo, registrado_em FROM clientes 
            WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'") . " 
            AND nome_completo LIKE ? 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $param, $offset, $clientes_por_pagina);
} elseif ($filtro) {
    $param = "%$filtro%";
    $sql = "SELECT id, nome_completo, email, telefone, cargo, registrado_em FROM clientes 
            WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'") . " 
            AND (nome_completo LIKE ? OR email LIKE ?) 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $param, $param, $offset, $clientes_por_pagina);
} else {
    $sql = "SELECT id, nome_completo, email, telefone, cargo, registrado_em FROM clientes 
            WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'") . " 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $clientes_por_pagina);
}

$stmt->execute();
$result = $stmt->get_result();

// Contagem total para paginação
if ($letra_filtro) {
    $sql_total = "SELECT COUNT(*) as total FROM clientes 
                  WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'") . " 
                  AND nome_completo LIKE ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $param);
} elseif ($filtro) {
    $sql_total = "SELECT COUNT(*) as total FROM clientes 
                  WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'") . " 
                  AND (nome_completo LIKE ? OR email LIKE ?)";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("ss", $param, $param);
} else {
    $sql_total = "SELECT COUNT(*) as total FROM clientes 
                  WHERE cargo " . ($cargo_usuario === 'Admin' ? "IN ('Funcionario', 'Gerente')" : "= 'Funcionario'");
    $stmt_total = $conn->prepare($sql_total);
}

$stmt_total->execute();
$total_result = $stmt_total->get_result()->fetch_assoc();
$total_clientes = $total_result['total'];
$total_paginas = ceil($total_clientes / $clientes_por_pagina);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Funcionários</title>
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
<div class="container">
    <div class="sidebar">
        <?php if ($cargo_usuario === 'Admin'): ?>
            <video autoplay loop muted><source src="../videos/overlay_branca.mp4" type="video/mp4"></video>
            <div class="profile-icon"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></div>
            <p><strong><?php echo $nome_completo; ?></strong></p>
            <p><?php echo $email; ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='admin_dashboard.php'"><img src="../img/casa.png"><span>Dashboard</span></div>
                <div class="icon-item" onclick="window.location.href='cadastro_admin.php'"><img src="../img/novo-usuario.png"><span>Cadastrar</span></div>
                <div class="icon-item" onclick="window.location.href='funcoes_admin.php'"><img src="../img/referencia.png"><span>Funções</span></div>
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'"><img src="../img/ajudando.png"><span>Esqueceu a Senha</span></div>
                <div class="icon-item" onclick="window.location.href='logout.php'"><img src="../img/sairr.png"><span>Sair</span></div>
            </div>
        <?php else: ?>
            <video autoplay loop muted><source src="../videos/overlay_azul.mp4" type="video/mp4"></video>
            <div class="profile-icon"><?php echo strtoupper(substr($_SESSION['usuarioNome'], 0, 1)); ?></div>
            <p><strong><?php echo htmlspecialchars($_SESSION['usuarioNome']); ?></strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuarioEmail']); ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='../perfil.php'"><img src="../img/usersembarra.png"><span>Minha conta</span></div>
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'"><img src="../img/ajudando.png"><span>Esqueceu a Senha</span></div>
                <div class="icon-item" onclick="window.location.href='consultar_clientes.php'"><img src="../img/lupa.png"><span>Consultar Clientes</span></div>
                <div class="icon-item" onclick="window.location.href='consultar_clientes.php'"><img src="../img/homem-de-negocios.png"><span>Consultar Funcionários</span></div>
                <div class="icon-item" onclick="window.location.href='consultar_modelos.php'"><img src="../img/referencia.png"><span>Consultar Modelos</span></div>
                <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'"><img src="../img/carro_de_frente.png"><span>Consultar Veículos</span></div>
                <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'"><img src="../img/promocoes.png"><span>Consultar Promoções</span></div>
                <div class="icon-item" onclick="window.location.href='logout.php'"><img src="../img/sairr.png"><span>Sair</span></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="content">
        <h2 class="btn-shine">Consulta de Funcionários</h2>
        <form method="GET" action="">
            <input type="text" name="search" class="input" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($filtro); ?>">
            <button type="submit"><img src="../img/lupa.png" alt="Buscar" class="icone-lupa"></button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Cargo</th>
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
                        <td><?php echo $row['cargo']; ?></td>
                        <td><?php echo $row['registrado_em']; ?></td>
                        <td>
                            <a class="a-btn" href="editar_funcionario.php?id=<?php echo $row['id']; ?>">
                                <img src="../img/editar.png" alt="Editar" class="btn-editar">
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="paginacao">
            <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
            <?php if ($pagina_atual > 1): ?>
                <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>"><img src="../img/setinha-esquerda.png" class="seta-img"></a>
            <?php endif; ?>
            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>"><img src="../img/setinha.png" class="seta-img"></a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
