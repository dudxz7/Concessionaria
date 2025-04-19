<?php
session_start(); // Inicia a sessão

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true || $_SESSION['usuarioCargo'] !== 'Admin') {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

$conn = new mysqli("localhost", "root", "", "sistema_bmw");

// Se der erro na conexão, redireciona
if ($conn->connect_error) {
    header("Location: erro_conexao.php");
    exit();
}

// Busca o cargo do usuário logado
$stmt = $conn->prepare("SELECT cargo FROM clientes WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuarioId']); // Usando o 'usuarioId' da sessão
$stmt->execute();
$stmt->bind_result($cargo);
$stmt->fetch();
$stmt->close();
$conn->close();

// Se o cargo não for 'Admin', redireciona
if ($cargo !== 'Admin') {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

$mensagem = ""; // Variável para armazenar a mensagem

// Processa o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    if (empty($email) || empty($senha)) {
        $mensagem = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "E-mail inválido.";
    } else {
        $conn = new mysqli("localhost", "root", "", "sistema_bmw");
        if ($conn->connect_error) {
            $mensagem = "Erro na conexão com o banco de dados.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 0) {
                $mensagem = "E-mail não encontrado.";
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmtUpdate = $conn->prepare("UPDATE clientes SET senha = ? WHERE email = ?");
                $stmtUpdate->bind_param("ss", $senhaHash, $email);

                if ($stmtUpdate->execute()) {
                    $mensagem = "Senha redefinida com sucesso!";
                } else {
                    $mensagem = "Erro ao redefinir a senha.";
                }
                $stmtUpdate->close();
            }
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - BMW</title>
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" href="../img/logoofcbmw.png">
    <style>
    .icone-eye {
        position: absolute;
        opacity: 0.88;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        width: 24px;
        height: 24px;
        transition: right 0.3s ease;
    }

    .back-button {
        margin-left: -15px;
        margin-top: -16px;
        text-decoration: none;
        display: flex;
        align-items: left;
        justify-content: left;
        border: none;
        cursor: pointer;
    }

    .back-button img {
        opacity: 0.87;
        width: 20px;
        height: 20px;
        transition: transform 0.3s ease-in-out;
    }

    .back-button:hover img {
        transform: scale(1.4);
    }

    .mensagem {
        font-size: 14px;
        color: #0026fd;
        text-align: left;
        top: 100%;
        left: 0;
        margin-top: -30px;
        margin-bottom: 20px;
        font-weight: bold;
        width: 100%;
    }

    .mensagem.sucesso {
        color: green;
    }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="container">
            <a href="funcoes_admin.php" class="back-button">
                <img src="../img/seta-esquerda24.png" alt="Voltar">
            </a>
            <h2>Redefinir Senha</h2>

            <form action="redefinir_senha.php" method="POST">
                <div class="campodoinput">
                    <input type="text" name="email" id="email" required spellcheck="false" autocomplete="off">
                    <label for="email">E-mail do usuário</label>
                    <img src="../img/usersemfundo.png" alt="Simbolo de usuário" class="icone-eye" id="userIcon">
                    <img src="../img/perigo.png" alt="Alerta" class="icone-alerta" id="alertEmail">
                    <span class="campo-obrigatorio">Campo obrigatório</span>
                </div>

                <div class="campodoinput">
                    <input type="password" name="senha" id="senha1" required spellcheck="false">
                    <label for="senha">Nova senha</label>
                    <img src="../img/olhofechado.png" alt="Olho fechado" class="icone-eye" id="eyeIcon">
                    <img src="../img/perigo.png" alt="Alerta" class="icone-alerta" id="alertSenha">
                    <span class="campo-obrigatorio">Campo obrigatório</span>
                </div>

                <?php if (!empty($mensagem)) : ?>
                <div class="mensagem <?php echo ($mensagem === 'Senha redefinida com sucesso!') ? 'sucesso' : ''; ?>">
                    <?php echo $mensagem; ?>
                </div>
                <?php endif; ?>

                <button class="btn" id="button" type="submit" disabled>Redefinir Senha</button>
            </form>
        </div>
    </div>

    <script src="../js/validacao-login.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const emailInput = document.getElementById("email");
        const senhaInput = document.getElementById("senha1");
        const botao = document.getElementById("button");
        const eyeIcon = document.getElementById("eyeIcon");

        eyeIcon.addEventListener("click", function() {
            if (senhaInput.type === "password") {
                senhaInput.type = "text";
                eyeIcon.src = "../img/olhoaberto.png";
            } else {
                senhaInput.type = "password";
                eyeIcon.src = "../img/olhofechado.png";
            }
        });

        function verificarCampos() {
            const emailPreenchido = emailInput.value.trim() !== "";
            const senhaPreenchida = senhaInput.value.trim() !== "";

            if (emailPreenchido && senhaPreenchida) {
                botao.disabled = false;
                botao.style.opacity = "1";
                botao.style.cursor = "pointer";
            } else {
                botao.disabled = true;
                botao.style.opacity = "0.5";
                botao.style.cursor = "not-allowed";
            }
        }

        emailInput.addEventListener("input", verificarCampos);
        senhaInput.addEventListener("input", verificarCampos);
        verificarCampos();
    });
    </script>
</body>

</html>
