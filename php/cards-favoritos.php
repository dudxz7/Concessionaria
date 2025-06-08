<?php
// cards-favoritos.php
// Exibe os cards dos modelos favoritados pelo usuário logado

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Corrige para garantir que a sessão e o id do usuário estejam corretos
if (!isset($_SESSION['usuarioId']) && isset($_SESSION['id_usuario'])) {
    $_SESSION['usuarioId'] = $_SESSION['id_usuario'];
}
if (!isset($_SESSION['usuarioId'])) {
    echo '<p>Você precisa estar logado para ver seus favoritos.</p>';
    return;
}
$usuarioId = $_SESSION['usuarioId'];

require_once 'conexao.php';

// Funções utilitárias iguais aos outros cards
if (!function_exists('encontrarImagemVeiculo')) {
    function encontrarImagemVeiculo($modelo, $cor, $nomeArquivoBase) {
        $modeloFormatado = strtolower(str_replace(' ', '-', $modelo));
        $pasta_fs = __DIR__ . "/../img/modelos/cores/{$modeloFormatado}/{$cor}/";
        $pasta_web = "img/modelos/cores/{$modeloFormatado}/{$cor}/";
        $extensao = pathinfo($nomeArquivoBase, PATHINFO_EXTENSION);
        if ($extensao) {
            if (file_exists($pasta_fs . $nomeArquivoBase)) {
                return $pasta_web . $nomeArquivoBase;
            }
            return $pasta_web . $nomeArquivoBase;
        } else {
            $extensoes = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            foreach ($extensoes as $ext) {
                $arquivo = $nomeArquivoBase . '.' . $ext;
                if (file_exists($pasta_fs . $arquivo)) {
                    return $pasta_web . $arquivo;
                }
            }
            return "img/modelos/padrao.webp";
        }
    }
}
if (!function_exists('gerarAno')) {
    function gerarAno($ano) {
        return ($ano - 1) . '/' . $ano;
    }
}
if (!function_exists('gerarRating')) {
    function gerarRating() {
        $cheias = rand(3, 5);
        $estrelas = array_fill(0, $cheias, 'estrela.png');
        if (count($estrelas) < 5 && rand(0, 1))
            $estrelas[] = 'estrela-metade.png';
        while (count($estrelas) < 5)
            $estrelas[] = 'estrela-neutra.png';
        return $estrelas;
    }
}
if (!function_exists('gerarNota')) {
    function gerarNota() {
        return rand(1, 1500);
    }
}

// Busca os IDs dos modelos favoritados pelo usuário
$sql = "SELECT modelo_id FROM favoritos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

$modelosFavoritos = [];
while ($row = $result->fetch_assoc()) {
    $modelosFavoritos[] = $row['modelo_id'];
}

if (empty($modelosFavoritos)) {
    echo '<p style="margin:2rem;font-size:1.2rem;">Você ainda não favoritou nenhum veículo.</p>';
    return;
}

// Busca dados dos modelos favoritados
$placeholders = implode(',', array_fill(0, count($modelosFavoritos), '?'));
$sqlModelos = "SELECT m.id, m.modelo, m.ano, m.preco, d.cor_principal, d.descricao, (
    SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = d.cor_principal AND i.ordem = 1 LIMIT 1
) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id IN ($placeholders) GROUP BY m.id";
$stmtModelos = $conn->prepare($sqlModelos);
$stmtModelos->bind_param(str_repeat('i', count($modelosFavoritos)), ...$modelosFavoritos);
$stmtModelos->execute();
$resultModelos = $stmtModelos->get_result();

if ($resultModelos->num_rows === 0) {
    echo '<p style="margin:2rem;font-size:1.2rem;">Nenhum veículo encontrado.</p>';
    return;
}

while ($carro = $resultModelos->fetch_assoc()) {
    $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
    $imagemPath = encontrarImagemVeiculo($carro['modelo'], $carro['cor_principal'], $imagemBase);
    $anoFormatado = gerarAno($carro['ano']);
    $rating = gerarRating();
    $nota = gerarNota();
    $favorito = true; // Aqui sempre é favorito
    $coracaoImg = "coracao-salvo.png";

    // Verifica promoção ativa
    $sqlPromo = "SELECT preco_com_desconto, desconto, data_limite FROM promocoes WHERE modelo_id = ? AND status = 'Ativa' AND data_limite > NOW() LIMIT 1";
    $stmtPromo = $conn->prepare($sqlPromo);
    $stmtPromo->bind_param('i', $carro['id']);
    $stmtPromo->execute();
    $promo = $stmtPromo->get_result()->fetch_assoc();

    if ($promo) {
        // Card com promoção (em favoritos.php NÃO mostra o preço antigo)
        $precoOriginal = $carro['preco'];
        $precoComDesconto = $promo['preco_com_desconto'];
        $desconto = $promo['desconto'];
        $dataAtual = new DateTime();
        $dataLimite = new DateTime($promo['data_limite']);
        $diasRest = $dataAtual->diff($dataLimite)->days . ' dias';
        echo '<div class="card">
                <div class="tempo-restante-wrapper">
                    <div class="tempo-restante">
                        <img src="../img/cards/relogio-branco.png" class="icon-tempo" alt="Tempo">
                        <div class="tempo-texto">
                            <span>Tempo restante</span>
                            <div class="dias">' . $diasRest . '</div>
                        </div>
                    </div>
                </div>
                <div class="favorite-icon">
                    <button type="button" class="btn-favoritar" data-modelo-id="' . (int) $carro['id'] . '" style="background:none;border:none;padding:0;cursor:pointer;">
                        <img src="../img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                    </button>
                </div>
                <img src="' . htmlspecialchars('../' . $imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p><img src="../img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="../img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>
                <div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="../img/cards/' . $estrela . '" alt="estrela">';
        }
        echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span></div>';
        // Só mostra o preço novo e desconto
        echo '<div class="preco-promocao">
                <div class="preco-novo">
                    <h2>R$ ' . number_format($precoComDesconto, 2, ',', '.') . '</h2>
                    <span class="desconto">-' . $desconto . '%</span>
                </div>
            </div>';
        echo '<a href="../php/pagina_veiculo.php?id=' . $carro['id'] . '" class="btn-link">
                <button class="btn-send">Estou interessado</button>
            </a>
        </div>';
    } else {
        // Card normal
        echo '<div class="card">
                <div class="favorite-icon">
                    <button type="button" class="btn-favoritar" data-modelo-id="' . (int) $carro['id'] . '" style="background:none;border:none;padding:0;cursor:pointer;">
                        <img src="../img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                    </button>
                </div>
                <img src="' . htmlspecialchars('../' . $imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p><img src="../img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="../img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>
                <div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="../img/cards/' . $estrela . '" alt="estrela">';
        }
        echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span>
                </div>
                <h2>R$ ' . number_format($carro['preco'], 2, ',', '.') . '</h2>
                <a href="../php/pagina_veiculo.php?id=' . $carro['id'] . '" class="btn-link">
                    <button class="btn-send">Estou interessado</button>
                </a>
            </div>';
    }
}
?>
