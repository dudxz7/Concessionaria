<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/tcpdf/tcpdf.php'; // Certifique-se de que a pasta tcpdf está em php/tcpdf/

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['venda_id'])) {
    die('Acesso inválido.');
}
$venda_id = intval($_POST['venda_id']);
if ($venda_id <= 0) {
    die('Venda não encontrada.');
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
    die('Venda não encontrada.');
}

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('BMW Sistema');
$pdf->SetAuthor('BMW');
$pdf->SetTitle('Cupom Fiscal - Venda Manual');
$pdf->SetMargins(20, 20, 20);
$pdf->AddPage();

$html = '<h2 style="color:#b80000;text-align:center;font-size:22px;">Cupom Fiscal</h2>';
$html .= '<hr style="border:1.5px dashed #b80000;margin:18px 0;">';
$html .= '<div style="font-size:14px;line-height:1.7;">';
$html .= '<b>Data:</b> ' . date('d/m/Y H:i', strtotime($venda['data_venda'])) . '<br>';
$html .= '<b>Cliente:</b> ' . htmlspecialchars($venda['cliente_nome']) . ' <span style="color:#888;">(CPF: ' . htmlspecialchars($venda['cliente_cpf']) . ')</span><br>';
$html .= '<b>Modelo:</b> ' . htmlspecialchars($venda['modelo']) . ' (' . $venda['ano'] . ')<br>';
$html .= '<b>Chassi:</b> ' . htmlspecialchars($venda['numero_chassi']) . '<br>';
$html .= '<b>Cor:</b> ' . htmlspecialchars($venda['cor_veiculo']) . '<br>';
$html .= '<b>Forma de Pagamento:</b> ' . htmlspecialchars($venda['forma_pagamento']) . '<br>';
$html .= '<b>Desconto:</b> ' . number_format($venda['desconto'], 2, ',', '.') . '%<br>';
$html .= '<b>Serviços Adicionais:</b> ' . htmlspecialchars($venda['servicos_adicionais']) . '<br>';
$html .= '<hr style="border:1.5px dashed #b80000;margin:18px 0;">';
$html .= '<div style="font-size:16px;color:#b80000;font-weight:bold;text-align:right;">Total Pago: R$ ' . number_format($venda['total'], 2, ',', '.') . '</div>';
$html .= '<div style="text-align:center;color:#888;margin-top:18px;font-size:13px;">Obrigado por comprar na BMW!<br>Este é um comprovante de venda manual.</div>';
$html .= '</div>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('cupom_fiscal_BMW.pdf', 'D');
exit;
