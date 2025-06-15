<?php
session_start();
// Headers anti-cache para garantir atualização do status
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
date_default_timezone_set('America/Sao_Paulo');

require_once 'conexao.php';

$id_veiculo = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$usuarioId = isset($_SESSION['usuarioId']) ? $_SESSION['usuarioId'] : null;
if (!$id_veiculo || !$cor || !$usuarioId) {
    header('Location: pagamento_expirado.php');
    exit;
}

// Busca boleto pendente
$sqlBoleto = "SELECT * FROM pagamento_boleto WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' ORDER BY data_criacao DESC LIMIT 1";
$stmtBoleto = $conn->prepare($sqlBoleto);
$stmtBoleto->bind_param("iis", $usuarioId, $id_veiculo, $cor);
$stmtBoleto->execute();
$boleto = $stmtBoleto->get_result()->fetch_assoc();
$stmtBoleto->close();

if ($boleto) {
    $codigo_barras = $boleto['codigo_barras'];
    $data_expiracao = strtotime($boleto['data_expiracao']);
    $expiraEmJs = $data_expiracao;
    // Se o boleto já expirou, atualiza o status e redireciona para pagamento.php
    if ($data_expiracao <= time()) {
        $sqlExpira = "UPDATE pagamento_boleto SET status = 'expirado' WHERE id = ? AND status = 'pendente'";
        $stmtExpira = $conn->prepare($sqlExpira);
        $stmtExpira->bind_param("i", $boleto['id']);
        $stmtExpira->execute();
        $stmtExpira->close();
        header('Location: pagamento.php?id=' . urlencode($id_veiculo) . '&cor=' . urlencode($cor));
        exit;
    }
} else {
    // Não bloqueia se houver expirado, permite criar novo boleto normalmente

    // Busca valor do veículo
    $sqlPreco = "SELECT m.preco, p.preco_com_desconto, p.status, p.data_limite FROM modelos m LEFT JOIN promocoes p ON m.id = p.modelo_id AND p.status = 'Ativa' AND p.data_limite > NOW() WHERE m.id = ?";
    $stmtPreco = $conn->prepare($sqlPreco);
    $stmtPreco->bind_param("i", $id_veiculo);
    $stmtPreco->execute();
    $resultPreco = $stmtPreco->get_result();
    $dadosPreco = $resultPreco->fetch_assoc();
    $stmtPreco->close();
    if (!empty($dadosPreco['preco_com_desconto']) && $dadosPreco['status'] === 'Ativa' && strtotime($dadosPreco['data_limite']) > time()) {
        $valor_boleto = $dadosPreco['preco_com_desconto'];
    } else {
        $valor_boleto = $dadosPreco['preco'];
    }
    $codigo_barras = "34191.79001 01043.510047 91020.150008 6 12340000010000";
    $expira_em = time() + 60; // Expira em 72 horas
    $data_expiracao = $expira_em;
    $data_expiracao_sql = date('Y-m-d H:i:s', $expira_em);
    $sqlInsert = "INSERT INTO pagamento_boleto (usuario_id, veiculo_id, cor, codigo_barras, status, data_expiracao, valor) VALUES (?, ?, ?, ?, 'pendente', ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iisssd", $usuarioId, $id_veiculo, $cor, $codigo_barras, $data_expiracao_sql, $valor_boleto);
    $stmtInsert->execute();
    $stmtInsert->close();
    $expiraEmJs = $expira_em;
}

$sql = "SELECT m.modelo, m.preco, p.preco_com_desconto, p.status, p.data_limite
        FROM modelos m
        LEFT JOIN promocoes p ON m.id = p.modelo_id AND p.status = 'Ativa' AND p.data_limite > NOW()
        WHERE m.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_veiculo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Veículo não encontrado.";
    exit;
}

$dados = $result->fetch_assoc();

