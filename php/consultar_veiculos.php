<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli("localhost", "root", "", "sistema_bmw");
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar o cargo do usuário logado
$usuario_id = $_SESSION['usuarioId'];
$sql = "SELECT cargo FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($cargo_usuario);
$stmt->fetch();
$stmt->free_result();

$isFuncionario = ($cargo_usuario == 'Funcionario');

// Paginação
$veiculos_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $veiculos_por_pagina;

// Filtros
$filtro = isset($_GET['search']) ? $_GET['search'] : '';
$letra_filtro = isset($_GET['letra']) ? $_GET['letra'] : '';

if ($filtro) {
    $sql = "SELECT veiculos.id, modelos.modelo, modelos.fabricante, modelos.ano, modelos.preco, veiculos.numero_chassi 
            FROM veiculos 
            JOIN modelos ON veiculos.modelo_id = modelos.id 
            WHERE modelos.modelo LIKE ? OR veiculos.numero_chassi LIKE ?
            ORDER BY veiculos.id ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "%$filtro%";
    $stmt->bind_param("ssii", $param, $param, $offset, $veiculos_por_pagina);
} elseif ($letra_filtro) {
    $sql = "SELECT veiculos.id, modelos.modelo, modelos.fabricante, modelos.ano, modelos.preco, veiculos.numero_chassi 
            FROM veiculos 
            JOIN modelos ON veiculos.modelo_id = modelos.id 
            WHERE modelos.modelo LIKE ?
            ORDER BY veiculos.id ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "$letra_filtro%";
    $stmt->bind_param("sii", $param, $offset, $veiculos_por_pagina);
} else {
    $sql = "SELECT veiculos.id, modelos.modelo, modelos.fabricante, modelos.ano, modelos.preco, veiculos.numero_chassi 
            FROM veiculos 
            JOIN modelos ON veiculos.modelo_id = modelos.id 
            ORDER BY veiculos.id ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $veiculos_por_pagina);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Consulta total para paginação
if ($filtro) {
    $sql_total = "SELECT COUNT(*) as total 
                  FROM veiculos 
                  JOIN modelos ON veiculos.modelo_id = modelos.id 
                  WHERE modelos.modelo LIKE ? OR veiculos.numero_chassi LIKE ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("ss", $param, $param);
} elseif ($letra_filtro) {
    $sql_total = "SELECT COUNT(*) as total 
                  FROM veiculos 
                  JOIN modelos ON veiculos.modelo_id = modelos.id 
                  WHERE modelos.modelo LIKE ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $param);
} else {
    $sql_total = "SELECT COUNT(*) as total FROM veiculos";
    $stmt_total = $conn->prepare($sql_total);
}
$stmt_total->execute();
$total_result = $stmt_total->get_result()->fetch_assoc();
$total_veiculos = $total_result['total'];
$stmt_total->close();

$total_paginas = ceil($total_veiculos / $veiculos_por_pagina);
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Veículos</title>
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="../videos/overlay_azul.mp4" type="video/mp4">
            </video>
            <div class="profile-icon"><?php echo strtoupper(substr($_SESSION['usuarioNome'], 0, 1)); ?></div>
            <p><strong><?php echo htmlspecialchars($_SESSION['usuarioNome']); ?></strong></p>
            <p><?php echo htmlspecialchars($_SESSION['usuarioEmail']); ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='../perfil.php'">
                    <img src="../img/usersembarra.png" alt="Minha Conta"><span>Minha conta</span>
                </div>
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'">
                    <img src="../img/ajudando.png" alt="Esqueceu a Senha"><span>Esqueceu a Senha</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_clientes.php'">
                    <img src="../img/lupa.png" alt="Consultar Clientes"><span>Consultar Clientes</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_modelos.php'">
                    <img src="../img/referencia.png" alt="Consultar Modelos"><span>Consultar Modelos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'">
                    <img src="../img/carro_de_frente.png" alt="Consultar Veículos"><span>Consultar Veículos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'">
                    <img src="../img/promocoes.png" alt="Consultar Promoções"><span>Consultar Promoções</span>
                </div>
                <div class="icon-item" onclick="window.location.href='logout.php'">
                    <img src="../img/sairr.png" alt="Sair"><span>Sair</span>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="content">
            <h2 class="btn-shine">Consulta de Veículos</h2>

            <?php if ($isFuncionario): ?>
                <a href="cadastrar_veiculo.php" class="btn-novo-cliente">
                    <img src="../img/carro.png" alt="Cadastrar Veículo" class="img-btn">Cadastrar Veículo Novo
                </a>
            <?php endif; ?>

            <form method="GET" action="">
                <input type="text" name="search" class="input" placeholder="Buscar por modelo ou chassi..." value="<?php echo htmlspecialchars($filtro); ?>">
                <button type="submit"><img src="../img/lupa.png" class="icone-lupa"></button>
            </form>

            <div class="letras-filtro">
                <?php foreach (range('A', 'Z') as $letra): ?>
                    <a href="?letra=<?php echo $letra; ?>" <?php echo ($letra == $letra_filtro) ? 'class="selected"' : ''; ?>>
                        <?php echo $letra; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Modelo</th>
                        <th>Fabricante</th>
                        <th>Ano</th>
                        <th>Preço</th>
                        <th>Chassi</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['modelo']; ?></td>
                            <td><?php echo $row['fabricante']; ?></td>
                            <td><?php echo $row['ano']; ?></td>
                            <td>R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo $row['numero_chassi']; ?></td>
                            <td>
                                <a class="a-btn" href="editar_veiculo.php?id=<?php echo $row['id']; ?>">
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
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha-esquerda.png" class="seta-img">
                    </a>
                <?php endif; ?>
                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha.png" class="seta-img">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
