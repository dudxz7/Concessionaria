<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('conexao.php');

$sql = "SELECT m.id, m.modelo, d.cor_principal, (
    SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = d.cor_principal AND i.ordem = 1 LIMIT 1
) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id IN (
    SELECT modelo_id FROM promocoes WHERE status = 'Ativa' AND data_limite > NOW()
) GROUP BY m.id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
        $modeloFormatado = strtolower(str_replace(' ', '-', $carro['modelo']));
        $cor = $carro['cor_principal'] ?? 'padrao';
        $imagemPath = "img/modelos/cores/{$modeloFormatado}/{$cor}/{$imagemBase}";
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
        echo '<div class="card-simples">
                <div class="favorite-icon">
                    <button type="button" data-modelo-id="' . (int) $carro['id'] . '" style="background:none;border:none;padding:0;cursor:pointer;">
                        <img src="img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                    </button>
                </div>
                <img src="' . htmlspecialchars($imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '" class="img-simples">
                <h2 class="modelo-simples">' . htmlspecialchars($carro['modelo']) . '</h2>
            </div>';
    }
} else {
    echo '<div style="width:100%;display:flex;align-items:center;justify-content:center;min-height:220px;">
            <p style="font-size:1.3rem;color:#222;text-align:center;">Nenhuma oferta encontrada.</p>
          </div>';
}
