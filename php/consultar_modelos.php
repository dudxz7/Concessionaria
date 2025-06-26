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

// Query base para mostrar quantos veículos cadastrados existem por modelo
$sql_base = "
    SELECT 
        modelos.id AS modelo_id, 
        modelos.modelo, 
        modelos.fabricante, 
        modelos.ano, 
        modelos.preco, 
        modelos.cor, 
        COUNT(CASE WHEN veiculos.status = 'disponivel' THEN 1 END) AS quantidade_veiculos
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
            vertical-align: middle;
            margin: 0 2px;
            width: 24px;
            height: 24px;
            object-fit: contain;
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
                        <th>Estoque</th> <!-- Coluna para quantidade de veículos -->
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
                            <td><?php echo $row['quantidade_veiculos']; ?></td> <!-- Exibe a quantidade de veículos -->
                            <td style="white-space:nowrap;">
                                <a class="a-btn" href="editar_modelo.php?id=<?php echo $row['modelo_id']; ?>" style="text-decoration:none;outline:none;margin-right:2px;vertical-align:middle;">
                                    <img src="../img/editar.png" alt="Editar" class="btn-editar">
                                </a>
                                <a class="a-btn" href="cadastrar_imagens_secundarias.php?modelo_id=<?php echo $row['modelo_id']; ?>">
                                    <img src="../img/envio.png" alt="Upload Imagens" class="btn-editar">
                                </a>
                                <a class="a-btn" href="editar_ordem_imagens.php?modelo_id=<?php echo $row['modelo_id']; ?>">
                                    <img src="../img/layer.png" alt="Editar Ordem de Imagens" class="btn-editar">
                                </a>
                                <button class="a-btn btn-editar" id="remover-modelo-<?php echo $row['modelo_id']; ?>" data-id="<?php echo $row['modelo_id']; ?>" data-nome="<?php echo htmlspecialchars($row['modelo'], ENT_QUOTES); ?>" style="background:none;border:none;cursor:pointer;text-decoration:none;padding:0;margin-left:2px;vertical-align:middle;">
                                    <img src="../img/lixeira.png" alt="Remover" class="btn-editar">
                                </button>
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

    <!-- Modal de confirmação de remoção -->
    <div class="modal-remover" id="modal-remover">
        <div class="modal-remover-content">
            <h3>Remover modelo</h3>
            <p id="modal-remover-msg">Tem certeza que deseja remover este modelo?</p>
            <div class="modal-remover-botoes">
                <button id="btn-cancelar-remover">Cancelar</button>
                <button id="btn-confirmar-remover">Remover</button>
            </div>
        </div>
    </div>
    <style>
    .modal-remover {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(30, 32, 38, 0.55);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .modal-remover.ativa { display: flex; }
    .modal-remover-content {
        background: #23272f;
        padding: 32px 28px 24px 28px;
        border-radius: 18px;
        max-width: 480px;
        min-width: 340px;
        width: 100%;
        margin: auto;
        text-align: center;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.25), 0 1.5px 8px 0 #0002;
        color: #f3f3f3;
        border: 1.5px solid #23272f;
        font-family: 'Segoe UI', 'Arial', sans-serif;
    }
    .modal-remover-content h3 {
        margin-top: 0;
        margin-bottom: 18px;
        font-size: 1.25rem;
        color: #e53935;
        letter-spacing: 0.5px;
    }
    #modal-remover-msg {
        font-size: 1.08rem;
        margin-bottom: 18px;
        color: #f3f3f3;
        word-break: break-word;
        line-height: 1.5;
    }
    .modal-remover-botoes {
        margin-top: 10px;
        display: flex;
        gap: 18px;
        justify-content: center;
    }
    .modal-remover-botoes button {
        padding: 9px 22px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.18s, color 0.18s, box-shadow 0.18s;
        box-shadow: 0 1.5px 6px #0001;
    }
    #btn-cancelar-remover {
        background: #2d313a;
        color: #f3f3f3;
        border: 1px solid #444;
    }
    #btn-cancelar-remover:hover {
        background: #23272f;
        color: #e53935;
        border: 1px solid #e53935;
    }
    #btn-confirmar-remover {
        background: linear-gradient(90deg, #e53935 60%, #b71c1c 100%);
        color: #fff;
        border: none;
    }
    #btn-confirmar-remover:hover {
        background: #fff;
        color: #e53935;
        border: 1px solid #e53935;
    }
    </style>
    <script>
    let idParaRemover = null;
    let nomeParaRemover = '';
    document.querySelectorAll('button[id^="remover-modelo-"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            idParaRemover = this.getAttribute('data-id');
            nomeParaRemover = this.getAttribute('data-nome');
            document.getElementById('modal-remover-msg').innerHTML = `Tem certeza que deseja excluir o modelo <b>${nomeParaRemover}</b> (ID: <span style='color:#e53935;font-weight:bold;'>${idParaRemover}</span>)?`;
            document.getElementById('modal-remover').classList.add('ativa');
        });
    });
    document.getElementById('btn-cancelar-remover').onclick = function() {
        document.getElementById('modal-remover').classList.remove('ativa');
        idParaRemover = null;
        nomeParaRemover = '';
    };
    document.getElementById('btn-confirmar-remover').onclick = function() {
        if (!idParaRemover) return;
        fetch('remover_modelo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(idParaRemover)
        })
        .then(res => res.json())
        .then(data => {
            if (data.sucesso) {
                const linha = document.querySelector('button[data-id="'+idParaRemover+'"').closest('tr');
                if (linha) linha.remove();
            } else {
                alert(data.erro || 'Erro ao remover modelo.');
            }
            document.getElementById('modal-remover').classList.remove('ativa');
            idParaRemover = null;
            nomeParaRemover = '';
            document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este modelo?';
        })
        .catch(() => {
            alert('Erro ao conectar com o servidor.');
            document.getElementById('modal-remover').classList.remove('ativa');
            idParaRemover = null;
            nomeParaRemover = '';
            document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este modelo?';
        });
    };
    </script>
</body>
</html>