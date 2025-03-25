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

// Busca dinâmica
$filtro = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filtro = $_GET['search'];
    $sql = "SELECT id, nome_completo, email, telefone FROM clientes 
            WHERE nome_completo LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql);
    $param = "%$filtro%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT id, nome_completo, email, telefone FROM clientes";
    $result = $conn->query($sql);
}

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
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="../videos/overlay_azul.mp4" type="video/mp4">
                Seu navegador não suporta vídeos.
            </video>
            <div class="profile-icon"><?php echo strtoupper(substr($_SESSION['usuarioNome'], 0, 1)); ?></div>
            <p><strong><?php echo $_SESSION['usuarioNome']; ?></strong></p>
            <p><?php echo $_SESSION['usuarioEmail']; ?></p>
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

        <div class="content">
            <h2>Consulta de Clientes</h2>
            <form method="GET" action="">
                <input type="text" name="search" class="input" placeholder="Buscar por nome ou email..." value="<?php echo htmlspecialchars($filtro); ?>">
                <button type="submit">Buscar</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nome_completo']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['telefone']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
