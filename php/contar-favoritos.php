<?php
session_start();
header('Content-Type: application/json');
require_once('conexao.php');

if (!isset($_SESSION['usuarioId'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}
$usuarioId = $_SESSION['usuarioId'];
$stmt = $conn->prepare('SELECT COUNT(*) FROM favoritos WHERE usuario_id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
echo json_encode(['success' => true, 'count' => $count]);
