<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/fpdf/fpdf.php';

// Recebe parâmetros via get ta meio obv na real 
$tipo = $_GET['tipo'] ?? '';
$veiculo_id = $_GET['veiculo_id'] ?? '';
$status = $_GET['status'] ?? '';
$data_expiracao = $_GET['data'] ?? '';
$data_criacao = $_GET['data_criacao'] ?? '';

// Detecta se o parâmetro recebido é veiculo_id ou modelo_id
$modelo_id = null;
$veiculo_id_param = intval($veiculo_id);
$sqlCheck = "SELECT modelo_id FROM veiculos WHERE id = ? LIMIT 1";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param('i', $veiculo_id_param);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();
if ($rowCheck = $resCheck->fetch_assoc()) {
    $modelo_id = $rowCheck['modelo_id'];
} else {
    $modelo_id = $veiculo_id_param; // Se não for id de veículo, assume que é modelo_id
}

// Busca dados do veículo (modelo)
$sql = "SELECT m.modelo, m.ano, d.descricao, m.preco FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $modelo_id);
$stmt->execute();
$result = $stmt->get_result();
$carro = $result->fetch_assoc();

// Busca cor da venda (pagamento) SEMPRE da tabela de pagamento correspondente
$cor_venda = '';
if ($tipo === 'PIX') {
    $sqlCor = "SELECT cor FROM pagamentos_pix WHERE veiculo_id = ? AND status = ? AND expira_em = ? LIMIT 1";
    $stmtCor = $conn->prepare($sqlCor);
    $stmtCor->bind_param('iss', $veiculo_id, $status, $data_expiracao);
    $stmtCor->execute();
    $resCor = $stmtCor->get_result();
    if ($rowCor = $resCor->fetch_assoc()) {
        $cor_venda = $rowCor['cor'];
    }
} elseif ($tipo === 'BOLETO') {
    $sqlCor = "SELECT cor FROM pagamento_boleto WHERE veiculo_id = ? AND status = ? AND data_expiracao = ? LIMIT 1";
    $stmtCor = $conn->prepare($sqlCor);
    $stmtCor->bind_param('iss', $veiculo_id, $status, $data_expiracao);
    $stmtCor->execute();
    $resCor = $stmtCor->get_result();
    if ($rowCor = $resCor->fetch_assoc()) {
        $cor_venda = $rowCor['cor'];
    }
} elseif ($tipo === 'CARTAO') {
    $sqlCor = "SELECT cor FROM pagamentos_cartao WHERE veiculo_id = ? AND status = ? ORDER BY id DESC LIMIT 1";
    $stmtCor = $conn->prepare($sqlCor);
    $stmtCor->bind_param('is', $veiculo_id, $status);
    $stmtCor->execute();
    $resCor = $stmtCor->get_result();
    if ($rowCor = $resCor->fetch_assoc()) {
        $cor_venda = $rowCor['cor'];
    }
}

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

