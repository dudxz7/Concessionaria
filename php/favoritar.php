<?php
session_start();
header('Content-Type: application/json');
require_once('conexao.php');

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}
if (!isset($_POST['modelo_id'])) {
    echo json_encode(['success' => false, 'error' => 'Modelo não informado']);
    exit;
}
$usuarioId = $_SESSION['usuarioId'];
$modeloId = (int)$_POST['modelo_id'];
$stmt = $conn->prepare('SELECT 1 FROM favoritos WHERE usuario_id = ? AND modelo_id = ?');
$stmt->bind_param('ii', $usuarioId, $modeloId);
$stmt->execute();
$stmt->store_result();
$isFavorito = $stmt->num_rows > 0;
$stmt->close();
if ($isFavorito) {
    $stmt = $conn->prepare('DELETE FROM favoritos WHERE usuario_id = ? AND modelo_id = ?');
    $stmt->bind_param('ii', $usuarioId, $modeloId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'favorito' => false]);
} else {
    $stmt = $conn->prepare('INSERT INTO favoritos (usuario_id, modelo_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $usuarioId, $modeloId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true, 'favorito' => true]);
}
?>
