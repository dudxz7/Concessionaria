<?php
session_start();
header('Content-Type: application/json');
require_once 'conexao.php';

function gerarChavePagamento($id_veiculo, $cor, $usuarioId) {
    return 'pagamento_' . $usuarioId . '_' . $id_veiculo . '_' . md5(strtolower($cor));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$pix_valido = false;
$usuarioId = isset($_SESSION['usuarioId']) ? $_SESSION['usuarioId'] : null;

if ($id > 0 && $cor && $usuarioId) {
    $sql = "SELECT expira_em FROM pagamentos_pix_pendentes WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $usuarioId, $id, $cor);
    $stmt->execute();
    $stmt->bind_result($expira_em_pix);
    if ($stmt->fetch()) {
        $agora = time();
        if ($expira_em_pix > $agora) {
            $pix_valido = true;
        }
    }
    $stmt->close();
}

echo json_encode(['pix_valido' => $pix_valido]);
