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

// Bloquear acesso para clientes comuns
if ($cargo_usuario === 'Cliente') {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

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
            WHERE (modelos.modelo LIKE ? OR veiculos.numero_chassi LIKE ?) AND veiculos.status = 'disponivel'
            ORDER BY veiculos.id ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "%$filtro%";
    $stmt->bind_param("ssii", $param, $param, $offset, $veiculos_por_pagina);
} elseif ($letra_filtro) {
    $sql = "SELECT veiculos.id, modelos.modelo, modelos.fabricante, modelos.ano, modelos.preco, veiculos.numero_chassi 
            FROM veiculos 
            JOIN modelos ON veiculos.modelo_id = modelos.id 
            WHERE modelos.modelo LIKE ? AND veiculos.status = 'disponivel'
            ORDER BY veiculos.id ASC
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "$letra_filtro%";
    $stmt->bind_param("sii", $param, $offset, $veiculos_por_pagina);
} else {
    $sql = "SELECT veiculos.id, modelos.modelo, modelos.fabricante, modelos.ano, modelos.preco, veiculos.numero_chassi 
            FROM veiculos 
            JOIN modelos ON veiculos.modelo_id = modelos.id 
            WHERE veiculos.status = 'disponivel'
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
                WHERE (modelos.modelo LIKE ? OR veiculos.numero_chassi LIKE ?) AND veiculos.status = 'disponivel'";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("ss", $param, $param);
} elseif ($letra_filtro) {
    $sql_total = "SELECT COUNT(*) as total 
                FROM veiculos 
                JOIN modelos ON veiculos.modelo_id = modelos.id 
                WHERE modelos.modelo LIKE ? AND veiculos.status = 'disponivel'";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $param);
} else {
    $sql_total = "SELECT COUNT(*) as total FROM veiculos WHERE status = 'disponivel'";
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
    <link rel="icon" href="../img/logos/logoofcbmw.png">
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
            <h2 class="btn-shine">Consulta de Veículos</h2>

            <?php if ($cargo_usuario === 'Gerente' || $cargo_usuario === 'Admin'): ?>
                <a href="cadastrar_veiculo.php" class="btn-novo-cliente">
                    <img src="../img/veiculos/carro.png" alt="Cadastrar Veículo" class="img-btn">Cadastrar Veículo Novo
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
                            <td style="white-space:nowrap; vertical-align:middle;">
                                <a class="a-btn" href="editar_veiculo.php?id=<?php echo $row['id']; ?>" style="display:inline-block;vertical-align:middle;margin-right:4px;">
                                    <img src="../img/editar.png" alt="Editar" class="btn-editar">
                                </a>
                                <button class="a-btn btn-editar" id="remover-veiculo-<?php echo $row['id']; ?>" data-id="<?php echo $row['id']; ?>" data-modelo="<?php echo htmlspecialchars($row['modelo'], ENT_QUOTES); ?>" style="background:none;border:none;cursor:pointer;text-decoration:none;padding:0;vertical-align:middle;display:inline-block;">
                                    <img src="../img/lixeira.png" alt="Remover" class="btn-editar">
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Modal de confirmação de remoção -->
            <div class="modal-remover" id="modal-remover">
                <div class="modal-remover-content">
                    <h3>Remover veículo</h3>
                    <p id="modal-remover-msg">Tem certeza que deseja remover este veículo?</p>
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
            let modeloParaRemover = '';
            document.querySelectorAll('button[id^="remover-veiculo-"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    idParaRemover = this.getAttribute('data-id');
                    modeloParaRemover = this.getAttribute('data-modelo');
                    document.getElementById('modal-remover-msg').innerHTML = `Tem certeza que deseja excluir o veículo <b>${modeloParaRemover}</b> (ID: <span style='color:#e53935;font-weight:bold;'>${idParaRemover}</span>)?`;
                    document.getElementById('modal-remover').classList.add('ativa');
                });
            });
            document.getElementById('btn-cancelar-remover').onclick = function() {
                document.getElementById('modal-remover').classList.remove('ativa');
                idParaRemover = null;
                modeloParaRemover = '';
            };
            document.getElementById('btn-confirmar-remover').onclick = function() {
                if (!idParaRemover) return;
                fetch('remover_veiculo.php', {
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
                        alert(data.erro || 'Erro ao remover veículo.');
                    }
                    document.getElementById('modal-remover').classList.remove('ativa');
                    idParaRemover = null;
                    modeloParaRemover = '';
                    document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este veículo?';
                })
                .catch(() => {
                    alert('Erro ao conectar com o servidor.');
                    document.getElementById('modal-remover').classList.remove('ativa');
                    idParaRemover = null;
                    modeloParaRemover = '';
                    document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este veículo?';
                });
            };
            </script>
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
