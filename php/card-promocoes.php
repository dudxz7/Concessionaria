<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('conexao.php');

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Evita funções duplicadas
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
            return $pasta_web . $nomeArquivoBase . '.jpg';
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
        if (count($estrelas) < 5 && rand(0, 1)) {
            $estrelas[] = 'estrela-metade.png';
        }
        while (count($estrelas) < 5) {
            $estrelas[] = 'estrela-neutra.png';
        }
        return $estrelas;
    }
}

if (!function_exists('gerarNota')) {
    function gerarNota() {
        return rand(1, 1500);
    }
}

// Handle filter parameters from POST (AJAX) ou GET (AJAX)
function getFiltro($key, $default = null) {
    if (isset($_GET[$key])) return trim($_GET[$key]);
    if (isset($_POST[$key])) return trim($_POST[$key]);
    return $default;
}
$modeloFiltro = getFiltro('modelo', '');
$corFiltro = getFiltro('cor', '');
$anoFiltro = getFiltro('ano', '');
$anoMin = getFiltro('ano_min', null); $anoMin = ($anoMin !== null && $anoMin !== '' && $anoMin !== '0') ? intval($anoMin) : null;
$anoMax = getFiltro('ano_max', null); $anoMax = ($anoMax !== null && $anoMax !== '' && $anoMax !== '0') ? intval($anoMax) : null;
$precoMin = getFiltro('preco_min', null); $precoMin = ($precoMin !== null && $precoMin !== '') ? floatval($precoMin) : null;
$precoMax = getFiltro('preco_max', null); $precoMax = ($precoMax !== null && $precoMax !== '') ? floatval($precoMax) : null;

// Build dynamic WHERE clauses
$where = ["p.status = 'Ativa'", "p.data_limite > NOW()"];
$params = [];
$types = '';

if ($modeloFiltro !== '') {
    $where[] = "(m.modelo LIKE ? OR m.abreviacao LIKE ?)";
    $params[] = "%$modeloFiltro%";
    $params[] = "%$modeloFiltro%";
    $types .= 'ss';
}
if ($corFiltro !== '') {
    // Corrige busca para múltiplas cores separadas por vírgula e ignora espaços
    $corList = array_map('trim', explode(',', strtolower($corFiltro)));
    $corConditions = [];
    foreach ($corList as $corItem) {
        $corItemNoSpace = str_replace(' ', '', $corItem);
        $corConditions[] = "FIND_IN_SET(?, LOWER(REPLACE(REPLACE(d.cor_principal, ', ', ','), ' ', '')))";

        $params[] = $corItemNoSpace;
        $types .= 's';
        $corConditions[] = "FIND_IN_SET(?, LOWER(REPLACE(REPLACE(m.cor, ', ', ','), ' ', '')))";

        $params[] = $corItemNoSpace;
        $types .= 's';
        $corConditions[] = "LOWER(REPLACE(d.cor_principal, ' ', '')) LIKE ?";

        $params[] = "%$corItemNoSpace%";
        $types .= 's';
        $corConditions[] = "LOWER(REPLACE(m.cor, ' ', '')) LIKE ?";

        $params[] = "%$corItemNoSpace%";
        $types .= 's';
    }
    $where[] = '(' . implode(' OR ', $corConditions) . ')';
}
if ($anoFiltro !== '' && is_null($anoMin) && is_null($anoMax)) {
    $where[] = "m.ano = ?";
    $params[] = $anoFiltro;
    $types .= 'i';
} else {
    if (!is_null($anoMin)) {
        $where[] = "m.ano >= ?";
        $params[] = $anoMin;
        $types .= 'i';
    }
    if (!is_null($anoMax)) {
        $where[] = "m.ano <= ?";
        $params[] = $anoMax;
        $types .= 'i';
    }
}
// ATENÇÃO: O filtro de preço abaixo já está correto, pois usa p.preco_com_desconto.
// Mas para garantir, vamos forçar o valor a ser float e garantir que nunca caia para preco_original.
if (!is_null($precoMin)) {
    $where[] = "p.preco_com_desconto >= ?";
    $params[] = (float)$precoMin;
    $types .= 'd';
}
if (!is_null($precoMax)) {
    $where[] = "p.preco_com_desconto <= ?";
    $params[] = (float)$precoMax;
    $types .= 'd';
}

$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "
    SELECT DISTINCT
        m.id,
        m.modelo,
        m.fabricante,
        d.cor_principal,
        m.ano,
        m.preco as preco_original,
        p.preco_com_desconto,
        p.desconto,
        p.data_limite,
        d.descricao,
        (
            SELECT i.imagem 
            FROM imagens_secundarias i 
            WHERE i.modelo_id = m.id 
                AND i.cor = d.cor_principal
                AND i.ordem = 1
            LIMIT 1
        ) AS imagem_padrao
    FROM modelos m
    INNER JOIN promocoes p ON m.id = p.modelo_id
    LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
    $whereSql
";

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

        $precoOriginal = $carro['preco_original'];
        $precoComDesconto = $carro['preco_com_desconto'];
        $desconto = $carro['desconto'];
        // $desconto = floatval(rtrim($carro['desconto'], '%')); se quiser remover o .00 extra  

        $dataAtual = new DateTime();
        $dataLimite = new DateTime($carro['data_limite']);
        $diasRest = $dataAtual->diff($dataLimite)->days . ' dias';

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

        // HTML do card
        echo '<div class="card">
                <div class="tempo-restante-wrapper">
                    <div class="tempo-restante">
                        <img src="img/cards/relogio-branco.png" class="icon-tempo" alt="Tempo">
                        <div class="tempo-texto">
                            <span>Tempo restante</span>
                            <div class="dias">' . $diasRest . '</div>
                        </div>
                    </div>
                </div>

                <div class="favorite-icon">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="modelo_id" value="' . $carro['id'] . '">
                        <button type="submit" name="favoritar" style="background:none;border:none;padding:0;cursor:pointer;">
                            <img src="img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                        </button>
                    </form>
                </div>

                <img src="' . htmlspecialchars($imagemPath) . '" alt="' . htmlspecialchars($carro['modelo']) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p>
                    <img src="img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . '
                    <img src="img/cards/painel-de-controle.png" alt="Km"> 0 Km
                </p>

                <div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="img/cards/' . $estrela . '" alt="estrela">';
        }
        echo '<span class="nota">(' . $nota . ')</span>
                </div>

                <div class="preco-promocao">
                    <h2 class="preco-antigo">R$ ' . number_format($precoOriginal, 2, ',', '.') . '</h2>
                    <div class="preco-novo">
                        <h2>R$ ' . number_format($precoComDesconto, 2, ',', '.') . '</h2>
                        <span class="desconto">-' . $desconto . '%</span>
                    </div>
                </div>

                <a href="php/pagina_veiculo.php?id=' . $carro['id'] . '" class="btn-link">
                    <button class="btn-send">Estou interessado</button>
                </a>
            </div>';
    }
} else {
    echo '<p>Nenhum veículo em promoção encontrado.</p>';
}
?>
