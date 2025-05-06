<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('conexao.php');

// Processa o clique no botão de favoritar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favoritar'])) {
    $modeloId = (int) $_POST['modelo_id'];
    $usuarioId = $_SESSION['usuarioId'] ?? null;

    if ($usuarioId) {
        // Verifica se já está nos favoritos
        $stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
        $stmt->bind_param("ii", $usuarioId, $modeloId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Remove
            $stmtDel = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
            $stmtDel->bind_param("ii", $usuarioId, $modeloId);
            $stmtDel->execute();
        } else {
            // Adiciona
            $stmtAdd = $conn->prepare("INSERT INTO favoritos (usuario_id, modelo_id) VALUES (?, ?)");
            $stmtAdd->bind_param("ii", $usuarioId, $modeloId);
            $stmtAdd->execute();
        }
    }

    // Redireciona para evitar reenvio e desce para a seção de veículos
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "#main");
    exit;

}

// Funções auxiliares
function gerarAno($ano)
{
    return ($ano - 1) . '/' . $ano;
}

function gerarRating()
{
    $cheias = rand(3, 5);
    $estrelas = array_fill(0, $cheias, 'estrela.png');
    if (count($estrelas) < 5 && rand(0, 1))
        $estrelas[] = 'estrela-metade.png';
    while (count($estrelas) < 5)
        $estrelas[] = 'estrela-neutra.png';
    return $estrelas;
}

function gerarNota()
{
    return rand(1, 1500);
}

// Consulta modelos SEM promoções ativas
$sql = "
  SELECT m.id, m.modelo, m.fabricante, m.cor, m.ano, m.preco, d.descricao, d.imagem
  FROM modelos m
  LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
  WHERE m.id NOT IN (
    SELECT modelo_id FROM promocoes
    WHERE status = 'Ativa' AND data_limite > NOW()
  )";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        $imagemPath = 'img/modelos/' . htmlspecialchars($carro['imagem']);
        $anoFormatado = gerarAno($carro['ano']);
        $rating = gerarRating();
        $nota = gerarNota();

        $favorito = false;
        if (isset($_SESSION['usuarioId'])) {
            $usuarioId = $_SESSION['usuarioId'];
            $stmt_favorito = $conn->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
            $stmt_favorito->bind_param("ii", $usuarioId, $carro['id']);
            $stmt_favorito->execute();
            $stmt_favorito->store_result();
            $favorito = $stmt_favorito->num_rows > 0;
        }

        $coracaoImg = $favorito ? "coracao-salvo.png" : "coracao-nao-salvo.png";

        echo '<div class="card">
                <div class="favorite-icon">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="modelo_id" value="' . (int) $carro['id'] . '">
                        <button type="submit" name="favoritar" style="background:none;border:none;padding:0;cursor:pointer;">
                            <img src="img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                        </button>
                    </form>
                </div>
                <img src="' . $imagemPath . '" alt="' . htmlspecialchars($carro['modelo']) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p><img src="img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>
                <div class="rating">';

        foreach ($rating as $estrela) {
            echo '<img src="img/cards/' . $estrela . '" alt="estrela">';
        }

        echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span>
                </div>
                <h2>R$ ' . number_format($carro['preco'], 2, ',', '.') . '</h2>
                <a href="php/pagina_veiculo.php?id=' . $carro['id'] . '">
                    <button class="btn-send">Estou interessado</button>
                </a>
            </div>';
    }
} else {
    echo "<p>Nenhum modelo encontrado.</p>";
}
?>