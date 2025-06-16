<?php
// atualizar_status_venda.php
header('Content-Type: application/json');
include('conexao.php');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$tipo = isset($_POST['tipo_pagamento']) ? $_POST['tipo_pagamento'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id || ($tipo !== 'Pix' && $tipo !== 'Boleto') || ($status !== 'aprovado' && $status !== 'recusado')) {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
    exit;
}

// Busca o status e veiculo_id atual
if ($tipo === 'Pix') {
    $sql = "SELECT status, veiculo_id FROM pagamentos_pix WHERE id = ?";
} else {
    $sql = "SELECT status, veiculo_id FROM pagamento_boleto WHERE id = ?";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($status_atual, $veiculo_id);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Registro não encontrado']);
    exit;
}
$stmt->close();

// Atualiza o status
if ($tipo === 'Pix') {
    $sql = "UPDATE pagamentos_pix SET status = ? WHERE id = ?";
} else {
    $sql = "UPDATE pagamento_boleto SET status = ? WHERE id = ?";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $status, $id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'error' => 'Falha ao atualizar status']);
    exit;
}

// Atualiza o estoque
$novoEstoque = null;
if ($status_atual !== $status) {
    // Se mudou para aprovado, diminui estoque
    if ($status === 'aprovado' && $status_atual !== 'aprovado') {
        $sql = "UPDATE estoque SET quantidade = GREATEST(quantidade-1,0) WHERE veiculo_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $veiculo_id);
        $stmt->execute();
        $stmt->close();
    }
    // Se mudou de aprovado para outro status, aumenta estoque
    if ($status_atual === 'aprovado' && $status !== 'aprovado') {
        $sql = "UPDATE estoque SET quantidade = quantidade+1 WHERE veiculo_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $veiculo_id);
        $stmt->execute();
        $stmt->close();
    }
    // Busca o novo valor do estoque
    $sql = "SELECT quantidade FROM estoque WHERE veiculo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $veiculo_id);
    $stmt->execute();
    $stmt->bind_result($novoEstoque);
    $stmt->fetch();
    $stmt->close();
}

echo json_encode(['success' => true, 'estoque' => $novoEstoque]);
