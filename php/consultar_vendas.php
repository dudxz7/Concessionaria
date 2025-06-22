<?php
session_start();
include('conexao.php');
// Atualiza boletos expirados
$conn->query("UPDATE pagamento_boleto SET status = 'expirado' WHERE status = 'pendente' AND data_expiracao < NOW()");
// Atualiza pix expirados
$conn->query("UPDATE pagamentos_pix SET status = 'expirado' WHERE status = 'pendente' AND expira_em < NOW()");

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

// Filtros adicionais para tipo de pagamento e ordenação
$tipo_pagamento_filtro = $_GET['tipo_pagamento'] ?? '';
$ordenar = $_GET['ordenar'] ?? 'id_asc'; // id_asc, id_desc, data_asc, data_desc

// Paginação
$limite = 10;
$pagina_atual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $limite;

// Isso aqui que junta as tabelas de pagamentos Pix e Boleto ( PHP EU TE ODEIO COM MT FORÇA D VDD)
$sql_union = [];
if ($tipo_pagamento_filtro === '' || $tipo_pagamento_filtro === 'Pix') {
    $sql_union[] = "SELECT 'Pix' AS tipo_pagamento, p.id, p.usuario_id, COALESCE(c.nome_completo, CONCAT('ID ', p.usuario_id)) AS usuario_nome, p.veiculo_id,
    p.cor, p.valor, p.status, p.expira_em AS data_pagamento FROM pagamentos_pix p LEFT JOIN clientes c ON c.id = p.usuario_id";
}
if ($tipo_pagamento_filtro === '' || $tipo_pagamento_filtro === 'Boleto') {
    $sql_union[] = "SELECT 'Boleto' AS tipo_pagamento, b.id, b.usuario_id, COALESCE(c.nome_completo, CONCAT('ID ', b.usuario_id)) AS usuario_nome, b.veiculo_id,
    b.cor, b.valor, b.status, b.data_expiracao AS data_pagamento FROM pagamento_boleto b LEFT JOIN clientes c ON c.id = b.usuario_id";
}
$sql_base = implode(" UNION ALL ", $sql_union);

// Adiciona filtro de busca
$where = [];
$params = [];
$tipos = "";
if (!empty($filtro)) {
    $where[] = "(CAST(usuario_id AS CHAR) LIKE ? OR usuario_nome LIKE ? OR CAST(veiculo_id AS CHAR) LIKE ? OR cor LIKE ?)";
    $busca = "%$filtro%";
    $params[] = &$busca;
    $params[] = &$busca;
    $params[] = &$busca;
    $params[] = &$busca;
    $tipos .= "ssss";
}

if (!empty($where)) {
    $sql_base = "SELECT * FROM (" . $sql_base . ") AS vendas WHERE " . implode(' AND ', $where);
} else {
    $sql_base = "SELECT * FROM (" . $sql_base . ") AS vendas";
}

