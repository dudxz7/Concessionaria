<?php
session_start();
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;
$primeiroNome = '';
$estado = '';

// Conectar ao banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomeCompleto = trim($_SESSION['usuarioNome']);
    $partesNome = explode(' ', $nomeCompleto);
    $primeiroNome = $partesNome[0];

    // Recuperar o estado do usuário
    $userId = $_SESSION['usuarioId'];
    $sql = "SELECT estado FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($estado);
    $stmt->fetch();
    $stmt->close();
}

// Mapeamento das capitais dos estados
$estadosCapitais = [
    "AC" => "Rio Branco", "AL" => "Maceió", "AM" => "Manaus", "AP" => "Macapá",
    "BA" => "Salvador", "CE" => "Fortaleza", "DF" => "Brasília", "ES" => "Vitória",
    "GO" => "Goiânia", "MA" => "São Luís", "MT" => "Cuiabá", "MS" => "Campo Grande",
    "MG" => "Belo Horizonte", "PA" => "Belém", "PB" => "João Pessoa", "PE" => "Recife",
    "PI" => "Teresina", "PR" => "Curitiba", "RJ" => "Rio de Janeiro", "RN" => "Natal",
    "RS" => "Porto Alegre", "RO" => "Porto Velho", "RR" => "Boa Vista", "SC" => "Florianópolis",
    "SE" => "Aracaju", "SP" => "São Paulo", "TO" => "Palmas"
];

// Determinar a capital baseada no estado
$capital = $estado && isset($estadosCapitais[$estado]) ? $estadosCapitais[$estado] : "Cidade - Estado";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMW Concessionária</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">
                <img src="img/logoofcbmw.png" alt="Logo BMW">
            </a>
            <a href="index.php" id="textlogo">BMW</a>
        </div>

        <div class="divider"></div>

        <div class="location">
            <img src="img/pin-de-localizacao.png" alt="Ícone de localização">
            <div class="location-text">
                <span>Pesquisando ofertas em</span>
                <u><strong id="user-location"><?php echo htmlspecialchars($capital); ?> e Região</strong></u>
            </div>
        </div>

        <div class="nav-icons">
            <a href="carrinho.php">
                <img src="img/heart.png" alt="Favoritos" class="heart-icon">
            </a>
            <div class="login">
                <?php if ($usuarioLogado): ?>
                    <a href="perfil.html">
                        <img src="img/usercomcontorno.png" alt="Perfil">
                    </a>
                    <a href="perfil.html"><span><?php echo htmlspecialchars($primeiroNome); ?></span></a>
                <?php else: ?>
                    <a href="login.php">
                        <img src="img/usercomcontorno.png" alt="Login">
                    </a>
                    <a href="login.php"><span>Entrar</span></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<h1>Bem-vindo ao Carrinho</h1>
<p>Aqui vão os detalhes do seu carrinho de compras.</p>

</body>
</html>

<?php
// Fechar a conexão com o banco de dados
$conn->close();
?>
