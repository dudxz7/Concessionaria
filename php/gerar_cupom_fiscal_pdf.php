<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/fpdf/fpdf.php'; // Ajuste o caminho se necessário

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

$pdf = new FPDF();
$pdf->AddPage();
// Removido logo e nome BMW Sór
$pdf->SetFont('Arial','B',18);
$pdf->SetTextColor(26,78,216); // azul
$pdf->Cell(0,12,'Cupom Fiscal',0,1,'C');
$pdf->SetDrawColor(26,78,216); // azul
$pdf->SetLineWidth(0.8);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(6);

$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(40,8,'Data:',0,0);
$pdf->Cell(0,8,date('d/m/Y H:i', strtotime($venda['data_venda'])),0,1);
$pdf->Cell(40,8,'Cliente:',0,0);
$pdf->Cell(0,8,utf8_decode($venda['cliente_nome']).' (CPF: '.$venda['cliente_cpf'].')',0,1);
$pdf->Cell(40,8,'Modelo:',0,0);
$pdf->Cell(0,8,utf8_decode($venda['modelo']).' ('.$venda['ano'].')',0,1);
$pdf->Cell(40,8,'Chassi:',0,0);
$pdf->Cell(0,8,$venda['numero_chassi'],0,1);
$pdf->Cell(40,8,'Cor:',0,0);
$pdf->Cell(0,8,utf8_decode($venda['cor_veiculo']),0,1);
$pdf->Cell(40,8,'Forma de Pagamento:',0,0);
$pdf->Cell(0,8,utf8_decode($venda['forma_pagamento']),0,1);
$pdf->Cell(40,8,'Desconto:',0,0);
$pdf->Cell(0,8,number_format($venda['desconto'],2,',','.').'% ',0,1);
$pdf->Cell(40,8,'Serv. Adicionais:',0,0);
$pdf->Cell(0,8,utf8_decode($venda['servicos_adicionais']),0,1);
$pdf->Ln(2);
$pdf->SetDrawColor(26,78,216); // azul
$pdf->SetLineWidth(0.5);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(6);
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(26,78,216); // azul
$pdf->Cell(0,10,'Total Pago: R$ '.number_format($venda['total'],2,',','.'),0,1,'R');
$pdf->SetFont('Arial','I',11);
$pdf->SetTextColor(120,120,120);
$pdf->Ln(6);
$pdf->MultiCell(0,8,utf8_decode("Obrigado por comprar na BMW!\nEste é um comprovante de venda manual."),0,'C');

$pdf->Output('D','cupom_fiscal_BMW.pdf');
exit;
