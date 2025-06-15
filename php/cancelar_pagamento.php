<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$cor = isset($_POST['cor']) ? trim($_POST['cor']) : '';
$usuarioId = $_SESSION['usuarioId'] ?? 0;

if (!$id || !$cor || !$usuarioId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos.']);
    exit;
}

// Tenta cancelar Pix primeiro
$sqlPix = "UPDATE pagamentos_pix SET status = 'cancelado' WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente'";
$stmtPix = $conn->prepare($sqlPix);
$stmtPix->bind_param('iis', $usuarioId, $id, $cor);
$stmtPix->execute();
$cancelouPix = $stmtPix->affected_rows > 0;
$stmtPix->close();

// Tenta cancelar Boleto
$sqlBoleto = "UPDATE pagamento_boleto SET status = 'cancelado' WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente'";
$stmtBoleto = $conn->prepare($sqlBoleto);
$stmtBoleto->bind_param('iis', $usuarioId, $id, $cor);
$stmtBoleto->execute();
$cancelouBoleto = $stmtBoleto->affected_rows > 0;
$stmtBoleto->close();

if ($cancelouPix || $cancelouBoleto) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhum pagamento pendente encontrado para cancelar.']);
}
