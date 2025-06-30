<?php
// atualizar_status_venda.php
header('Content-Type: application/json');
include('conexao.php');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$tipo = isset($_POST['tipo_pagamento']) ? $_POST['tipo_pagamento'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$id || ($tipo !== 'Pix' && $tipo !== 'Boleto') || !in_array($status, ['aprovado', 'recusado', 'pendente'])) {
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
    // Busca o modelo_id do veiculo
    $sql = "SELECT modelo_id FROM veiculos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $veiculo_id);
    $stmt->execute();
    $stmt->bind_result($modelo_id);
    if (!$stmt->fetch() || !$modelo_id) {
        $stmt->close();
        echo json_encode(['success' => false, 'error' => 'Veículo não encontrado para a venda. Não foi possível atualizar o status.']);
        exit;
    }
    $stmt->close();

    if ($status === 'aprovado' && $status_atual !== 'aprovado') {
        // Marca como vendido o veículo disponível de menor id desse modelo
        $sql = "SELECT id FROM veiculos WHERE modelo_id = ? AND (status = 'disponivel' OR status IS NULL) ORDER BY id ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $modelo_id);
        $stmt->execute();
        $stmt->bind_result($id_vendido);
        if ($stmt->fetch()) {
            $stmt->close();
            // Atualiza status, id_pagamento e tipo_pagamento
            $sql = "UPDATE veiculos SET status = 'vendido', id_pagamento = ?, tipo_pagamento = ? WHERE id = ?";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param('isi', $id, $tipo, $id_vendido);
            $stmt2->execute();
            $stmt2->close();
            // Recusa todas as outras tentativas pendentes para o mesmo veiculo/modelo
            $ids_recusados = [];
            // Pix
            $sql = "UPDATE pagamentos_pix SET status = 'recusado' WHERE veiculo_id = ? AND id != ? AND status = 'pendente'";
            $stmt3 = $conn->prepare($sql);
            $stmt3->bind_param('ii', $id_vendido, $id);
            $stmt3->execute();
            $stmt3->close();
            $sql = "SELECT id FROM pagamentos_pix WHERE veiculo_id = ? AND id != ? AND status = 'recusado'";
            $stmt4 = $conn->prepare($sql);
            $stmt4->bind_param('ii', $id_vendido, $id);
            $stmt4->execute();
            $stmt4->bind_result($id_recusado_pix);
            while ($stmt4->fetch()) {
                $ids_recusados[] = ['id' => $id_recusado_pix, 'tipo' => 'Pix'];
            }
            $stmt4->close();
            // Boleto
            $sql = "UPDATE pagamento_boleto SET status = 'recusado' WHERE veiculo_id = ? AND id != ? AND status = 'pendente'";
            $stmt5 = $conn->prepare($sql);
            $stmt5->bind_param('ii', $id_vendido, $id);
            $stmt5->execute();
            $stmt5->close();
            $sql = "SELECT id FROM pagamento_boleto WHERE veiculo_id = ? AND id != ? AND status = 'recusado'";
            $stmt6 = $conn->prepare($sql);
            $stmt6->bind_param('ii', $id_vendido, $id);
            $stmt6->execute();
            $stmt6->bind_result($id_recusado_boleto);
            while ($stmt6->fetch()) {
                $ids_recusados[] = ['id' => $id_recusado_boleto, 'tipo' => 'Boleto'];
            }
            $stmt6->close();
        } else {
            $stmt->close();
        }
    }
    // Se mudou de aprovado para outro status (recusado ou pendente), libera o veículo vendido de menor id desse modelo
    if ($status_atual === 'aprovado' && $status !== 'aprovado') {
        $sql = "SELECT id FROM veiculos WHERE modelo_id = ? AND status = 'vendido' ORDER BY id ASC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $modelo_id);
        $stmt->execute();
        $stmt->bind_result($id_disp);
        if ($stmt->fetch()) {
            $stmt->close();
            $sql = "UPDATE veiculos SET status = 'disponivel' WHERE id = ?";
            $stmt2 = $conn->prepare($sql);
            $stmt2->bind_param('i', $id_disp);
            $stmt2->execute();
            $stmt2->close();
        } else {
            $stmt->close();
        }
    }
    // Busca o novo valor do estoque (veículos disponíveis desse modelo)
    $sql = "SELECT COUNT(*) FROM veiculos WHERE modelo_id = ? AND (status = 'disponivel' OR status IS NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $modelo_id);
    $stmt->execute();
    $stmt->bind_result($novoEstoque);
    $stmt->fetch();
    $stmt->close();
}

echo json_encode(['success' => true, 'estoque' => $novoEstoque, 'ids_recusados' => $ids_recusados]);
