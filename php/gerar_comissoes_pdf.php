<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/fpdf/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_relatorio = $_POST['tipo_relatorio'] ?? 'vendas_funcionario';
    $data_ini = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;
    $percentual_comissao = 0.005; // 0,5%
    $id_funcionario = isset($_POST['funcionario']) && $_POST['funcionario'] !== '' ? intval($_POST['funcionario']) : null;

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,utf8_decode('Relatório de Comissões'),0,1,'C');
    if ($data_ini && $data_fim) {
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,8,utf8_decode('Período: ') . date('d/m/Y', strtotime($data_ini)) . utf8_decode(' a ') . date('d/m/Y', strtotime($data_fim)),0,1);
    }
    $pdf->Ln(4);

    switch ($tipo_relatorio) {
        case 'vendas_funcionario':
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(0,8,utf8_decode('Vendas físicas por funcionários:'),0,1);
            // Cabeçalho da tabela
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(60,8,utf8_decode('Funcionário'),1);
            $pdf->Cell(30,8,utf8_decode('Qtde Vendas'),1);
            $pdf->Cell(40,8,utf8_decode('Valor Total (R$)'),1);
            $pdf->Cell(40,8,utf8_decode('Comissão (R$)'),1);
            $pdf->Ln();
            $pdf->SetFont('Arial','',10);
            // Busca vendas do funcionário selecionado OU de todos
            if ($id_funcionario) {
                $sql = "SELECT c.id as funcionario_id, c.nome_completo, COUNT(v.id) as total, SUM(v.total) as valor_total FROM vendas_fisicas v JOIN clientes c ON v.usuario_id = c.id WHERE c.id = $id_funcionario ";
                if ($data_ini && $data_fim) {
                    $sql .= "AND v.data_venda BETWEEN '$data_ini' AND '$data_fim' ";
                }
                $sql .= "GROUP BY c.id, c.nome_completo";
            } else {
                $sql = "SELECT c.id as funcionario_id, c.nome_completo, COUNT(v.id) as total, SUM(v.total) as valor_total FROM vendas_fisicas v JOIN clientes c ON v.usuario_id = c.id ";
                if ($data_ini && $data_fim) {
                    $sql .= "WHERE v.data_venda BETWEEN '$data_ini' AND '$data_fim' ";
                }
                $sql .= "GROUP BY c.id, c.nome_completo";
            }
            $res = $conn->query($sql);
            $temVendas = false;
            $funcionarios = [];
            while ($row = $res->fetch_assoc()) {
                $temVendas = true;
                $valor_total = $row['valor_total'] ?? 0;
                $comissao = $valor_total * $percentual_comissao;
                $pdf->Cell(60,8,utf8_decode($row['nome_completo']),1);
                $pdf->Cell(30,8,$row['total'],1,0,'C');
                $pdf->Cell(40,8,number_format($valor_total,2,',','.'),1,0,'R');
                $pdf->Cell(40,8,number_format($comissao,2,',','.'),1,0,'R');
                $pdf->Ln();
                $funcionarios[] = $row;
            }
            if (!$temVendas) {
                $pdf->Cell(170,8,utf8_decode('Nenhum funcionário realizou vendas no período.'),1,1,'C');
            }
            $pdf->Ln(4);
            // Detalhamento das vendas por funcionário
            foreach ($funcionarios as $row) {
                $pdf->SetFont('Arial','B',11);
                $pdf->Cell(0,8,utf8_decode('Detalhamento das vendas de: '.$row['nome_completo']),0,1);
                $pdf->SetFont('Arial','B',10);
                $pdf->Cell(18,8,'ID',1);
                $pdf->Cell(32,8,'Data',1);
                $pdf->Cell(40,8,utf8_decode('Cliente'),1);
                $pdf->Cell(35,8,utf8_decode('Modelo'),1);
                $pdf->Cell(35,8,utf8_decode('Valor (R$)'),1);
                $pdf->Cell(35,8,utf8_decode('Comissão'),1);
                $pdf->Ln();
                $pdf->SetFont('Arial','',10);
                $sqlDet = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, m.modelo FROM vendas_fisicas v JOIN clientes cli ON v.cliente_id = cli.id JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id WHERE v.usuario_id = ?";
                if ($data_ini && $data_fim) {
                    $sqlDet .= " AND v.data_venda BETWEEN '$data_ini' AND '$data_fim'";
                }
                $sqlDet .= " ORDER BY v.data_venda DESC";
                $stmt = $conn->prepare($sqlDet);
                $stmt->bind_param('i', $row['funcionario_id']);
                $stmt->execute(); $resDet = $stmt->get_result();
                $temDetalhe = false;
                while ($v = $resDet->fetch_assoc()) {
                    $temDetalhe = true;
                    $comissaoVenda = $v['total'] * $percentual_comissao;
                    $pdf->Cell(18,8,$v['id'],1);
                    $pdf->Cell(32,8,date('d/m/Y H:i', strtotime($v['data_venda'])),1);
                    $pdf->Cell(40,8,utf8_decode($v['cliente']),1);
                    $pdf->Cell(35,8,utf8_decode($v['modelo']),1);
                    $pdf->Cell(35,8,number_format($v['total'],2,',','.'),1,0,'R');
                    $pdf->Cell(35,8,number_format($comissaoVenda,2,',','.'),1,0,'R');
                    $pdf->Ln();
                }
                if (!$temDetalhe) {
                    $pdf->Cell(195,8,utf8_decode('Nenhuma venda detalhada para este funcionário.'),1,1,'C');
                }
                $pdf->Ln(2);
            }
            // Só mostra funcionários sem vendas se NÃO for relatório de um funcionário específico
            if (!$id_funcionario) {
                $sqlSemVendas = "SELECT nome_completo FROM clientes WHERE cargo = 'Funcionario' AND id NOT IN (SELECT usuario_id FROM vendas_fisicas";
                if ($data_ini && $data_fim) {
                    $sqlSemVendas .= " WHERE data_venda BETWEEN '$data_ini' AND '$data_fim'";
                }
                $sqlSemVendas .= ")";
                $resSem = $conn->query($sqlSemVendas);
                $semVendas = [];
                while ($row = $resSem->fetch_assoc()) {
                    $semVendas[] = $row['nome_completo'];
                }
                if (count($semVendas) > 0) {
                    $pdf->SetFont('Arial','B',11);
                    $pdf->Cell(0,8,utf8_decode('Funcionários sem vendas:'),0,1);
                    $pdf->SetFont('Arial','',10);
                    $pdf->MultiCell(0,8,utf8_decode(implode(', ', $semVendas)));
                }
            }
            break;
        case 'vendas_modelo':
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(0,8,utf8_decode('Vendas físicas por modelo de carro:'),0,1);
            $sql = "SELECT m.id as modelo_id, m.modelo, COUNT(v.id) as total FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id ";
            if ($data_ini && $data_fim) {
                $sql .= "WHERE v.data_venda BETWEEN '$data_ini' AND '$data_fim' ";
            }
            $sql .= "GROUP BY m.id, m.modelo";
            $res = $conn->query($sql);
            while ($row = $res->fetch_assoc()) {
                $pdf->SetFont('Arial','B',11);
                $pdf->Cell(0,8,utf8_decode($row['modelo'].': '.$row['total'].' vendas'),0,1);
                $pdf->SetFont('Arial','',10);
                // Detalha as vendas desse modelo
                $sqlDet = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, c.nome_completo as funcionario FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id JOIN clientes cli ON v.cliente_id = cli.id JOIN clientes c ON v.usuario_id = c.id WHERE m.id = ?";
                if ($data_ini && $data_fim) {
                    $sqlDet .= " AND v.data_venda BETWEEN '$data_ini' AND '$data_fim'";
                }
                $sqlDet .= " ORDER BY v.data_venda DESC";
                $stmt = $conn->prepare($sqlDet);
                $stmt->bind_param('i', $row['modelo_id']);
                $stmt->execute(); $resDet = $stmt->get_result();
                while ($v = $resDet->fetch_assoc()) {
                    $pdf->MultiCell(0,8,utf8_decode('   ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['data_venda'])).' | Valor: R$ '.number_format($v['total'],2,',','.').' | Cliente: '.$v['cliente'].' | Funcionário: '.$v['funcionario'].' | Forma: '.$v['forma_pagamento']));
                }
                $pdf->Ln(2);
            }
            break;
        default:
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(0,8,utf8_decode('Tipo de relatório não reconhecido.'),0,1);
            break;
    }
    $pdf->Output('I','relatorio_comissoes.pdf');
    exit;
}
// Se não for post, redireciona
header('Location: comissoes.php');
exit;
