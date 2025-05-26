<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('conexao.php');

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Função atualizada para retornar a URL correta e tratar espaços e vírgulas
function encontrarImagemVeiculo($modelo, $cor, $nomeArquivoBase)
{
    $modeloFormatado = strtolower(str_replace(' ', '-', $modelo));
    $pasta_fs = __DIR__ . "/../img/modelos/cores/{$modeloFormatado}/{$cor}/";
    $pasta_web = "img/modelos/cores/{$modeloFormatado}/{$cor}/";

    // Detecta se o nome já tem extensão
    $extensao = pathinfo($nomeArquivoBase, PATHINFO_EXTENSION);

    if ($extensao) {
        // Se arquivo com essa extensão existir, retorna caminho
        if (file_exists($pasta_fs . $nomeArquivoBase)) {
            return $pasta_web . $nomeArquivoBase;
        }
        // Se não existe, retorna caminho com o nome original (para debug)
        return $pasta_web . $nomeArquivoBase;
    } else {
        // Sem extensão: tenta as extensões comuns
        $extensoes = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
        foreach ($extensoes as $ext) {
            $arquivo = $nomeArquivoBase . '.' . $ext;
            if (file_exists($pasta_fs . $arquivo)) {
                return $pasta_web . $arquivo;
            }
        }
        // Se não achou nada, retorna com .jpg para debug
        return "img/modelos/padrao.webp";
    }
}

// Não processa mais POST nem faz header() aqui!

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

// --- FILTRO PHP PELO GET ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$precoMin = (isset($_GET['preco_min']) && $_GET['preco_min'] !== '' && $_GET['preco_min'] !== '0') ? floatval($_GET['preco_min']) : null;
$precoMax = (isset($_GET['preco_max']) && $_GET['preco_max'] !== '' && $_GET['preco_max'] !== '0') ? floatval($_GET['preco_max']) : null;
$anoMin = (isset($_GET['ano_min']) && $_GET['ano_min'] !== '' && $_GET['ano_min'] !== '0') ? intval($_GET['ano_min']) : null;
$anoMax = (isset($_GET['ano_max']) && $_GET['ano_max'] !== '' && $_GET['ano_max'] !== '0') ? intval($_GET['ano_max']) : null;
$anoFiltro = (isset($_GET['ano']) && $_GET['ano'] !== '' && $_GET['ano'] !== '0') ? intval($_GET['ano']) : null;

$params = [];
$types = '';

$sql = "SELECT m.id, m.modelo, m.fabricante, m.cor, d.cor_principal, m.ano, m.preco, d.descricao, (
    SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = d.cor_principal AND i.ordem = 1 LIMIT 1
) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE 1=1";

// Excluir todos os modelos em promoção (qualquer cor), sempre!
$sql .= " AND m.id NOT IN (
    SELECT modelo_id FROM promocoes WHERE status = 'Ativa' AND data_limite > NOW()
)";

// Filtro de cor robusto para múltiplos valores (ex: "preto,branco")
if ($cor !== '') {
    $corList = array_map('trim', explode(',', strtolower($cor)));
    $corConditions = [];
    foreach ($corList as $corItem) {
        $corItemNoSpace = str_replace(' ', '', $corItem);
        $corConditions[] = "FIND_IN_SET(?, LOWER(REPLACE(REPLACE(m.cor, ', ', ','), ' ', '')))";

        $params[] = $corItemNoSpace;
        $types .= 's';
        $corConditions[] = "FIND_IN_SET(?, LOWER(REPLACE(REPLACE(d.cor_principal, ', ', ','), ' ', '')))";

        $params[] = $corItemNoSpace;
        $types .= 's';
        $corConditions[] = "LOWER(REPLACE(m.cor, ' ', '')) LIKE ?";

        $params[] = "%$corItemNoSpace%";
        $types .= 's';
        $corConditions[] = "LOWER(REPLACE(d.cor_principal, ' ', '')) LIKE ?";

        $params[] = "%$corItemNoSpace%";
        $types .= 's';
    }
    $sql .= " AND (" . implode(' OR ', $corConditions) . ")";
}
if ($search !== '') {
    $sql .= " AND (m.modelo LIKE ? OR m.fabricante LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if (!is_null($precoMin)) {
    $sql .= " AND m.preco >= ?";
    $params[] = $precoMin;
    $types .= 'd';
}
if (!is_null($precoMax)) {
    $sql .= " AND m.preco <= ?";
    $params[] = $precoMax;
    $types .= 'd';
}
// Filtro de ano (faixa ou único)
if (!is_null($anoFiltro) && is_null($anoMin) && is_null($anoMax)) {
    $sql .= " AND m.ano = ?";
    $params[] = $anoFiltro;
    $types .= 'i';
} else if (!is_null($anoMin) || !is_null($anoMax)) {
    if (!is_null($anoMin)) {
        $sql .= " AND m.ano >= ?";
        $params[] = $anoMin;
        $types .= 'i';
    }
    if (!is_null($anoMax)) {
        $sql .= " AND m.ano <= ?";
        $params[] = $anoMax;
        $types .= 'i';
    }
}
$sql .= " GROUP BY m.id";

$stmt = $conn->prepare($sql);
if (!empty($types) && count($params) === strlen($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
        $imagemPath = encontrarImagemVeiculo($carro['modelo'], $carro['cor_principal'], $imagemBase);

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
                    <button type="button" class="btn-favoritar" data-modelo-id="' . (int) $carro['id'] . '" style="background:none;border:none;padding:0;cursor:pointer;">
                        <img src="img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                    </button>
                </div>
                <img src="' . htmlspecialchars($imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '">
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
                <a href="php/pagina_veiculo.php?id=' . $carro['id'] . '" class="btn-link">
                    <button class="btn-send">Estou interessado</button>
                </a>
            </div>';
    }
} else {
    echo "<p style='width:100%;text-align:center;font-size:1.2rem;padding:2em 0;'>Nenhum modelo encontrado.</p>";
}
?>