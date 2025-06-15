<?php
session_start();
require_once 'conexao.php'; // ajuste o caminho se necessário

// Função para gerar uma chave única para cada tentativa de pagamento
function gerarChavePagamento($id_veiculo, $cor, $usuarioId) {
    return 'pagamento_' . $usuarioId . '_' . $id_veiculo . '_' . md5(strtolower($cor));
}

$usuarioId = isset($_SESSION['usuarioId']) ? $_SESSION['usuarioId'] : null;
$id_veiculo = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cor = isset($_GET['cor']) ? trim($_GET['cor']) : '';
$chave_pagamento = gerarChavePagamento($id_veiculo, $cor, $usuarioId);
$agora = time();
$pagamento_info = $_SESSION[$chave_pagamento] ?? null;

// FLUXO IDEAL: SÓ USA SESSION PARA VALIDAR O PIX
if ($pagamento_info && isset($pagamento_info['expira_em']) && $pagamento_info['expira_em'] > $agora) {
    // Session válida, segue para exibir QR Code normalmente
    // NÃO LIMPA session aqui!
    $_SESSION['pagamento_autorizado'] = true;
} else {
    // Se não está autorizado ou expirou, redireciona para pagamento.php
    header('Location: pagamento.php?id=' . $id_veiculo . '&cor=' . urlencode($cor));
    exit;
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
    <title>Pagamento via Pix</title>
    <link rel="stylesheet" href="../css/pagamento-pix.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="pix-container">

        <!-- Topo com título e logo -->
        <div class="pix-header">
            <div class="titulo">Pagamento via pix</div>
            <img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Logo Pix" class="pix-logo">
        </div>

        <!-- Preço e temporizador -->
        <div class="linha-preco-temporizador">
            <div class="total"><span class="rs">R$</span> <?= $preco_formatado ?></div>
            <div class="temporizador">
                <span>Expira em</span>
                <div class="contador">
                    <img src="../img/relogio.png" alt="Relógio">
                    <span id="pix-timer">--:--</span>
                </div>
            </div>
        </div>

        <!-- Texto e QR code -->
        <p class="qr-instru">Escaneie o QR CODE ou copie o código abaixo</p>
        <div class="qrcode-box">
            <img src="../img/qrcodes/qrcode_insta.png" alt="QR Code" class="qrcode-img">
        </div>

        <!-- Info Pagador -->
        <div class="pagador">
            PAG SEGURO TECNOLOGIA LTDA.<br>
            CNPJ:00.000.000/0001-11
        </div>

        <!-- Copiar código -->
        <p class="copiar-instru">Copie o código abaixo</p>
        <div class="copiar-box">
            <input type="text" readonly value="00020126685004br.gov.bcb.pix...etc" id="codigoPix">
            <button class="copiar-btn"><img src="../img/copia.png" alt="Copiar" /> Copiar</button>
        </div>

        <!-- Texto acima dos passos -->
        <p class="finaliza">Para finalizar sua compra, compense o pix no prazo limite.</p>

        <!-- Passos -->
        <div class="passos">
            <div class="passo">
                <div class="passo-numero">PASSO 1</div>
                <div class="passo-texto">Abra o app do seu banco e entre no ambiente pix</div>
            </div>
            <div class="passo">
                <div class="passo-numero">PASSO 2</div>
                <div class="passo-texto">Escolha Pagar com QR CODE e aponte a câmera para o código acima, ou cole o
                    código identificador de transação.</div>
            </div>
            <div class="passo">
                <div class="passo-numero">PASSO 3</div>
                <div class="passo-texto">Confirme os dados e o pagamento.</div>
            </div>
        </div>
    </div>
    <!-- Após exibir a página, NÃO limpe a autorização aqui! -->
    <!-- Removido: unset($_SESSION['pagamento_autorizado']); -->
    <script>
(function() {
    // Tempo de expiração salvo na sessão (em segundos)
    var expiraEm = <?= isset($pagamento_info['expira_em']) ? intval($pagamento_info['expira_em']) : 0 ?>;
    var agora = Math.floor(Date.now() / 1000);
    var tempoRestante = expiraEm - agora;
    var timerSpan = document.getElementById('pix-timer');

    function pad(n) { return n < 10 ? '0' + n : n; }

    function atualizarTimer() {
        if (tempoRestante <= 0) {
            timerSpan.textContent = '00:00';
            timerSpan.style.color = 'red';
            // Opcional: redirecionar ou mostrar mensagem de expiração
            return;
        }
        var min = Math.floor(tempoRestante / 60);
        var seg = tempoRestante % 60;
        timerSpan.textContent = pad(min) + ':' + pad(seg);
        tempoRestante--;
        setTimeout(atualizarTimer, 1000);
    }
    atualizarTimer();
})();
</script>
<script src="../js/copiar-codigo-pix.js"></script>
</body>
</html>
