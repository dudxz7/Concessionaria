<?php
// cards-a-pagar.php
// Exibe os cards dos veículos "a pagar" do usuário logado, vindos de pagamento_pix-pendentes e pagamento_boleto

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioId']) && isset($_SESSION['id_usuario'])) {
    $_SESSION['usuarioId'] = $_SESSION['id_usuario'];
}
if (!isset($_SESSION['usuarioId'])) {
    echo '<p>Você precisa estar logado para ver seus itens a pagar.</p>';
    return;
}
$usuarioId = $_SESSION['usuarioId'];

require_once 'conexao.php';

// Funções utilitárias (copiadas de cards-favoritos.php)
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

// Busca os IDs dos modelos a pagar do usuário nas duas tabelas
$modelosAPagar = [];
$coresAPagar = [];

// PIX pendentes
$sqlPix = "SELECT veiculo_id, cor FROM pagamentos_pix_pendentes WHERE usuario_id = ?";
$stmtPix = $conn->prepare($sqlPix);
$stmtPix->bind_param('i', $usuarioId);
$stmtPix->execute();
$resultPix = $stmtPix->get_result();
while ($row = $resultPix->fetch_assoc()) {
    $modelosAPagar[] = $row['veiculo_id'];
    $coresAPagar[$row['veiculo_id']] = $row['cor'];
}

// Boleto pendentes
$sqlBoleto = "SELECT veiculo_id, cor FROM pagamento_boleto WHERE usuario_id = ? AND status = 'pendente'";
$stmtBoleto = $conn->prepare($sqlBoleto);
$stmtBoleto->bind_param('i', $usuarioId);
$stmtBoleto->execute();
$resultBoleto = $stmtBoleto->get_result();
while ($row = $resultBoleto->fetch_assoc()) {
    $modelosAPagar[] = $row['veiculo_id'];
    $coresAPagar[$row['veiculo_id']] = $row['cor'];
}

// Remove duplicados
$modelosAPagar = array_unique($modelosAPagar);

if (empty($modelosAPagar)) {
    echo '<p style="margin:2rem;font-size:1.2rem;">Você não possui veículos a pagar no momento.</p>';
    return;
}

// Busca dados dos modelos a pagar
$cards = [];
foreach ($modelosAPagar as $id) {
    $cor = $coresAPagar[$id];
    $sqlModelos = "SELECT m.id, m.modelo, m.ano, m.preco, d.descricao, (
        SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = ? AND i.ordem = 1 LIMIT 1
    ) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id = ? GROUP BY m.id";
    $stmtModelos = $conn->prepare($sqlModelos);
    $stmtModelos->bind_param('si', $cor, $id);
    $stmtModelos->execute();
    $resultModelos = $stmtModelos->get_result();
    if ($carro = $resultModelos->fetch_assoc()) {
        $carro['cor_selecionada'] = $cor;
        $cards[] = $carro;
    }
}

if (empty($cards)) {
    echo '<p style="margin:2rem;font-size:1.2rem;">Nenhum veículo encontrado.</p>';
    return;
}

foreach ($cards as $carro) {
    $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
    $corSelecionada = $carro['cor_selecionada'] ?? '';
    $imagemPath = encontrarImagemVeiculo($carro['modelo'], $corSelecionada, $imagemBase);
    $anoFormatado = gerarAno($carro['ano']);
    $rating = gerarRating();
    $nota = gerarNota();

    // Busca promoção ativa para o modelo
    $sqlPromo = "SELECT preco_com_desconto FROM promocoes WHERE modelo_id = ? AND status = 'Ativa' AND data_limite > NOW() LIMIT 1";
    $stmtPromo = $conn->prepare($sqlPromo);
    $stmtPromo->bind_param('i', $carro['id']);
    $stmtPromo->execute();
    $promo = $stmtPromo->get_result()->fetch_assoc();
    $precoExibir = $promo && !empty($promo['preco_com_desconto']) ? $promo['preco_com_desconto'] : $carro['preco'];

    echo '<div class="card">';
    echo '<img src="' . htmlspecialchars('../' . $imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '" style="width:100%;max-width:320px;">';
    echo '<h2>' . htmlspecialchars($carro['modelo']) . '</h2>';
    echo '<p>' . htmlspecialchars($carro['descricao']) . '</p>';
    echo '<p><img src="../img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="../img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>';
    echo '<div class="rating">';
    foreach ($rating as $estrela) {
        echo '<img src="../img/cards/' . $estrela . '" alt="estrela">';
    }
    echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span></div>';
    echo '<h2>R$ ' . number_format($precoExibir, 2, ',', '.') . '</h2>';
    // Botão de pagamento para boleto: redireciona para pagamento.php (que faz o fluxo correto de autorização e expiração)
    echo '<a href="../php/pagamento.php?id=' . $carro['id'] . '&cor=' . urlencode($corSelecionada) . '&redir=1" class="btn-link">';
    echo '<button class="btn-send">Pagar</button>';
    echo '</a>';
    echo '</div>';
}
?>
