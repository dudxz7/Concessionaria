<?php
// Endpoint AJAX para retornar imagens de uma cor específica de um modelo
session_start();
include 'conexao.php';

$id_modelo = isset($_GET['id_modelo']) ? intval($_GET['id_modelo']) : 0;
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';

if ($id_modelo <= 0 || empty($cor)) {
    echo json_encode(['success' => false, 'error' => 'Parâmetros inválidos']);
    exit;
}

// Busca o slug do modelo
$sqlModelo = "SELECT modelo FROM modelos WHERE id = ?";
$stmt = $conn->prepare($sqlModelo);
$stmt->bind_param("i", $id_modelo);
$stmt->execute();
$stmt->bind_result($modelo);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Modelo não encontrado']);
    exit;
}
$stmt->close();
$modelo_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($modelo));

// Busca imagens da cor
$sql = "SELECT imagem, cor, ordem FROM imagens_secundarias WHERE modelo_id = ? AND LOWER(cor) = LOWER(?) ORDER BY ordem ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id_modelo, $cor);
$stmt->execute();
$result = $stmt->get_result();
$imagens = [];
while ($row = $result->fetch_assoc()) {
    $cor_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower(trim($row['cor'])));
    $imagemPath = "../img/modelos/cores/{$modelo_slug}/{$cor_slug}/" . $row['imagem'];
    if (file_exists(__DIR__ . "/../img/modelos/cores/{$modelo_slug}/{$cor_slug}/" . $row['imagem'])) {
        $imagens[] = [
            'path' => $imagemPath,
            'ordem' => $row['ordem']
        ];
    }
}
$stmt->close();

echo json_encode(['success' => true, 'imagens' => $imagens]);
