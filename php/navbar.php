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
                    <a href="carrinho.php">
                        <img src="img/navbar/heart.png" alt="Favoritos" class="heart-icon-navbar">
                    </a>
                    <div class="login">
                        <?php if ($usuarioLogado): ?>
                            <!-- Se o usuário estiver logado, mostra o nome -->
                            <a href="<?php echo $linkPerfil; ?>">
                                <img src="img/navbar/usercomcontorno.png" alt="Perfil">
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