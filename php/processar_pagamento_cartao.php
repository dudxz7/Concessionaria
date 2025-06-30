<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json');

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Validação dos campos obrigatórios
if (!isset($_POST['cliente_id']) || !isset($_POST['veiculo_id']) || !isset($_POST['cor']) || !isset($_POST['nome_impresso']) || !isset($_POST['numero_cartao_final']) || !isset($_POST['bandeira']) || !isset($_POST['valor']) || !isset($_POST['status']) || !isset($_POST['parcelas'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'erro' => 'Campos obrigatórios ausentes.'
    ]);
    exit;
}

$cliente_id = isset($_SESSION['usuarioId']) ? intval($_SESSION['usuarioId']) : 0;
$veiculo_id = intval($_POST['veiculo_id']);
$cor = trim($_POST['cor']);
$nome_impresso = trim($_POST['nome_impresso']);
$numero_cartao_final = trim($_POST['numero_cartao_final']);
$bandeira = trim($_POST['bandeira']);
$valor = floatval(str_replace([','], ['.'], $_POST['valor'])); // Garante decimal correto
$status = ($_POST['status'] === 'aprovado') ? 'aprovado' : 'recusado';
$parcelas = intval($_POST['parcelas']);

// INSERÇÃO NO BANCO (agora com os novos campos)
$sql = "INSERT INTO pagamentos_cartao (cliente_id, veiculo_id, cor, nome_impresso, numero_cartao_final, bandeira, valor, status, parcelas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iissssdsi', $cliente_id, $veiculo_id, $cor, $nome_impresso, $numero_cartao_final, $bandeira, $valor, $status, $parcelas);

if ($stmt->execute()) {
    $id_pagamento_cartao = $stmt->insert_id;
    // Se pagamento aprovado, marca veículo como vendido e vincula o pagamento
    if ($status === 'aprovado') {
        // Busca o veículo disponível do mesmo modelo, com menor id
        $sql_veic = "SELECT id FROM veiculos WHERE modelo_id = ? AND status = 'disponivel' ORDER BY id ASC LIMIT 1";
        $stmt_veic = $conn->prepare($sql_veic);
        $stmt_veic->bind_param('i', $veiculo_id);
        $stmt_veic->execute();
        $stmt_veic->bind_result($veic_id);
        if ($stmt_veic->fetch() && $veic_id) {
            $stmt_veic->close();
            $sql_update = "UPDATE veiculos SET status = 'vendido', id_pagamento = ?, tipo_pagamento = 'CARTAO' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ii', $id_pagamento_cartao, $veic_id);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_veic->close();
        }
    }
    echo json_encode([
        'success' => true,
        'message' => 'Pagamento registrado com sucesso!',
        'debug' => [
            'cliente_id' => $cliente_id,
            'veiculo_id' => $veiculo_id,
            'cor' => $cor,
            'nome_impresso' => $nome_impresso,
            'numero_cartao_final' => $numero_cartao_final,
            'bandeira' => $bandeira,
            'valor' => $valor,
            'status' => $status,
            'parcelas' => $parcelas
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao registrar pagamento.',
        'error' => $stmt->error,
        'debug' => [
            'cliente_id' => $cliente_id,
            'veiculo_id' => $veiculo_id,
            'cor' => $cor,
            'nome_impresso' => $nome_impresso,
            'numero_cartao_final' => $numero_cartao_final,
            'bandeira' => $bandeira,
            'valor' => $valor,
            'status' => $status,
            'parcelas' => $parcelas
        ]
    ]);
}
$stmt->close();
$conn->close();
?>