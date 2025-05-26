<?php
session_start();
// Variáveis globais para navbar
$usuarioLogado = isset($_SESSION['usuarioLogado']) && $_SESSION['usuarioLogado'] === true;
$nomeUsuario = "";
if ($usuarioLogado && isset($_SESSION['usuarioNome'])) {
    $nomes = explode(" ", trim($_SESSION['usuarioNome']));
    $primeiroNome = $nomes[0] ?? "";
    $nomeUsuario = $primeiroNome;
}
$linkPerfil = 'perfil.php';
if ($usuarioLogado && isset($_SESSION['usuarioAdmin']) && $_SESSION['usuarioAdmin'] == 1) {
    $linkPerfil = 'php/admin_dashboard.php';
}
$capitais = [
    "AC" => "Rio Branco",
    "AL" => "Maceió",
    "AM" => "Manaus",
    "AP" => "Macapá",
    "BA" => "Salvador",
    "CE" => "Fortaleza",
    "DF" => "Brasília",
    "ES" => "Vitória",
    "GO" => "Goiânia",
    "MA" => "São Luís",
    "MG" => "Belo Horizonte",
    "MS" => "Campo Grande",
    "MT" => "Cuiabá",
    "PA" => "Belém",
    "PB" => "João Pessoa",
    "PE" => "Recife",
    "PI" => "Teresina",
    "PR" => "Curitiba",
    "RJ" => "Rio de Janeiro",
    "RN" => "Natal",
    "RO" => "Porto Velho",
    "RR" => "Boa Vista",
    "RS" => "Porto Alegre",
    "SC" => "Florianópolis",
    "SE" => "Aracaju",
    "SP" => "São Paulo",
    "TO" => "Palmas"
];
$estado = isset($_SESSION['usuarioEstado']) ? $_SESSION['usuarioEstado'] : "";
$capital = isset($capitais[$estado]) ? $capitais[$estado] . " - " . $estado : "Cidade - Estado";
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas as Ofertas | BMW</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/visualizar-modelos.css">
    <link rel="icon" href="img/logos/logoofcbmw.png">
    <script src="js/favoritar.js" defer></script>
    <script src="js/navbar-favoritos.js" defer></script>
    <script src="js/favoritar-card.js" defer></script>
    <style>
        .navbar {
            background-color: black;
            z-index: 11;
        }
    </style>
</head>
<body>
    <?php include 'php/navbar.php'; ?>
    <div class="main-content" style="padding: 40px 0 60px 0; min-height: 60vh;">
        <h1
            style="text-align:center; font-family: 'Clash Display', 'Inter', Arial, sans-serif; font-size:2.5rem; margin-bottom: 32px; margin-top: 80px;">
            Todas as Ofertas</h1>
        <div class="cards-simples-grid">
            <?php include 'php/cards-simples-promocoes.php'; ?>
        </div>
    </div>
    <?php include 'php/footer.php'; ?>
    <script src="js/navbar-favoritos.js" defer></script>
    <script src="js/favoritar-card.js" defer></script>
</body>
</html>