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
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>
<body>
<div class="container">
    <!-- Sidebar -->
    <div>
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="content">
        <?php if ($cargo_usuario === 'Admin'): ?>
            <a href="funcoes_admin.php" class="back-button">
                <img src="../img/seta-esquerdabranca.png" alt="Voltar">
            </a>
        <?php endif; ?>
        
        <h2 class="btn-shine">Consulta de Funcionários</h2>
        
        <a href="cadastro_admin.php?redir=3" class="btn-novo-cliente">
            <img src="../img/adicionar-usuario.png" alt="Cadastrar Funcionário" class="img-btn">
            Cadastrar Funcionário
        </a>

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
                        <td style="white-space:nowrap;">
                            <a class="a-btn" href="editar_funcionario.php?id=<?php echo $row['id']; ?>" style="text-decoration:none;outline:none;margin-right:2px;vertical-align:middle;">
                                <img src="../img/editar.png" alt="Editar" class="btn-editar">
                            </a>
                            <button class="a-btn btn-editar" id="remover-funcionario-<?php echo $row['id']; ?>" data-id="<?php echo $row['id']; ?>" data-nome="<?php echo htmlspecialchars($row['nome_completo'], ENT_QUOTES); ?>" data-cargo="<?php echo $row['cargo']; ?>" style="background:none;border:none;cursor:pointer;text-decoration:none;padding:0;margin-left:2px;vertical-align:middle;">
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
                <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>"><img src="../img/setinha-esquerda.png" class="seta-img"></a>
            <?php endif; ?>
            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>"><img src="../img/setinha.png" class="seta-img"></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de confirmação de remoção -->
<div class="modal-remover" id="modal-remover">
    <div class="modal-remover-content">
        <h3>Remover funcionário</h3>
        <p id="modal-remover-msg">Tem certeza que deseja remover este funcionário?</p>
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
let cargoParaRemover = '';
document.querySelectorAll('button[id^="remover-funcionario-"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        idParaRemover = this.getAttribute('data-id');
        nomeParaRemover = this.getAttribute('data-nome');
        cargoParaRemover = this.getAttribute('data-cargo');
        document.getElementById('modal-remover-msg').innerHTML = `Tem certeza que deseja excluir <b>${nomeParaRemover}</b> (ID: <span style='color:#e53935;font-weight:bold;'>${idParaRemover}</span>)?`;
        document.getElementById('modal-remover').classList.add('ativa');
    });
});
document.getElementById('btn-cancelar-remover').onclick = function() {
    document.getElementById('modal-remover').classList.remove('ativa');
    idParaRemover = null;
    nomeParaRemover = '';
    cargoParaRemover = '';
};
document.getElementById('btn-confirmar-remover').onclick = function() {
    if (!idParaRemover) return;
    fetch('remover_funcionario.php', {
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
            alert(data.erro || 'Erro ao remover funcionário.');
        }
        document.getElementById('modal-remover').classList.remove('ativa');
        idParaRemover = null;
        nomeParaRemover = '';
        cargoParaRemover = '';
        document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este funcionário?';
    })
    .catch(() => {
        alert('Erro ao conectar com o servidor.');
        document.getElementById('modal-remover').classList.remove('ativa');
        idParaRemover = null;
        nomeParaRemover = '';
        cargoParaRemover = '';
        document.getElementById('modal-remover-msg').textContent = 'Tem certeza que deseja remover este funcionário?';
    });
};
</script>
</body>
</html>
