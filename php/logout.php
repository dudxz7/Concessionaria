<?php
// Iniciar a sessão
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Adicionar um pequeno delay para a transição antes de redirecionar
header("Refresh: 2; URL=../index.php");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desconectando...</title>
    <link rel="stylesheet" href="../css/carregando.css">
    <link rel="icon" href="../img/logos/logout-saindo.png">
</head>
<body>
    <div class="logout-container">
        <h2>Você foi desconectado com sucesso!</h2>
        <p>Você será redirecionado para a página inicial...</p>

        <!-- Spinner de carregamento -->
        <div class="dot-spinner">
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
            <div class="dot-spinner__dot"></div>
        </div>
    </div>
</body>
</html>
