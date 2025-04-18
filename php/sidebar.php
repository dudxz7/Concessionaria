<?php
// Iniciar a sessão (certifique-se de que a sessão não foi iniciada anteriormente)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pega os dados da sessão com segurança
$cargo_usuario = $_SESSION['usuarioCargo'] ?? 'Cliente'; // Se o cargo não estiver setado, assume 'Cliente'

// Variáveis globais da sidebar
$nome_completo = $_SESSION['usuarioNome'] ?? 'Usuário';
$email = $_SESSION['usuarioEmail'] ?? '';
?>

<div class="sidebar">
    <?php if ($cargo_usuario === 'Admin'): ?>
        <video autoplay loop muted>
            <source src="../videos/overlay_branca.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        <div class="profile-icon"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></div>
        <p><strong><?php echo htmlspecialchars($nome_completo); ?></strong></p>
        <p><?php echo htmlspecialchars($email); ?></p>

        <div class="icons">
            <div class="icon-item" onclick="window.location.href='admin_dashboard.php'">
                <img src="../img/casa.png" alt="Dashboard">
                <span>Dashboard</span>
            </div>
            <div class="icon-item" onclick="window.location.href='cadastro_admin.php'">
                <img src="../img/novo-usuario.png" alt="Cadastro do admin">
                <span>Cadastrar</span>
            </div>
            <div class="icon-item" onclick="window.location.href='funcoes_admin.php'">
                <img src="../img/referencia.png" alt="Funções">
                <span>Funções</span>
            </div>
            <div class="icon-item" onclick="window.location.href='esquecer_senha.php'">
                <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                <span>Esqueceu a Senha</span>
            </div>
            <div class="icon-item" onclick="window.location.href='logout.php'">
                <img src="../img/sairr.png" alt="Sair">
                <span>Sair</span>
            </div>
        </div>

    <?php else: ?>
        <video autoplay loop muted>
            <source src="../videos/overlay_azul.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        <div class="profile-icon"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></div>
        <p><strong><?php echo htmlspecialchars($nome_completo); ?></strong></p>
        <p><?php echo htmlspecialchars($email); ?></p>

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
            <?php if ($cargo_usuario === 'Gerente' || $cargo_usuario === 'Admin'): ?>
            <div class="icon-item" onclick="window.location.href='consultar_func_gerente.php'">
                <img src="../img/homem-de-negocios.png" alt="Consultar Funcionários e Gerentes">
                <span>Consultar Funcionários</span>
            </div>
            <?php endif; ?>
            <div class="icon-item" onclick="window.location.href='consultar_modelos.php'">
                <img src="../img/referencia.png" alt="Consultar Modelos">
                <span>Consultar Modelos</span>
            </div>
            <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'">
                <img src="../img/carro_de_frente.png" alt="Consultar Veículos">
                <span>Consultar Veículos</span>
            </div>
            <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'">
                <img src="../img/promocoes.png" alt="Consultar promoções">
                <span>Consultar Promoções</span>
            </div>
            <div class="icon-item" onclick="window.location.href='logout.php'">
                <img src="../img/sairr.png" alt="Sair">
                <span>Sair</span>
            </div>
        </div>
    <?php endif; ?>
</div>
