<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php';

// Limpa pagamentos Pix expirados (boa prática)
$conn->query("DELETE FROM pagamentos_pix WHERE expira_em <= NOW() AND status = 'pendente'");

// Função para gerar uma chave única para cada tentativa de pagamento
function gerarChavePagamento($id_veiculo, $cor, $usuarioId) {
    return 'pagamento_' . $usuarioId . '_' . $id_veiculo . '_' . md5(strtolower($cor));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['cor'])) {
    $id = intval($_POST['id']);
    $cor = trim($_POST['cor']);
    $usuarioId = $_SESSION['usuarioId'];
    $criado_em = date('Y-m-d H:i:s');
    $expira_em = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    $expira_em_timestamp = strtotime($expira_em); // Salva como timestamp
    $chave = gerarChavePagamento($id, $cor, $usuarioId);
    
    // Remove qualquer Pix pendente anterior para o mesmo usuário/veículo/cor
    $sqlDel = "DELETE FROM pagamentos_pix WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente'";
    $stmtDel = $conn->prepare($sqlDel);
    $stmtDel->bind_param("iis", $usuarioId, $id, $cor);
    $stmtDel->execute();
    $stmtDel->close();

    // Busca o valor do veículo
    $valor_pix = null;
    $sqlPreco = "SELECT preco FROM modelos WHERE id = ? LIMIT 1";
    $stmtPreco = $conn->prepare($sqlPreco);
    $stmtPreco->bind_param("i", $id);
    $stmtPreco->execute();
    $stmtPreco->bind_result($valor_pix);
    $stmtPreco->fetch();
    $stmtPreco->close();

    // Insere novo Pix pendente
    $sql = "INSERT INTO pagamentos_pix (usuario_id, veiculo_id, cor, criado_em, expira_em, status, valor, forma_pagamento) VALUES (?, ?, ?, ?, ?, 'pendente', ?, 'pix')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssd", $usuarioId, $id, $cor, $criado_em, $expira_em, $valor_pix);
    if ($stmt->execute()) {
        $_SESSION[$chave] = [
            'expira_em' => $expira_em_timestamp
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