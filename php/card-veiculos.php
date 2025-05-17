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


// POST favoritar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favoritar'])) {
    $modeloId = (int) $_POST['modelo_id'];
    $usuarioId = $_SESSION['usuarioId'] ?? null;

    if ($usuarioId) {
        $stmt = $conn->prepare("SELECT 1 FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
        $stmt->bind_param("ii", $usuarioId, $modeloId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmtDel = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND modelo_id = ?");
            $stmtDel->bind_param("ii", $usuarioId, $modeloId);
            $stmtDel->execute();
        } else {
            $stmtAdd = $conn->prepare("INSERT INTO favoritos (usuario_id, modelo_id) VALUES (?, ?)");
            $stmtAdd->bind_param("ii", $usuarioId, $modeloId);
            $stmtAdd->execute();
        }
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "#main");
    exit;
}

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

$sql = "
    SELECT 
        m.id,
        m.modelo,
        m.fabricante,
        d.cor_principal,   -- Pega cor principal da tabela detalhes_modelos
        m.ano,
        m.preco,
        d.descricao,
        (
            SELECT i.imagem 
            FROM imagens_secundarias i 
            WHERE i.modelo_id = m.id 
                AND i.cor = d.cor_principal   -- Usa cor_principal da tabela detalhes_modelos
                AND i.ordem = 1
            LIMIT 1
        ) AS imagem_padrao
    FROM modelos m
    LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
    WHERE m.id NOT IN (
        SELECT modelo_id FROM promocoes
        WHERE status = 'Ativa' AND data_limite > NOW()
    )
    GROUP BY m.id
";


$result = $conn->query($sql);
if (!$result) {
    die("Erro na consulta: " . $conn->error);
}

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
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="modelo_id" value="' . (int) $carro['id'] . '">
                        <button type="submit" name="favoritar" style="background:none;border:none;padding:0;cursor:pointer;">
                            <img src="img/coracoes/' . $coracaoImg . '" alt="Favoritar" class="heart-icon" draggable="false">
                        </button>
                    </form>
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
    echo "<p>Nenhum modelo encontrado.</p>";
}
?>