// Só usa o preço promocional se a promoção estiver ativa e dentro do prazo
if (!empty($dados['preco_com_desconto']) && $dados['status'] === 'Ativa' && strtotime($dados['data_limite']) > time()) {
    $preco_final = $dados['preco_com_desconto'];
} else {
    $preco_final = $dados['preco'];
}
$preco_formatado = number_format($preco_final, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pagamento por Boleto</title>
    <link rel="stylesheet" href="../css/pagamento-pix.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .qrcode-img {
            margin-top: 10px;
            width: 430px;
            height: auto;
            border: none;
            border-radius: 0px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="pix-container">
        <!-- Topo com título e logo -->
        <div class="pix-header">
            <div class="titulo">Pagamento por boleto</div>
            <img src="../img/formas-de-pagamento/boletov2.png" alt="Logo Boleto" class="pix-logo">
        </div>

        <!-- Preço e temporizador -->
        <div class="linha-preco-temporizador">
            <div class="total"><span class="rs">R$</span> <span id="preco-boleto"><?php echo $preco_formatado; ?></span></div>
            <div class="temporizador">
                <span>Expira em</span>
                <div class="contador">
                    <img src="../img/relogio.png" alt="Relógio">
                    <span id="boleto-timer">--:--:--</span>
                </div>
            </div>
        </div>

        <!-- Texto e código de barras -->
        <p class="qr-instru">Baixe ou copie o código de barras do boleto abaixo</p>
        <div class="qrcode-box">
            <img src="../img/qrcodes/boleto.png" alt="Boleto" class="qrcode-img">
        </div>

        <!-- Info Pagador -->
        <div class="pagador">
            PAG SEGURO TECNOLOGIA LTDA.<br>
            CNPJ:00.000.000/0001-11
        </div>

        <!-- Copiar código -->
        <p class="copiar-instru">Copie o código de barras abaixo</p>
        <div class="copiar-box">
            <input type="text" readonly value="<?php echo htmlspecialchars($codigo_barras); ?>" id="codigoBoleto">
            <button class="copiar-btn"><img src="../img/copia.png" alt="Copiar" /> Copiar</button>
        </div>

        <!-- Texto acima dos passos -->
        <p class="finaliza">Para finalizar sua compra, pague o boleto até o prazo limite.</p>

        <!-- Passos -->
        <div class="passos">
            <div class="passo">
                <div class="passo-numero">PASSO 1</div>
                <div class="passo-texto">Baixe ou copie o código de barras do boleto acima</div>
            </div>
            <div class="passo">
                <div class="passo-numero">PASSO 2</div>
                <div class="passo-texto">Acesse o app do seu banco ou internet banking e escolha a opção de pagamento de boleto</div>
            </div>
            <div class="passo">
                <div class="passo-numero">PASSO 3</div>
                <div class="passo-texto">Cole ou digite o código de barras e confirme o pagamento</div>
            </div>
        </div>
    </div>
    <script>
(function() {
    // Tempo de expiração salvo no banco (em segundos)
    var expiraEm = <?php echo isset($expiraEmJs) ? intval($expiraEmJs) : 0 ?>;
    var agora = Math.floor(Date.now() / 1000);
    var tempoRestante = expiraEm - agora;
    var timerSpan = document.getElementById('boleto-timer');

    function pad(n) { return n < 10 ? '0' + n : n; }

    function atualizarTimer() {
        if (tempoRestante <= 0) {
            timerSpan.textContent = '00:00:00';
            timerSpan.style.color = 'red';
            return;
        }
        var horas = Math.floor(tempoRestante / 3600);
        var min = Math.floor((tempoRestante % 3600) / 60);
        var seg = tempoRestante % 60;
        timerSpan.textContent = pad(horas) + ':' + pad(min) + ':' + pad(seg);
        tempoRestante--;
        setTimeout(atualizarTimer, 1000);
    }
    atualizarTimer();
})();
</script>
<script src="../js/copiar-codigo-boleto.js"></script>
</body>
</html>