// Ordenação
switch ($ordenar) {
    case 'id_desc':
        $sql_base .= " ORDER BY data_pagamento DESC, id DESC";
        break;
    case 'data_asc':
        $sql_base .= " ORDER BY data_pagamento ASC, id ASC";
        break;
    case 'data_desc':
        $sql_base .= " ORDER BY data_pagamento DESC, id DESC";
        break;
    default:
        $sql_base .= " ORDER BY data_pagamento ASC, id ASC";
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
    <title>Consultar Vendas</title>
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        a {
            text-decoration: none;
        }

        .btn-editar {
            display: inline-block;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }

        .toggle-switch input {
            display: none;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            transition: .4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked+.slider {
            background-color: #4caf50;
        }

        .toggle-switch input:not(:checked)+.slider {
            background-color: #e53935;
        }

        /* Amarelo para pendente */
        .toggle-switch.pendente .slider {
            background-color: #ffc107 !important;
        }

        .toggle-switch.expirado .slider {
            background-color:rgb(121, 121, 121) !important;
        }

        .toggle-switch input:checked+.slider:before {
            transform: translateX(24px);
        }

        .toggle-switch input:not(:checked)+.slider:before {
            transform: translateX(0);
        }

        .filtro-input {
            padding: 8px 14px;
            border-radius: 6px;
            border: 1.5px solid #2f4eda;
            background: #fff;
            font-size: 1.08rem;
            min-width: 220px;
            transition: border 0.2s;
        }

        .filtro-input:focus {
            border-color: #2f4eda;
            outline: 2px solid rgb(37, 33, 243);
        }

        .filtro-select {
            width: 150px;
            height: 48px;
            margin-top: 1px; 
            padding: 12px 20px;
            padding: 8px 12px;
            border: 1.5px solid #f2f2f2;
            background: #f2f2f2;
            font-size: 1.0rem;
            min-width: 120px;
            color: #222;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%232196f3" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 18px 18px;
        }

        .filtro-select:focus {
            border-color: #2f4eda;
            outline: 2px solid #2f4eda;
            box-shadow: 0 0 0 2px #2196f355;
        }

        @media (max-width: 700px) {
            #filtro-vendas-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filtro-select,
            .filtro-input {
                width: 100%;
                min-width: 0;
            }
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
            <h2 class="btn-shine">Consulta de Vendas</h2>
            <form id="filtro-vendas-form" method="GET" action="">
                <input type="text" name="search" class="input filtro-input" placeholder="Buscar por usuário, veículo ou cor..."
                    value="<?php echo htmlspecialchars($filtro); ?>">
                <select name="tipo_pagamento" class="input-select filtro-select">
                    <option value="" <?php if ($tipo_pagamento_filtro === '')
                        echo 'selected'; ?>>Todos</option>
                    <option value="Pix" <?php if ($tipo_pagamento_filtro === 'Pix')
                        echo 'selected'; ?>>Pix</option>
                    <option value="Boleto" <?php if ($tipo_pagamento_filtro === 'Boleto')
                        echo 'selected'; ?>>Boleto
                    </option>
                </select>
                <select name="ordenar" class="input-select filtro-select">
                    <option value="id_asc" <?php if ($ordenar === 'id_asc')
                        echo 'selected'; ?>>ID Crescente</option>
                    <option value="id_desc" <?php if ($ordenar === 'id_desc')
                        echo 'selected'; ?>>ID Decrescente</option>
                    <option value="data_asc" <?php if ($ordenar === 'data_asc')
                        echo 'selected'; ?>>Mais Antigo</option>
                    <option value="data_desc" <?php if ($ordenar === 'data_desc')
                        echo 'selected'; ?>>Mais Recente
                    </option>
                </select>
                <button type="submit" class="filtro-btn">
                    <img src="../img/lupa.png" class="icone-lupa" style="width:18px;vertical-align:middle;">
                </button>
            </form>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Usuário</th>
                        <th>Veículo</th>
                        <th>Cor</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Data Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $contador = 1 + $offset;
                    $agora = date('Y-m-d H:i:s');
                    while ($row = $result->fetch_assoc()) {
                        $toggleClass = '';
                        $toggleDisabled = '';
                        $status = $row['status'];
                        $data_pagamento = $row['data_pagamento'];
                        // Só desabilita o toggle se status for 'expirado'
                        if ($status === 'pendente') {
                            $toggleClass = 'pendente';
                        } else if ($status === 'expirado') {
                            $toggleClass = 'expirado';
                            $toggleDisabled = 'disabled';
                        } else if ($status === 'recusado') {
                            $toggleClass = 'recusado';
                        }
                    ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td><?php echo $row['tipo_pagamento']; ?></td>
                            <td><?php echo htmlspecialchars($row['usuario_nome']); ?></td>
                            <td><?php echo $row['veiculo_id']; ?></td>
                            <td><?php echo $row['cor']; ?></td>
                            <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                            <td class="status-label" data-id="<?php echo $row['id']; ?>"
                                data-tipo="<?php echo $row['tipo_pagamento']; ?>">
                                <?php echo $status; ?>
                            </td>
                            <td><?php echo $row['data_pagamento']; ?></td>
                            <td>
                                <label class="toggle-switch <?php echo $toggleClass; ?>">
                                    <input type="checkbox" class="toggle-aprovar" data-id="<?php echo $row['id']; ?>"
                                        data-tipo="<?php echo $row['tipo_pagamento']; ?>" <?php echo ($row['status'] === 'aprovado') ? 'checked' : ''; ?> <?php echo $toggleDisabled; ?> />
                                    <span class="slider"></span>
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="paginacao">
                <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>">
                        <img src="../img/setinha-esquerda.png" class="seta-img">
                    </a>
                <?php endif; ?>
                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>">
                        <img src="../img/setinha.png" class="seta-img">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Submete o formulário automaticamente ao trocar qualquer filtro
        document.querySelectorAll('.filtro-select').forEach(function(select) {
            select.addEventListener('change', function() {
                document.getElementById('filtro-vendas-form').submit();
            });
        });

        // Debounce para search bar
        let searchTimeout;
        const searchInput = document.querySelector('.filtro-input');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    document.getElementById('filtro-vendas-form').submit();
                }, 500); // 500ms após parar de digitar
            });
        }

        document.querySelectorAll('.toggle-aprovar').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                var id = this.getAttribute('data-id');
                var tipo = this.getAttribute('data-tipo');
                var checked = this.checked;
                var status = checked ? 'aprovado' : 'recusado';
                var tdStatus = this.closest('tr').querySelector('.status-label');
                var label = this.closest('.toggle-switch');
                var toggleInput = this;
                // Desabilita o toggle durante a requisição
                toggleInput.disabled = true;
                fetch('atualizar_status_venda.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(id) + '&tipo_pagamento=' + encodeURIComponent(tipo) + '&status=' + encodeURIComponent(status)
                })
                    .then(response => response.json())
                    .then(data => {
                        // Recarrega a página após a resposta, independente de sucesso ou erro
                        location.reload();
                    })
                    .catch(() => {
                        // Também recarrega a página em caso de erro
                        location.reload();
                    });
            });
        });
        // Ao carregar, desabilita toggles de vendas expiradas
        document.querySelectorAll('.toggle-aprovar').forEach(function(toggle) {
            var tr = toggle.closest('tr');
            var tdStatus = tr.querySelector('.status-label');
            var label = tr.querySelector('.toggle-switch');
            if (tdStatus.textContent.trim() === 'recusado') {
                label.classList.add('recusado');
            }
            if (tdStatus.textContent.trim() === 'expirado') {
                toggle.disabled = true;
                label.classList.add('expirado');
            } else if (tdStatus.textContent.trim() === 'pendente') {
                toggle.disabled = false;
                label.classList.add('pendente');
            }
        });
    </script>
</body>
</html>