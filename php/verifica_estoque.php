<?php
// verifica_estoque.php
header('Content-Type: application/json');
include 'conexao.php';
// Recebe o id do modelo pela query string (ex: pagina_veiculo.php?id=1)
$modelo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = ["disponivel" => false];
if ($modelo_id > 0) {
    // Verifica se existe pelo menos 1 veículo desse modelo com status 'disponivel'
    $sql = "SELECT 1 FROM veiculos WHERE modelo_id = ? AND status = 'disponivel' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $modelo_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $res["disponivel"] = true;
    }
    $stmt->close();
}
echo json_encode($res);
?>
