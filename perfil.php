<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: login.html");
    exit;
}

// Conectar com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar os dados do usuário
$id = $_SESSION['usuarioId'];
$sql = "SELECT nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, cargo, endereco, pis FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($nome_completo, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $cargo, $endereco, $pis);
    $stmt->fetch();
} else {
    echo "Erro ao recuperar os dados!";
    exit;
}

// Verificando se houve atualização no perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoEstado = $_POST['estado'];
    $novaCidade = $_POST['cidade'];
    $novoEndereco = $_POST['endereco'];

    // Atualizar os dados no banco de dados
    $updateSql = "UPDATE clientes SET estado = ?, cidade = ?, endereco = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssi", $novoEstado, $novaCidade, $novoEndereco, $id);
    $updateStmt->execute();

    // Atualizando a sessão com os novos dados
    $_SESSION['usuarioEstado'] = $novoEstado;
    $_SESSION['usuarioCidade'] = $novaCidade;

    // Definir mensagem de sucesso na sessão
    $_SESSION['mensagemSucesso'] = "Dados atualizados com sucesso!";

    // Redirecionando para atualizar os dados na página
    header("Location: perfil.php");
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil BMW</title>
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="videos/overlay_azul.mp4" type="video/mp4">
                Seu navegador não suporta vídeos.
            </video>
            <div class="profile-icon"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></div>
            <p><strong><?php echo $nome_completo; ?></strong></p>
            <p><?php echo $email; ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='perfil.php'">
                    <img src="img/usersembarra.png" alt="Minha Conta">
                    <span>Minha conta</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/esquecer_senha.php'">
                    <img src="img/ajudando.png" alt="Esqueceu a Senha">
                    <span>Esqueceu a Senha</span>
                </div>
                <?php if ($cargo !== 'Cliente' ): ?>
                <div class="icon-item" onclick="window.location.href='php/consultar_clientes.php'">
                    <img src="img/lupa.png" alt="Consultar clientes">
                    <span>Consultar Clientes</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/consultar_modelos.php'">
                    <img src="img/referencia.png" alt="Consultar Modelos">
                    <span>Consultar Modelos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/consultar_veiculos.php'">
                    <img src="img/carro_de_frente.png" alt="Consultar Veículos">
                    <span>Consultar Veículos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/consultar_promocoes.php'">
                    <img src="img/promocoes.png" alt="Consultar promoções">
                    <span>Consultar Promoções</span>
                </div>
                <?php endif; ?>
                <div class="icon-item" onclick="window.location.href='php/logout.php'">
                    <img src="img/sairr.png" alt="Sair">
                    <span>Sair</span>
                </div>
            </div>
        </div>
        <div class="content">
            <!-- Imagem da seta para voltar -->
            <a href="index.php" class="back-button">
                <img src="img/seta-esquerdabranca.png" alt="Voltar">
            </a>

            <h2>Meus dados</h2>
            <p id="descricao">Campos com (*) não podem ser alterados</p>
            
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="left-column">
                        <div class="input-container">
                            <label for="nome">Nome*</label>
                            <input type="text" id="nome" value="<?php echo $nome_completo; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="email">Email*</label>
                            <input type="email" id="email" value="<?php echo $email; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="cpf">CPF*</label>
                            <input type="text" id="cpf" value="<?php echo $cpf; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="rg">RG*</label>
                            <input type="text" id="rg" value="<?php echo $rg; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="cnh">CNH*</label>
                            <input type="text" id="cnh" value="<?php echo $cnh; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="telefone">Telefone*</label>
                            <input type="text" id="telefone" value="<?php echo $telefone; ?>" readonly class="com-asterisco">
                        </div>
                    </div>
                    <div class="right-column">
                        <div class="input-container">
                            <label for="cargo">Cargo*</label>
                            <input type="text" id="cargo" value="<?php echo $cargo; ?>" readonly class="com-asterisco">
                        </div>
                        <?php if ($cargo !== 'Cliente'): ?>
                        <div class="input-container">
                            <label for="pis">Pis*</label>
                            <input type="text" id="pis" name="pis" value="<?php echo $pis; ?>" readonly class="com-asterisco">
                        </div>
                        <?php endif; ?>
                        <div class="input-container">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>" maxlength="2" required>
                        </div>
                        <div class="input-container">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo $cidade; ?>" maxlength="28" required>
                        </div>
                        <?php if ($cargo !== 'Cliente'): ?>
                        <div class="input-container">
                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo $endereco; ?>" maxlength="100">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mensagem de sucesso (se presente) -->
                <?php if (isset($_SESSION['mensagemSucesso'])): ?>
                    <div class="alert-success">
                        <?php
                        echo $_SESSION['mensagemSucesso'];
                        unset($_SESSION['mensagemSucesso']); // Limpa a mensagem após exibição
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Botão de salvar alterações -->
                <div class="button-container">
                    <button type="submit" class="salvar-btn">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/function-perfil.js"></script>

</body>
</html>
