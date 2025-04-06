<?php
session_start();
include('conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioNome'])) {
    header('Location: login.php');
    exit;
}

// Pega o ID do usuário logado
$usuarioId = $_SESSION['usuarioId'] ?? null;

// Verifica o cargo do usuário no banco
$cargo_usuario = '';
$isFuncionario = false;

if ($usuarioId) {
    $sql = "SELECT cargo FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $cargo_usuario = $row['cargo'];
        $_SESSION['usuarioCargo'] = $cargo_usuario; // salva na sessão pra usar depois também
        $isFuncionario = ($cargo_usuario === 'Funcionario');
    }
}

// Filtros
$filtro = $_GET['search'] ?? '';
$letra_filtro = $_GET['letra'] ?? '';

// Paginação
$limite = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $limite;

// Query base
$sql_base = "SELECT * FROM modelos WHERE 1=1";
$params = [];
$tipos = "";

// Adiciona filtro por busca
if (!empty($filtro)) {
    $sql_base .= " AND (modelo LIKE ? OR fabricante LIKE ?)";
    $busca = "%$filtro%";
    $params[] = &$busca;
    $params[] = &$busca;
    $tipos .= "ss";
}

// Adiciona filtro por letra
if (!empty($letra_filtro)) {
    $sql_base .= " AND modelo LIKE ?";
    $letra = "$letra_filtro%";
    $params[] = &$letra;
    $tipos .= "s";
}

// Consulta total de resultados
$stmt_total = $conn->prepare($sql_base);
if (!empty($tipos) && count($params) > 0) {
    $stmt_total->bind_param($tipos, ...$params);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->num_rows;
$total_paginas = ceil($total_registros / $limite);

// Consulta paginada
$sql_pag = $sql_base . " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_pag);

// Inclui os limites na consulta paginada
$params_pag = $params;
$tipos_pag = $tipos . "ii";
$params_pag[] = &$limite;
$params_pag[] = &$offset;

if (!empty($tipos_pag) && count($params_pag) > 0) {
    $stmt->bind_param($tipos_pag, ...$params_pag);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Modelos</title>
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
            <div class="icon-item" onclick="window.location.href='../perfil.php'"><img src="../img/usersembarra.png"><span>Minha conta</span></div>
            <div class="icon-item" onclick="window.location.href='esquecer_senha.php'"><img src="../img/ajudando.png"><span>Esqueceu a Senha</span></div>
            <div class="icon-item" onclick="window.location.href='consultar_clientes.php'"><img src="../img/lupa.png"><span>Consultar Clientes</span></div>
            <div class="icon-item" onclick="window.location.href='consultar_modelos.php'"><img src="../img/referencia.png"><span>Consultar Modelos</span></div>
            <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'"><img src="../img/carro_de_frente.png"><span>Consultar Veículos</span></div>
            <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'"><img src="../img/promocoes.png"><span>Consultar Promoções</span></div>
            <div class="icon-item" onclick="window.location.href='logout.php'"><img src="../img/sairr.png"><span>Sair</span></div>
        </div>
    </div>

    <!-- Conteúdo -->
    <div class="content">
        <h2 class="btn-shine">Consulta de Modelos</h2>

        <?php if ($isFuncionario): ?>
            <a href="cadastrar_modelos.php" class="btn-novo-cliente">
                <img src="../img/engrenagem.png" alt="Cadastrar Modelo" class="img-btn">Cadastrar Modelo
            </a>
        <?php endif; ?>

        <form method="GET" action="">
            <input type="text" name="search" class="input" placeholder="Buscar por modelo ou fabricante..." value="<?php echo htmlspecialchars($filtro); ?>">
            <button type="submit"><img src="../img/lupa.png" class="icone-lupa"></button>
        </form>

        <div class="letras-filtro">
            <?php foreach (range('A', 'Z') as $letra): ?>
                <a href="?letra=<?php echo $letra; ?>" <?php echo ($letra == $letra_filtro) ? 'class="selected"' : ''; ?>><?php echo $letra; ?></a>
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
                <th>Cores</th>
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
                    <td><?php echo $row['cor']; ?></td>
                    <td>
                        <a class="a-btn" href="editar_modelo.php?id=<?php echo $row['id']; ?>">
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
