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
    <title>Logout | BMW Motors</title>
    <link rel="icon" href="../img/logout-saindo.png">
    <link rel="stylesheet" href="../css/logout.css">
</head>
<body>
    <div class="logout-container">
        <div class="bmw-cyber-loader">
            <svg viewBox="0 0 120 120">
                <defs>
                    <linearGradient id="bmwBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#00b4d8"/>
                        <stop offset="100%" stop-color="#0077b6"/>
                    </linearGradient>
                    <linearGradient id="bmwWhite" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#fff"/>
                        <stop offset="100%" stop-color="#b3c6ff"/>
                    </linearGradient>
                </defs>
                <circle cx="60" cy="60" r="48" stroke="url(#bmwBlue)" stroke-width="8" fill="none" stroke-dasharray="60 30 20 50" stroke-dashoffset="0">
                    <animateTransform attributeName="transform" type="rotate" from="0 60 60" to="360 60 60" dur="1.2s" repeatCount="indefinite"/>
                </circle>
                <circle cx="60" cy="60" r="36" stroke="url(#bmwWhite)" stroke-width="4" fill="none" stroke-dasharray="40 20 10 30" stroke-dashoffset="0">
                    <animateTransform attributeName="transform" type="rotate" from="360 60 60" to="0 60 60" dur="1.7s" repeatCount="indefinite"/>
                </circle>
            </svg>
            <span class="bmw-logo-center">
                <img src="../img/logos/logoofcbmw.png" alt="BMW" />
            </span>
        </div>
        <div class="logout-redirect">Redirecionando...</div>
    </div>
</body>
</html>
