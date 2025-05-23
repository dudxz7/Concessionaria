<?php
session_start();
require_once 'conexao.php';

// Função para gerar uma chave única para cada tentativa de pagamento
function gerarChavePagamento($id_veiculo, $cor) {
    return 'pagamento_' . $id_veiculo . '_' . md5(strtolower($cor));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['cor'])) {
    $id = intval($_POST['id']);
    $cor = trim($_POST['cor']);
    $usuarioId = $_SESSION['usuarioId'];
    $expira_em = time() + 15 * 60; // 15 minutos
    $chave = gerarChavePagamento($id, $cor);
    
    // Remove qualquer Pix pendente anterior para o mesmo usuário/veículo/cor
    $sqlDel = "DELETE FROM pagamentos_pix_pendentes WHERE usuario_id = ? AND veiculo_id = ? AND cor = ?";
    $stmtDel = $conn->prepare($sqlDel);
    $stmtDel->bind_param("iis", $usuarioId, $id, $cor);
    $stmtDel->execute();
    $stmtDel->close();

    // Insere novo Pix pendente
    $sql = "INSERT INTO pagamentos_pix_pendentes (usuario_id, veiculo_id, cor, expira_em) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisi", $usuarioId, $id, $cor, $expira_em);
    if ($stmt->execute()) {
        $_SESSION[$chave] = [
            'expira_em' => $expira_em
        ];
        $_SESSION['pagamento_autorizado'] = true;
        $_SESSION['pagamento_id'] = $id;
        $_SESSION['pagamento_cor'] = $cor;
        echo 'ok';
        exit;
    } else {
        echo 'erro';
        exit;
    }
}
echo 'erro';
exit;
