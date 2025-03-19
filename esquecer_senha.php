<?php
session_start();
include('php/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioId'])) {
    die("Erro: Usuário não autenticado.");
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmar_senha'];
    $idUsuario = $_SESSION['usuarioId']; // Agora temos certeza que a sessão tem esse valor

    if ($novaSenha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";
    } else {
        $senhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $sql = "UPDATE clientes SET senha = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt->execute([$senhaHash, $idUsuario])) {
            $_SESSION['sucesso'] = "Senha atualizada com sucesso!";
            header("Location: " . $_SERVER['PHP_SELF']); // Redireciona para a mesma página para limpar a mensagem após o refresh
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
    <link rel="stylesheet" href="css/esquecer_a_senha.css">
    <link rel="icon" href="img/logoofcbmw.png">
</head>
<body>
    <div class="login-container">
        <div class="container">
            <h2>Redefinir Senha</h2>

            

            <!-- Formulário para redefinir senha -->
            <form action="" method="POST">
                <div class="campodoinput">
                    <input type="password" name="senha" id="senha" required>
                    <label for="senha">Nova Senha</label>
                    <img src="img/olhofechado.png" alt="Olho fechado" class="icone-eye" id="eyeSenha">
                </div>

                <div class="campodoinput">
                    <input type="password" name="confirmar_senha" id="confirmar_senha" required>
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <img src="img/olhofechado.png" alt="Olho fechado" class="icone-eye" id="eyeConfirmarSenha">
                    <!-- Mensagem de erro -->
                    <span id="erroConfirmarSenha" class="campo-obrigatorio">As senhas não coincidem</span>
                    <!-- Mensagens de erro ou sucesso -->
                    <?php if (isset($erro)): ?>
                        <div class="erro"><?php echo $erro; ?></div>
                    <?php elseif (isset($_SESSION['sucesso'])): ?>
                    <div class="sucesso" id="sucesso"><?php echo $_SESSION['sucesso']; ?></div>
                    <?php unset($_SESSION['sucesso']); ?> <!-- Limpa a variável de sessão após mostrar a mensagem -->
                    <?php endif; ?>
                </div>

                <!-- Botão só será ativado quando as senhas coincidirem -->
                <button class="btn" id="btnRedefinir" disabled>Redefinir Senha</button>
            </form>
        </div>
    </div>

    <script>
    // Referência para a div de sucesso
    const sucessoDiv = document.getElementById("sucesso");

    // Função para esconder a mensagem de sucesso após 2 segundos
    if (sucessoDiv) {
        setTimeout(() => {
            sucessoDiv.style.display = "none";  // Esconde a div após 2 segundos
        }, 2000);  // 2000 milissegundos = 2 segundos
    }

    // Referências para os campos de senha e os ícones de olho 
    const senhaInput = document.getElementById("senha");
    const confirmarSenhaInput = document.getElementById("confirmar_senha");
    const eyeIconSenha = document.getElementById("eyeSenha");
    const eyeIconConfirmarSenha = document.getElementById("eyeConfirmarSenha");
    const erroConfirmarSenha = document.getElementById("erroConfirmarSenha");
    const btnRedefinir = document.getElementById("btnRedefinir");

    // Função para alternar entre mostrar/ocultar as senhas
    function togglePassword() {
        const isPassword = senhaInput.type === "password";

        // Alterna o tipo dos inputs
        senhaInput.type = isPassword ? "text" : "password";
        confirmarSenhaInput.type = isPassword ? "text" : "password";

        // Alterna os ícones
        const newIcon = isPassword ? "olhoaberto.png" : "olhofechado.png";
        eyeIconSenha.src = `img/${newIcon}`;
        eyeIconConfirmarSenha.src = `img/${newIcon}`;
    }

    // Função para verificar se as senhas são iguais
    function validarSenhasEmTempoReal() {
        if (senhaInput.value !== confirmarSenhaInput.value) {
            erroConfirmarSenha.style.display = "block"; // Exibe a mensagem de erro
            btnRedefinir.disabled = true; // Desabilita o botão
            btnRedefinir.style.opacity = "0.5"; // Define a opacidade para 50%
        } else {
            erroConfirmarSenha.style.display = "none"; // Esconde a mensagem de erro
            btnRedefinir.disabled = false; // Habilita o botão
            btnRedefinir.style.opacity = "1"; // Restaura a opacidade para 100%
        }
    }

    // Adiciona o evento de input para validar enquanto o usuário digita
    senhaInput.addEventListener("input", validarSenhasEmTempoReal);
    confirmarSenhaInput.addEventListener("input", validarSenhasEmTempoReal);

    // Adiciona evento de clique para os ícones de olho (ambos ativam a mesma função)
    eyeIconSenha.addEventListener("click", togglePassword);
    eyeIconConfirmarSenha.addEventListener("click", togglePassword);
    </script>
</body>
</html>
