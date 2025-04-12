<?php
require_once("../php/conexao.php");
session_start();

$erro = "";

// Verifica se o ID foi passado
if (!isset($_GET['id']) && !isset($_POST['id'])) {
    echo "ID do funcionário não fornecido.";
    exit;
}

$id = $_GET['id'] ?? $_POST['id'];

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $pis = $_POST['pis'];
    $endereco = $_POST['endereco'];
    $cpf = $_POST['cpf'];
    $rg = $_POST['rg'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $telefone = $_POST['telefone'];
    $cnh = $_POST['cnh'];

    // Verifica se existe outro com o mesmo PIS
    if (!empty($pis)) {
        $sqlVerificaPIS = "SELECT id FROM clientes WHERE pis = ? AND id != ?";
        $stmtVerificaPIS = $conn->prepare($sqlVerificaPIS);
        $stmtVerificaPIS->bind_param("si", $pis, $id);
        $stmtVerificaPIS->execute();
        $resultVerificaPIS = $stmtVerificaPIS->get_result();

        if ($resultVerificaPIS->num_rows > 0) {
            $erro = "Já existe um funcionário com este PIS.";
        }
    }

    // Verifica se existe outro com o mesmo CPF
    if (empty($erro)) {
        $sqlVerificaCPF = "SELECT id FROM clientes WHERE cpf = ? AND id != ?";
        $stmtVerificaCPF = $conn->prepare($sqlVerificaCPF);
        $stmtVerificaCPF->bind_param("si", $cpf, $id);
        $stmtVerificaCPF->execute();
        $resultVerificaCPF = $stmtVerificaCPF->get_result();

        if ($resultVerificaCPF->num_rows > 0) {
            $erro = "Já existe um funcionário com este CPF.";
        }
    }

    // Atualiza os dados se não houver erro
    if (empty($erro)) {
        $sqlUpdate = "UPDATE clientes SET nome_completo=?, email=?, pis=?, endereco=?, cpf=?, rg=?, cidade=?, estado=?, telefone=?, cnh=? WHERE id=?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssssssssssi", $nome, $email, $pis, $endereco, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $id);

        if ($stmtUpdate->execute()) {
            header("Location: consultar_func_gerente.php");
            exit;
        } else {
            $erro = "Erro ao atualizar os dados.";
        }
    }
}

// Carrega os dados do funcionário (após possível POST também)
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Funcionário não encontrado.";
    exit;
}

$dados = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Funcionário</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
<div class="container">
    <a href="consultar_func_gerente.php" class="back-button">
        <img src="../img/seta-esquerda24.png" alt="Voltar">
    </a>
    <h2>Editar Funcionário</h2>

    <form action="editar_funcionario.php" method="post">
        <input type="hidden" name="id" value="<?= $dados['id'] ?>">

        <div class="input-group">
            <label>Nome completo</label>
            <div class="input-wrapper">
                <input type="text" name="nome" value="<?= $dados['nome_completo'] ?? '' ?>" required>
                <img src="../img/usersemfundo.png" alt="Ícone usuário">
            </div>
        </div>

        <div class="input-group">
            <label>E-mail</label>
            <div class="input-wrapper">
                <input type="email" name="email" value="<?= $dados['email'] ?? '' ?>" required>
                <img src="../img/e-mail.png" alt="Ícone e-mail">
            </div>
        </div>

        <div id="camposExtras">
            <div class="input-group">
                <label>PIS</label>
                <div class="input-wrapper">
                    <input type="text" name="pis" value="<?= $dados['pis'] ?? '' ?>" maxlength="11">
                    <img src="../img/arquivo.png" alt="Ícone PIS">
                </div>
            </div>

            <div class="input-group">
                <label>Endereço</label>
                <div class="input-wrapper">
                    <input type="text" name="endereco" value="<?= $dados['endereco'] ?? '' ?>">
                    <img src="../img/lugar-colocar.png" alt="Ícone endereço">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="input-group">
                <label>CPF</label>
                <div class="input-wrapper">
                    <input type="text" name="cpf" value="<?= $dados['cpf'] ?? '' ?>" required>
                    <img src="../img/arquivo.png" alt="Ícone CPF">
                </div>
            </div>
            <div class="input-group">
                <label>RG</label>
                <div class="input-wrapper">
                    <input type="text" name="rg" value="<?= $dados['rg'] ?? '' ?>" required>
                    <img src="../img/cartaozin.png" alt="Ícone RG">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="input-group">
                <label>Cidade</label>
                <div class="input-wrapper">
                    <input type="text" name="cidade" value="<?= $dados['cidade'] ?? '' ?>" required>
                    <img src="../img/construcao-da-cidade.png" alt="Ícone cidade">
                </div>
            </div>
            <div class="input-group">
                <label>Estado</label>
                <div class="input-wrapper">
                    <input type="text" name="estado" value="<?= $dados['estado'] ?? '' ?>" maxlength="2" required>
                    <img src="../img/lugar-colocar.png" alt="Ícone estado">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="input-group">
                <label>Telefone</label>
                <div class="input-wrapper">
                    <input type="tel" name="telefone" value="<?= $dados['telefone'] ?? '' ?>" required>
                    <img src="../img/telefone.png" alt="Ícone telefone">
                </div>
            </div>
            <div class="input-group">
                <label>CNH</label>
                <div class="input-wrapper">
                    <input type="text" name="cnh" value="<?= $dados['cnh'] ?? '' ?>">
                    <img src="../img/carteira-de-motorista.png" alt="Ícone CNH">
                </div>
            </div>
        </div>

        <?php if (!empty($erro)): ?>
            <div style="color: red; margin-top: 10px;"><?= $erro ?></div>
        <?php endif; ?>

        <button type="submit" class="btn">
            <img src="../img/verifica.png" alt="Ícone de check">
            Atualizar Dados
        </button>
    </form>
</div>
</body>
</html>
