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

// Armazenar o cargo na sessão para futuras verificações
$_SESSION['usuarioCargo'] = $cargo_usuario;

// Verificar permissão de acesso
if (!in_array($cargo_usuario, ['Funcionario', 'Gerente', 'Admin'])) {
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

// Buscar clientes
if ($letra_filtro) {
    $sql = "SELECT id, nome_completo, email, telefone, registrado_em, cpf FROM clientes 
            WHERE cargo = 'Cliente' AND nome_completo LIKE ? 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $param = "$letra_filtro%";
    $stmt->bind_param("sii", $param, $offset, $clientes_por_pagina);
} else {
    if ($filtro) {
        // Remove qualquer máscara do filtro se for CPF
        $filtro_sem_mascara = preg_replace('/[^0-9]/', '', $filtro);
        $sql = "SELECT id, nome_completo, email, telefone, registrado_em, cpf FROM clientes 
                WHERE cargo = 'Cliente' AND (
                    nome_completo LIKE ? OR email LIKE ? OR cpf LIKE ? OR cpf LIKE ?
                ) 
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $param = "%$filtro%";
        $param_cpf = "%$filtro_sem_mascara%";
        $stmt->bind_param("ssssii", $param, $param, $param, $param_cpf, $offset, $clientes_por_pagina);
    } else {
        $sql = "SELECT id, nome_completo, email, telefone, registrado_em, cpf FROM clientes 
                WHERE cargo = 'Cliente'
                LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $clientes_por_pagina);
    }
}

$stmt->execute();
$result = $stmt->get_result();

// Contar total de clientes
if ($letra_filtro) {
    $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE cargo = 'Cliente' AND nome_completo LIKE ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("s", $param);
} else {
    if ($filtro) {
        $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE cargo = 'Cliente' AND (
            nome_completo LIKE ? OR email LIKE ? OR cpf LIKE ? OR cpf LIKE ?
        )";
        $stmt_total = $conn->prepare($sql_total);
        $stmt_total->bind_param("ssss", $param, $param, $param, $param_cpf);
    } else {
        $sql_total = "SELECT COUNT(*) as total FROM clientes WHERE cargo = 'Cliente'";
        $stmt_total = $conn->prepare($sql_total);
    }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Clientes</title>
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

            <h2 class="btn-shine">Consulta de Clientes</h2>

            <a href="../registro.html" class="btn-novo-cliente">
                <img src="../img/adicionar-usuario.png" alt="Cadastrar Cliente" class="img-btn">
                Cadastrar Cliente Novo
            </a>

            <form method="GET" action="">
                <input type="text" name="search" class="input" id="search-cpf" placeholder="Buscar por nome, email ou CPF..." value="<?php echo htmlspecialchars($filtro); ?>">
                <button type="submit">
                    <img src="../img/lupa.png" alt="Buscar" class="icone-lupa">
                </button>
            </form>
            <script>
            // Máscara de CPF no input de pesquisa
            function cpfMask(v) {
                v = v.replace(/\D/g, "");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d)/, "$1.$2");
                v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
                return v;
            }
            const searchInput = document.getElementById('search-cpf');
            searchInput.addEventListener('input', function(e) {
                // Só aplica máscara se for número e até 11 dígitos
                let valor = this.value.replace(/\D/g, '');
                if (valor.length <= 11) {
                    this.value = cpfMask(this.value);
                }
            });
            </script>

            <div class="letras-filtro">
            <?php
                foreach (range('A', 'Z') as $letra) {
                    $class = ($letra == $letra_filtro) ? 'class="selected"' : '';
                    echo "<a href='?letra=$letra' $class>$letra</a> ";
                }
            ?>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
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
                            <td><?php 
                                $cpf = preg_replace('/[^0-9]/', '', $row['cpf']);
                                if (strlen($cpf) === 11) {
                                    echo substr($cpf,0,3).'.'.substr($cpf,3,3).'.'.substr($cpf,6,3).'-'.substr($cpf,9,2);
                                } else {
                                    echo $row['cpf'];
                                }
                            ?></td>
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
                <span>Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
                <?php if ($pagina_atual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_atual - 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha-esquerda.png" alt="Anterior" class="seta-img">
                    </a>
                <?php endif; ?>
                <?php if ($pagina_atual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_atual + 1; ?>&search=<?php echo urlencode($filtro); ?>&letra=<?php echo urlencode($letra_filtro); ?>">
                        <img src="../img/setinha.png" alt="Próximo" class="seta-img">
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
