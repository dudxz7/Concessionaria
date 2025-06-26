<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Requisição inválida.']);
    exit;
}

$id = intval($_POST['id']);
if ($id <= 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido.']);
    exit;
}

require_once 'conexao.php';

// Checar se o funcionário existe
$stmt = $conn->prepare('SELECT id FROM clientes WHERE id = ? AND (cargo = "Funcionario" OR cargo = "Gerente")');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['sucesso' => false, 'erro' => 'Funcionário não encontrado.']);
    exit;
}
$stmt->close();

// Remove o funcionário/gerente
$stmt = $conn->prepare('DELETE FROM clientes WHERE id = ? AND (cargo = "Funcionario" OR cargo = "Gerente")');
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao remover funcionário.']);
}
