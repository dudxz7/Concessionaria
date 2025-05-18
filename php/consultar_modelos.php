<?php
session_start();
include('conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioNome'])) {
    header('Location: ../login.html');
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
        $_SESSION['usuarioCargo'] = $cargo_usuario;

        // ❌ BLOQUEIA CLIENTES
        if ($cargo_usuario === 'Cliente') {
            echo "<h2>Acesso Negado</h2>";
            echo "<p>Você não tem permissão para acessar esta página.</p>";
            exit;
        }
    }
}

// Filtros
$filtro = $_GET['search'] ?? '';
$letra_filtro = $_GET['letra'] ?? '';

// Paginação
$limite = 10;
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $limite;

// Query base com JOIN para contar o estoque
$sql_base = "
    SELECT 
        modelos.id AS modelo_id, 
        modelos.modelo, 
        modelos.fabricante, 
        modelos.ano, 
        modelos.preco, 
        modelos.cor, 
        COUNT(veiculos.id) AS estoque
    FROM modelos
    LEFT JOIN veiculos ON veiculos.modelo_id = modelos.id
    WHERE 1=1
";

// Adiciona filtro por busca
$params = [];
$tipos = "";
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

$sql_base .= " GROUP BY modelos.id"; // Agrupar por modelo

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
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        a {
            text-decoration: none;
        }

        .btn-editar {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div>
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Conteúdo -->
        <div class="content">
            <?php if ($cargo_usuario === 'Admin'): ?>
                <a href="funcoes_admin.php" class="back-button">
                    <img src="../img/seta-esquerdabranca.png" alt="Voltar">
                </a>
            <?php endif; ?>
            <h2 class="btn-shine">Consulta de Modelos</h2>

            <?php if ($cargo_usuario === 'Gerente' || $cargo_usuario === 'Admin'): ?>
                <a href="cadastrar_modelos.php" class="btn-novo-cliente">
                    <img src="../img/engrenagem.png" alt="Cadastrar Modelo" class="img-btn">
                    Cadastrar Modelo
                </a>
            <?php endif; ?>

            <form method="GET" action="">
                <input type="text" name="search" class="input" placeholder="Buscar por modelo ou fabricante..."
                    value="<?php echo htmlspecialchars($filtro); ?>">
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
                        <th>Cor</th>
                        <th>Estoque</th> <!-- Coluna para Estoque -->
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['modelo_id']; ?></td>
                            <td><?php echo $row['modelo']; ?></td>
                            <td><?php echo $row['fabricante']; ?></td>
                            <td><?php echo $row['ano']; ?></td>
                            <td>R$ <?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo $row['cor']; ?></td>
                            <td><?php echo $row['estoque']; ?></td> <!-- Exibe o estoque -->
                            <td>
                                <a class="a-btn" href="editar_modelo.php?id=<?php echo $row['modelo_id']; ?>">
                                    <img src="../img/editar.png" alt="Editar" class="btn-editar">
                                </a>
                                <a class="a-btn"
                                    href="cadastrar_imagens_secundarias.php?modelo_id=<?php echo $row['modelo_id']; ?>">
                                    <img src="../img/envio.png" alt="Upload Imagens" class="btn-editar">
                                </a>
                                <a class="a-btn" href="editar_ordem_imagens.php?modelo_id=<?php echo $row['modelo_id']; ?>">
                                    <img src="../img/layer.png" alt="Editar Ordem de Imagens" class="btn-editar">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="paginacao">
                <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
                <?php if ($pagina_atual > 1): ?>
                    <a
                        href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha-esquerda.png" class="seta-img">
                    </a>
                <?php endif; ?>
                <?php if ($pagina_atual < $total_paginas): ?>
                    <a
                        href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha.png" class="seta-img">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>