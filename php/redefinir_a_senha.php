<?php
session_start();
include('../php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioId'])) {
    die("Erro: Usuário não autenticado.");
}

$idUsuario = $_SESSION['usuarioId'];

// Buscar cargo do usuário
$sql = "SELECT cargo FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$cargo = $usuario['cargo'] ?? '';

// Define o link de voltar com base no cargo
if ($cargo === "Admin") {
    $linkVoltar = "admin_dashboard.php";
} elseif (in_array($cargo, ["Funcionario", "Gerente"])) {
    $linkVoltar = "../perfil.php";
} else {
    $linkVoltar = "../perfil.php";
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if ($novaSenha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";
    } else {
        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $sql = "UPDATE clientes SET senha = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$senhaHash, $idUsuario])) {
            $_SESSION['sucesso'] = "Senha atualizada com sucesso!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $erro = "Erro ao atualizar a senha.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="../css/redefinir_a_senha.css">
    <link rel="icon" href="../img/senha.png">
</head>
<body>
    <div class="login-container">
        <div class="container">
            <a href="<?php echo $linkVoltar; ?>" class="back-button">
                <img src="../img/seta-esquerda24.png" alt="Voltar">
            </a>
            <h2>Redefinir Senha</h2>
            <form action="" method="POST">
                <div class="campodoinput">
                    <input type="password" name="senha" id="senha" required>
                    <label for="senha">Nova Senha</label>
                    <img src="../img/olhofechado.png" alt="Olho fechado" class="icone-eye" id="eyeSenha">
                </div>

                <div class="campodoinput">
                    <input type="password" name="confirmar_senha" id="confirmar_senha" required>
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <img src="../img/olhofechado.png" alt="Olho fechado" class="icone-eye" id="eyeConfirmarSenha">
                    <span id="erroConfirmarSenha" class="campo-obrigatorio">As senhas não coincidem</span>
                    
                    <?php if (isset($erro)): ?>
                        <div class="erro"><?php echo $erro; ?></div>
                    <?php elseif (isset($_SESSION['sucesso'])): ?>
                        <div class="sucesso" id="sucesso"><?php echo $_SESSION['sucesso']; ?></div>
                        <?php unset($_SESSION['sucesso']); ?>
                    <?php endif; ?>
                </div>

                <button class="btn" id="btnRedefinir" disabled>Redefinir Senha</button>
            </form>
        </div>
    </div>

    <script>
    const sucessoDiv = document.getElementById("sucesso");

    if (sucessoDiv) {
        setTimeout(() => {
            sucessoDiv.style.display = "none";
        }, 2000);
    }

    const senhaInput = document.getElementById("senha");
    const confirmarSenhaInput = document.getElementById("confirmar_senha");
    const eyeIconSenha = document.getElementById("eyeSenha");
    const eyeIconConfirmarSenha = document.getElementById("eyeConfirmarSenha");
    const erroConfirmarSenha = document.getElementById("erroConfirmarSenha");
    const btnRedefinir = document.getElementById("btnRedefinir");

    function togglePassword() {
        const isPassword = senhaInput.type === "password";
        senhaInput.type = isPassword ? "text" : "password";
        confirmarSenhaInput.type = isPassword ? "text" : "password";
        const newIcon = isPassword ? "olhoaberto.png" : "olhofechado.png";
        eyeIconSenha.src = `../img/${newIcon}`;
        eyeIconConfirmarSenha.src = `../img/${newIcon}`;
    }

    function validarSenhasEmTempoReal() {
        const senha = senhaInput.value.trim();
        const confirmarSenha = confirmarSenhaInput.value.trim();

        if (senha === "" || confirmarSenha === "") {
            erroConfirmarSenha.style.display = "none";
            btnRedefinir.disabled = true;
            btnRedefinir.style.opacity = "0.5";
            btnRedefinir.style.cursor = "not-allowed";
            return;
        }

        if (senha !== confirmarSenha) {
            erroConfirmarSenha.style.display = "block";
            btnRedefinir.disabled = true;
            btnRedefinir.style.opacity = "0.5";
            btnRedefinir.style.cursor = "not-allowed";
        } else {
            erroConfirmarSenha.style.display = "none";
            btnRedefinir.disabled = false;
            btnRedefinir.style.opacity = "1";
            btnRedefinir.style.cursor = "pointer";
        }
    }

    senhaInput.addEventListener("input", validarSenhasEmTempoReal);
    confirmarSenhaInput.addEventListener("input", validarSenhasEmTempoReal);
    eyeIconSenha.addEventListener("click", togglePassword);
    eyeIconConfirmarSenha.addEventListener("click", togglePassword);

    // Dispara a validação ao carregar a página (caso o campo já tenha algo)
    window.addEventListener("DOMContentLoaded", validarSenhasEmTempoReal);
</script>

</body>
</html>