// Busca dados do pagamento cartão se for tipo CARTAO
$parcelas = 1;
$valor_pagamento = 0;
$bandeira = '';
$final_cartao = '';
$nome_impresso = '';
$tem_juros = false;
$valor_parcela = 0;
$data_criacao_cartao = '';
$numero_chassi = '';
$status_veiculo = '';
if ($tipo === 'CARTAO') {
    // Busca o veículo vendido pelo vínculo id_pagamento/tipo_pagamento
    $sqlVeic = "SELECT id, numero_chassi, status, id_pagamento FROM veiculos WHERE id_pagamento IS NOT NULL AND tipo_pagamento = 'CARTAO' AND id_pagamento IN (SELECT id FROM pagamentos_cartao WHERE status = ? AND veiculo_id IN (SELECT id FROM veiculos WHERE modelo_id = ?)) ORDER BY id DESC LIMIT 1";
    $stmtVeic = $conn->prepare($sqlVeic);
    $stmtVeic->bind_param('si', $status, $modelo_id);
    $stmtVeic->execute();
    $resVeic = $stmtVeic->get_result();
    if ($rowVeic = $resVeic->fetch_assoc()) {
        $numero_chassi = $rowVeic['numero_chassi'] ?? '';
        $status_veiculo = $rowVeic['status'] ?? '';
        $id_pagamento_cartao = $rowVeic['id_pagamento'];
        // Busca o pagamento do cartão vinculado ao veículo
        $sqlCartao = "SELECT id, valor, parcelas, bandeira, numero_cartao_final, nome_impresso, cor, veiculo_id FROM pagamentos_cartao WHERE id = ? LIMIT 1";
        $stmtCartao = $conn->prepare($sqlCartao);
        $stmtCartao->bind_param('i', $id_pagamento_cartao);
        $stmtCartao->execute();
        $resCartao = $stmtCartao->get_result();
        if ($rowCartao = $resCartao->fetch_assoc()) {
            $valor_pagamento = floatval($rowCartao['valor']);
            $parcelas = intval($rowCartao['parcelas']);
            $bandeira = $rowCartao['bandeira'] ?? '';
            $final_cartao = $rowCartao['numero_cartao_final'] ?? '';
            $nome_impresso = $rowCartao['nome_impresso'] ?? '';
            if (!empty($rowCartao['cor'])) {
                $cor_venda = $rowCartao['cor'];
            }
            // Juros: a partir de 11x é sempre com juros
            $valor_parcela = $parcelas > 0 ? ($valor_pagamento / $parcelas) : $valor_pagamento;
            $tem_juros = ($parcelas > 10); // 11x, 12x, 24x sempre com juros
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
$pdf->Cell(50,8,'Preco:',0,0);
if ($tipo === 'CARTAO') {
    $preco_str = 'R$ '.number_format($valor_pagamento,2,',','.');
    $info_parcelas = $parcelas > 1 ? ("{$parcelas}x de R$ ".number_format($valor_parcela,2,',','.')) : '';
    $info_juros = $tem_juros ? ' (com juros)' : ' (sem juros)';
    $pdf->Cell(0,8,($info_parcelas ? $info_parcelas.' = ' : '').$preco_str.$info_juros,0,1);
    // Detalhes do cartão
    $pdf->Cell(50,8,'Bandeira:',0,0); $pdf->Cell(0,8,ucfirst($bandeira),0,1);
    $pdf->Cell(50,8,'Final do Cartao:',0,0); $pdf->Cell(0,8,$final_cartao,0,1);
    $pdf->Cell(50,8,'Nome impresso:',0,0); $pdf->Cell(0,8,$nome_impresso,0,1);
} else {
    $pdf->Cell(0,8,'R$ '.number_format($carro['preco'] ?? 0,2,',','.'),0,1);
}
$pdf->Cell(50,8,'Tipo de Pagamento:',0,0); $pdf->Cell(0,8,$tipo,0,1);
$pdf->Cell(50,8,'Status:',0,0); $pdf->Cell(0,8,ucfirst($status),0,1);
$pdf->Cell(50,8,'Data de Criacao:',0,0); $pdf->Cell(0,8,($tipo === 'CARTAO' ? '-' : ($data_criacao ? date('d/m/Y H:i', strtotime($data_criacao)) : '-')),0,1);
$pdf->Cell(50,8,'Data de Expiracao:',0,0); $pdf->Cell(0,8,($data_expiracao ? date('d/m/Y H:i', strtotime($data_expiracao)) : '-'),0,1);

// Busca o veículo vendido pelo vínculo id_pagamento/tipo_pagamento para qualquer tipo
$numero_chassi = '';
$status_veiculo = '';
$id_pagamento = null;
if (in_array($tipo, ['CARTAO', 'PIX', 'BOLETO'])) {
    $tipo_busca = strtoupper($tipo);
    $sqlVeic = "SELECT id, numero_chassi, status, id_pagamento FROM veiculos WHERE id_pagamento IS NOT NULL AND tipo_pagamento = ? AND id_pagamento = ? LIMIT 1";
    $id_pagamento = null;
    // Descobre o id do pagamento para o tipo
    if ($tipo_busca === 'CARTAO') {
        $sqlPag = "SELECT id FROM pagamentos_cartao WHERE veiculo_id = ? AND status = ? ORDER BY id DESC LIMIT 1";
    } elseif ($tipo_busca === 'PIX') {
        $sqlPag = "SELECT id FROM pagamentos_pix WHERE veiculo_id = ? AND status = ? AND expira_em = ? ORDER BY id DESC LIMIT 1";
    } elseif ($tipo_busca === 'BOLETO') {
        $sqlPag = "SELECT id FROM pagamento_boleto WHERE veiculo_id = ? AND status = ? AND data_expiracao = ? ORDER BY id DESC LIMIT 1";
    }
    $stmtPag = $conn->prepare($sqlPag);
    if ($tipo_busca === 'CARTAO') {
        $stmtPag->bind_param('is', $veiculo_id, $status);
    } elseif ($tipo_busca === 'PIX') {
        $stmtPag->bind_param('iss', $veiculo_id, $status, $data_expiracao);
    } elseif ($tipo_busca === 'BOLETO') {
        $stmtPag->bind_param('iss', $veiculo_id, $status, $data_expiracao);
    }
    $stmtPag->execute();
    $resPag = $stmtPag->get_result();
    if ($rowPag = $resPag->fetch_assoc()) {
        $id_pagamento = $rowPag['id'];
    }
    if ($id_pagamento) {
        $stmtVeic = $conn->prepare($sqlVeic);
        $stmtVeic->bind_param('si', $tipo_busca, $id_pagamento);
        $stmtVeic->execute();
        $resVeic = $stmtVeic->get_result();
        if ($rowVeic = $resVeic->fetch_assoc()) {
            $numero_chassi = $rowVeic['numero_chassi'] ?? '';
            $status_veiculo = $rowVeic['status'] ?? '';
        }
    }
}

// Exibe detalhes do veículo vendido se encontrado
if (!empty($numero_chassi) && !empty($status_veiculo)) {
    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'Detalhes do Veiculo:',0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(50,8,'Chassi:',0,0); $pdf->Cell(0,8,$numero_chassi,0,1);
    $pdf->Cell(50,8,'Cor:',0,0); $pdf->Cell(0,8,($cor_venda !== '' ? $cor_venda : '-'),0,1);
    $pdf->Cell(50,8,'Status do Veiculo:',0,0); $pdf->Cell(0,8,ucfirst($status_veiculo),0,1);
} elseif (!empty($cor_venda)) {
    $pdf->Ln(6);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,8,'Detalhes do Veiculo:',0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(50,8,'Cor:',0,0); $pdf->Cell(0,8,($cor_venda !== '' ? $cor_venda : '-'),0,1);
}

// Exibe o PDF no navegador, sem salvar no servidor
$pdf->Output('I', 'relatorio_pagamento.pdf');
exit;