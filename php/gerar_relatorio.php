<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/fpdf/fpdf.php';

// Recebe parâmetros via get ta meio obv na real 
$tipo = $_GET['tipo'] ?? '';
$veiculo_id = $_GET['veiculo_id'] ?? '';
$status = $_GET['status'] ?? '';
$data_expiracao = $_GET['data'] ?? '';
$data_criacao = $_GET['data_criacao'] ?? '';

// Busca dados do veículo
$sql = "SELECT m.modelo, m.ano, d.descricao, m.preco FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $veiculo_id);
$stmt->execute();
$result = $stmt->get_result();
$carro = $result->fetch_assoc();

// Corrige data de criação se vier vazia
if (empty($data_criacao)) {
    if ($tipo === 'PIX') {
        $sqlPix = "SELECT criado_em FROM pagamentos_pix WHERE veiculo_id = ? AND expira_em = ? LIMIT 1";
        $stmtPix = $conn->prepare($sqlPix);
        $stmtPix->bind_param('is', $veiculo_id, $data_expiracao);
        $stmtPix->execute();
        $resPix = $stmtPix->get_result();
        if ($rowPix = $resPix->fetch_assoc()) {
            $data_criacao = $rowPix['criado_em'];
        }
    } elseif ($tipo === 'BOLETO') {
        $sqlBoleto = "SELECT data_criacao FROM pagamento_boleto WHERE veiculo_id = ? AND data_expiracao = ? LIMIT 1";
        $stmtBoleto = $conn->prepare($sqlBoleto);
        $stmtBoleto->bind_param('is', $veiculo_id, $data_expiracao);
        $stmtBoleto->execute();
        $resBoleto = $stmtBoleto->get_result();
        if ($rowBoleto = $resBoleto->fetch_assoc()) {
            $data_criacao = $rowBoleto['data_criacao'];
        }
    }
}

// Monta PDF
$pdf = new FPDF();
$pdf->AddPage();
// Centraliza logo e texto como um bloco
$logoPath = __DIR__ . '/../img/logos/logoofcbmw.png';
$logoW = 22;
$logoH = 22;
$espaco = 8;
$pdf->SetFont('Arial','B',16);
$titulo = utf8_decode('Relatório de Pagamento - BMW');
$tituloW = $pdf->GetStringWidth($titulo);
$blocoW = $logoW + $espaco + $tituloW;
$paginaW = $pdf->GetPageWidth();
$xInicio = ($paginaW - $blocoW) / 2;
$yInicio = 12;
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, $xInicio, $yInicio, $logoW, $logoH);
}
$pdf->SetXY($xInicio + $logoW + $espaco, $yInicio + 4);
$pdf->Cell($tituloW, 15, $titulo, 0, 1, 'L');
$pdf->Ln(18); // margin-bottom maior após o cabeçalho
$pdf->SetFont('Arial','',12);
$pdf->Cell(50,8,'Modelo:',0,0); $pdf->Cell(0,8,$carro['modelo'] ?? '',0,1);
$pdf->Cell(50,8,'Ano:',0,0); $pdf->Cell(0,8,$carro['ano'] ?? '',0,1);
$pdf->Cell(50,8,'Descricao:',0,0); $pdf->MultiCell(0,8,$carro['descricao'] ?? '');
$pdf->Cell(50,8,'Preco:',0,0); $pdf->Cell(0,8,'R$ '.number_format($carro['preco'] ?? 0,2,',','.'),0,1);
$pdf->Cell(50,8,'Tipo de Pagamento:',0,0); $pdf->Cell(0,8,$tipo,0,1);
$pdf->Cell(50,8,'Status:',0,0); $pdf->Cell(0,8,ucfirst($status),0,1);
$pdf->Cell(50,8,'Data de Criacao:',0,0); $pdf->Cell(0,8,($data_criacao ? date('d/m/Y H:i', strtotime($data_criacao)) : '-'),0,1);
$pdf->Cell(50,8,'Data de Expiracao:',0,0); $pdf->Cell(0,8,($data_expiracao ? date('d/m/Y H:i', strtotime($data_expiracao)) : '-'),0,1);

// Exibe o PDF no navegador, sem salvar no servidor
$pdf->Output('I', 'relatorio_pagamento.pdf');
exit;