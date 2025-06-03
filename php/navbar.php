<?php
require_once('conexao.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$favoritoCount = 0;

if (isset($_SESSION['usuarioId'])) {
    $usuarioId = $_SESSION['usuarioId'];


    $stmt = $conn->prepare("SELECT COUNT(*) FROM favoritos WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $stmt->bind_result($count);
    if ($stmt->fetch()) {
        $favoritoCount = $count;
    }
    $stmt->close();
}
?>

<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="img/logos/logoofcbmw.png" alt="Logo BMW">
        </a>
        <a href="index.php" id="textlogo">BMW</a>
    </div>

    <div class="location">
        <img src="img/navbar/pin-de-localizacao.png" alt="Ícone de localização">
        <div class="location-text">
            <span>Pesquisando ofertas em</span>
            <u><strong id="user-location"><?php echo htmlspecialchars($capital); ?> e Região</strong></u>
        </div>
    </div>

    <div class="nav-icons">
        <div class="heart-container" style="position: relative; display: inline-block; cursor:pointer;" onclick="window.location.href='php/favoritos.php'">
            <img src="img/navbar/heart.png" class="heart-icon-navbar" alt="Favoritos">
            <span class="heart-counter <?php echo ($favoritoCount == 0) ? 'oculto' : ''; ?>"><?php echo $favoritoCount; ?></span>
        </div>

        <div class="login">
            <?php
            // Recupera a foto de perfil do usuário logado
            $fotoPerfilNavbar = null;
            if (isset($_SESSION['usuarioId'])) {
                $usuarioId = $_SESSION['usuarioId'];
                $stmtFoto = $conn->prepare("SELECT foto_perfil FROM clientes WHERE id = ?");
                $stmtFoto->bind_param("i", $usuarioId);
                $stmtFoto->execute();
                $stmtFoto->bind_result($fotoPerfilTmp);
                if ($stmtFoto->fetch() && !empty($fotoPerfilTmp)) {
                    $fotoPerfilNavbar = $fotoPerfilTmp;
                }
                $stmtFoto->close();
            }
            ?>
            <?php if ($usuarioLogado): ?>
                <!-- Se o usuário estiver logado, mostra o nome -->
                <a href="<?php echo $linkPerfil; ?>">
                    <?php if (!empty($fotoPerfilNavbar) && file_exists(__DIR__ . '/../' . $fotoPerfilNavbar)): ?>
                        <img src="<?php echo $fotoPerfilNavbar; ?>" alt="Perfil" style="width:32px;height:32px;object-fit:cover;border-radius:50%;vertical-align:middle;" />
                    <?php else: ?>
                        <img src="img/navbar/usercomcontorno.png" alt="Perfil">
                    <?php endif; ?>
                </a>
                <a href="<?php echo $linkPerfil; ?>"><span><?php echo htmlspecialchars($nomeUsuario); ?></span></a>
            <?php else: ?>
                <!-- Se não estiver logado, mostra o link para login -->
                <a href="login.html">
                    <img src="img/navbar/usercomcontorno.png" alt="Login">
                </a>
                <a href="login.html"><span>Entrar</span></a>
            <?php endif; ?>
        </div>
    </div>
</nav>