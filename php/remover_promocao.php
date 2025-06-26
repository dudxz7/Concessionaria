<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido.']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'ID inválido.']);
    exit;
}

$id = (int)$_POST['id'];

$conn = new mysqli('localhost', 'root', '', 'sistema_bmw');
if ($conn->connect_error) {
    echo json_encode(['sucesso' => false, 'erro' => 'Falha na conexão com o banco de dados.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM promocoes WHERE id = ?');
$stmt->bind_param('i', $id);
$sucesso = $stmt->execute();
$stmt->close();
$conn->close();

if ($sucesso) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao remover promoção.']);
}
