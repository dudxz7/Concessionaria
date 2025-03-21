<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: login.php");
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
$sql = "SELECT nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, cargo FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($nome_completo, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $cargo);
    $stmt->fetch();
} else {
    echo "Erro ao recuperar os dados!";
    exit;
}

// Verificando se houve atualização no perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoEstado = $_POST['estado'];
    $novaCidade = $_POST['cidade'];

    // Atualizar os dados no banco de dados
    $updateSql = "UPDATE clientes SET estado = ?, cidade = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssi", $novoEstado, $novaCidade, $id);
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
                <source src="img/particulaazul.mp4" type="video/mp4">
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
                <div class="icon-item" onclick="window.location.href='esquecer_senha.php'">
                    <img src="img/ajudando.png" alt="Esqueceu a Senha">
                    <span>Esqueceu a Senha</span>
                </div>
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
                        <div class="input-container">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>" maxlength="2" required>
                        </div>
                        <div class="input-container">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo $cidade; ?>" maxlength="28" required>
                        </div>
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

    <script>
        // Função para bloquear a digitação de números e transformar em maiúsculas
        function bloquearNumeros(event) {
            const key = event.key;
            if (/\d/.test(key)) {
                event.preventDefault();
            }
        }

        // Função para transformar o texto digitado em maiúsculas
        function transformarMaiusculas(event) {
            event.target.value = event.target.value.toUpperCase();
        }

        // Adiciona os eventos nos campos de Estado e Cidade
        document.getElementById("estado").addEventListener("keydown", bloquearNumeros);
        document.getElementById("cidade").addEventListener("keydown", bloquearNumeros);
        document.getElementById("estado").addEventListener("input", transformarMaiusculas);
        
        // Monitorando os campos de estado e cidade para habilitar o botão
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('#estado, #cidade');
            const salvarBtn = document.querySelector('.salvar-btn');

            // Função para verificar se algum campo foi alterado
            function verificarCampos() {
                let algumCampoAlterado = false;

                // Verificando se algum dos campos de estado ou cidade foi alterado
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        algumCampoAlterado = true;
                    }
                });

                // Se algum campo foi alterado, ativa o botão
                if (algumCampoAlterado) {
                    salvarBtn.classList.add('enabled');
                    salvarBtn.disabled = false;
                } else {
                    salvarBtn.classList.remove('enabled');
                    salvarBtn.disabled = true;
                }
            }

            // Verifica os campos sempre que o usuário digitar algo
            inputs.forEach(input => {
                input.addEventListener('input', verificarCampos);
            });
        });
    </script>

</body>
</html>
