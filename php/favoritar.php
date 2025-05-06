<?php
session_start();
require_once('conexao.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favoritar'])) {
    $modeloId = $_POST['modelo_id'];
    $usuarioId = $_SESSION['usuarioId'] ?? null;

    if ($usuarioId) {
        // Verifica se já é favorito
        $stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
        $stmt->bind_param("ii", $usuarioId, $modeloId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Já é favorito, então remove
            $stmtDel = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
            $stmtDel->bind_param("ii", $usuarioId, $modeloId);
            $stmtDel->execute();
        } else {
            // Não é favorito, então adiciona
            $stmtAdd = $conn->prepare("INSERT INTO favoritos (usuario_id, modelo_id) VALUES (?, ?)");
            $stmtAdd->bind_param("ii", $usuarioId, $modeloId);
            $stmtAdd->execute();
        }
    }

    // Redireciona de volta para a mesma página (evita reenvio do formulário)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
