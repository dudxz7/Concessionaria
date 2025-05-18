<?php
session_start();

// Verifica login
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$conn = new mysqli("localhost", "root", "", "sistema_bmw");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica permissão do usuário
$usuarioId = $_SESSION['usuarioId'] ?? null;
if ($usuarioId) {
    $sql = "SELECT cargo FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_assoc();
    $stmt->close();

    if (!$dados || !in_array($dados['cargo'], ['Admin', 'Gerente'])) {
        echo "<h2>Acesso Negado</h2><p>Você não tem permissão para excluir imagens.</p>";
        exit;
    }
} else {
    echo "Usuário não identificado.";
    exit;
}

// Recebe os parâmetros
$imagem_id = isset($_GET['imagem_id']) ? intval($_GET['imagem_id']) : 0;
$modelo_id = isset($_GET['modelo_id']) ? intval($_GET['modelo_id']) : 0;
$cor = isset($_GET['cor']) ? $_GET['cor'] : '';

if (!$imagem_id || !$modelo_id || !$cor) {
    echo "Parâmetros inválidos.";
    exit;
}

// Busca o nome do arquivo da imagem para exclusão física
$stmt = $conn->prepare("SELECT imagem FROM imagens_secundarias WHERE id = ? AND modelo_id = ? AND cor = ?");
$stmt->bind_param("iis", $imagem_id, $modelo_id, $cor);
$stmt->execute();
$res = $stmt->get_result();
$imagemData = $res->fetch_assoc();
$stmt->close();

if (!$imagemData) {
    echo "Imagem não encontrada.";
    exit;
}

$nomeArquivo = $imagemData['imagem'];

// Caminho completo da imagem (ajuste conforme seu projeto)
$caminhoImagem = __DIR__ . "/../img/modelos/cores/" 
    . strtolower(preg_replace('/[^a-z0-9\-]/i', '-', $_GET['modelo'])) . "/" 
    . strtolower(preg_replace('/[^a-z0-9\-]/i', '-', $cor)) . "/" 
    . $nomeArquivo;

// Remove a imagem do banco
$stmtDel = $conn->prepare("DELETE FROM imagens_secundarias WHERE id = ?");
$stmtDel->bind_param("i", $imagem_id);
$stmtDel->execute();
$stmtDel->close();

// Remove o arquivo físico se existir
if (file_exists($caminhoImagem)) {
    unlink($caminhoImagem);
}

$conn->close();

// Redireciona de volta para a página de edição da ordem, mantendo modelo e cor selecionados
header("Location: editar_ordem_imagens.php?modelo_id=$modelo_id&cor=" . urlencode($cor));
exit;
?>
