<?php
require_once __DIR__ . '/conexao.php';

$venda_id = intval($_GET['venda_id'] ?? 0);
if ($venda_id <= 0) {
    echo '<h2>Venda não encontrada!</h2>';
    exit;
}

// Busca dados da venda, cliente, veículo
$sql = "SELECT v.id, v.data_venda, v.forma_pagamento, v.desconto, v.total, v.cor_veiculo, v.servicos_adicionais,
              c.nome_completo AS cliente_nome, c.cpf AS cliente_cpf,
              ve.numero_chassi, m.modelo, m.ano
       FROM vendas_fisicas v
       JOIN clientes c ON v.cliente_id = c.id
       JOIN veiculos ve ON v.veiculo_id = ve.id
       JOIN modelos m ON ve.modelo_id = m.id
       WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $venda_id);
$stmt->execute();
$res = $stmt->get_result();
$venda = $res->fetch_assoc();
$stmt->close();

if (!$venda) {
    echo '<h2>Venda não encontrada!</h2>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cupom Fiscal - Venda Manual</title>
    <link rel="stylesheet" href="../css/cupom_fiscal.css">
    <link rel="icon" href="../img/desconto.png">
</head>
<body>
<div class="cupom-wrap">
<div class="cupom">
    <h2>Cupom Fiscal</h2>
    <div class="linha"></div>
    <div class="info"><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></div>
    <div class="info"><strong>Cliente:</strong> <?= htmlspecialchars($venda['cliente_nome']) ?> <span style="color:#888;">(CPF: <?= htmlspecialchars($venda['cliente_cpf']) ?>)</span></div>
    <div class="info"><strong>Modelo:</strong> <?= htmlspecialchars($venda['modelo']) ?> (<?= $venda['ano'] ?>)</div>
    <div class="info"><strong>Chassi:</strong> <?= htmlspecialchars($venda['numero_chassi']) ?></div>
    <div class="info"><strong>Cor:</strong> <?= htmlspecialchars($venda['cor_veiculo']) ?></div>
    <div class="info"><strong>Forma de Pagamento:</strong> <?= htmlspecialchars($venda['forma_pagamento']) ?></div>
    <div class="info"><strong>Desconto:</strong> <?= number_format($venda['desconto'], 2, ',', '.') ?>%</div>
    <div class="info"><strong>Serviços Adicionais:</strong> <?= htmlspecialchars($venda['servicos_adicionais']) ?></div>
    <div class="linha"></div>
    <div class="total">Total Pago: R$ <?= number_format($venda['total'], 2, ',', '.') ?></div>
    <div class="footer">Obrigado por comprar na BMW!<br>Este é um comprovante de venda manual.</div>
    <form method="post" action="gerar_cupom_fiscal_pdf.php" style="margin-top: 32px; text-align: center;">
        <input type="hidden" name="venda_id" value="<?= htmlspecialchars($venda_id) ?>">
        <button type="submit" class="btn-pdf">
            <img src="../img/pdf.png" alt="PDF" style="height: 22px; vertical-align: middle; margin-right: 10px; filter: drop-shadow(0 1px 2px #0002);">
            Gerar PDF
        </button>
    </form>
</div>
</div>
</body>
</html>
