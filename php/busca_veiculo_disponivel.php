<?php
header('Content-Type: application/json');
include 'conexao.php';
$modelo_id = isset($_GET['modelo_id']) ? intval($_GET['modelo_id']) : 0;
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$res = ["veiculo_id" => null];
if ($modelo_id > 0 && $cor !== '') {
    $stmt = $conn->prepare("SELECT v.id FROM veiculos v INNER JOIN estoque e ON v.id = e.veiculo_id WHERE v.modelo_id = ? AND v.cor = ? AND e.quantidade > 0 LIMIT 1");
    $stmt->bind_param("is", $modelo_id, $cor);
    $stmt->execute();
    $stmt->bind_result($veiculo_id);
    if ($stmt->fetch()) {
        $res["veiculo_id"] = $veiculo_id;
    }
    $stmt->close();
}
echo json_encode($res);
