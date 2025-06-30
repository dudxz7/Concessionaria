<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/fpdf/fpdf.php';

// Define timezone para America/Sao_Paulo
@date_default_timezone_set('America/Sao_Paulo');

// Recebe dados do formulário
$nome_relatorio = $_POST['report-name'] ?? '';
$tipo_relatorio = $_POST['report-type'] ?? '';
$data_inicio = $_POST['data-inicio'] ?? null;
$data_fim = $_POST['data-fim'] ?? null;
$data_geracao = date('Y-m-d H:i:s');

// Gera o conteúdo do relatório conforme o tipo
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,utf8_decode($nome_relatorio),0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,utf8_decode('Tipo: '.relatorio_tipo_nome($tipo_relatorio)),0,1,'C');
$pdf->Cell(0,10,utf8_decode('Gerado em: '.date('d/m/Y H:i')),0,1,'C');
$pdf->Ln(8);

switch ($tipo_relatorio) {
    case 'vendas_intervalo':
        $pdf->Cell(0,8,utf8_decode('Período: '.date('d/m/Y', strtotime($data_inicio)).' a '.date('d/m/Y', strtotime($data_fim))),0,1);
        $total = 0; $valor = 0;
        // Pix
        $sql = "SELECT id, valor, criado_em, veiculo_id, usuario_id, status FROM pagamentos_pix WHERE status = 'APROVADO' AND criado_em BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $data_inicio, $data_fim);
        $stmt->execute(); $res = $stmt->get_result();
        $vendas_pix = $res->fetch_all(MYSQLI_ASSOC);
        $total += count($vendas_pix); $valor += array_sum(array_column($vendas_pix, 'valor'));
        // Boleto
        $sql = "SELECT id, valor, data_criacao, veiculo_id, usuario_id, status FROM pagamento_boleto WHERE status = 'APROVADO' AND data_criacao BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $data_inicio, $data_fim);
        $stmt->execute(); $res = $stmt->get_result();
        $vendas_boleto = $res->fetch_all(MYSQLI_ASSOC);
        $total += count($vendas_boleto); $valor += array_sum(array_column($vendas_boleto, 'valor'));
        // Cartão
        $sql = "SELECT id, valor, veiculo_id, status FROM pagamentos_cartao WHERE status = 'APROVADO'";
        $res = $conn->query($sql);
        $vendas_cartao = $res->fetch_all(MYSQLI_ASSOC);
        $total += count($vendas_cartao); $valor += array_sum(array_column($vendas_cartao, 'valor'));
        // Físicas
        $sql = "SELECT v.id, v.total, v.data_venda, v.veiculo_id, v.cliente_id, v.forma_pagamento, c.nome_completo as funcionario, cli.nome_completo as cliente FROM vendas_fisicas v JOIN clientes c ON v.usuario_id = c.id LEFT JOIN clientes cli ON v.cliente_id = cli.id WHERE v.data_venda BETWEEN ? AND ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $data_inicio, $data_fim);
        $stmt->execute(); $res = $stmt->get_result();
        $vendas_fisicas = $res->fetch_all(MYSQLI_ASSOC);
        $total += count($vendas_fisicas); $valor += array_sum(array_column($vendas_fisicas, 'total'));
        $pdf->Cell(0,8,utf8_decode('Total de vendas: '.($total ?? 0)),0,1);
        $pdf->Cell(0,8,utf8_decode('Valor total: R$ '.number_format($valor ?? 0,2,',','.')),0,1);
        $pdf->Ln(4);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,8,utf8_decode('Detalhamento das vendas:'),0,1);
        $pdf->SetFont('Arial','',11);
        // Pix
        foreach ($vendas_pix as $v) {
            $modelo = '-'; $cliente = '-';
            if (!empty($v['veiculo_id'])) {
                $sqlMod = "SELECT m.modelo FROM veiculos ve JOIN modelos m ON ve.modelo_id = m.id WHERE ve.id = ?";
                $stmt = $conn->prepare($sqlMod);
                $stmt->bind_param('i', $v['veiculo_id']);
                $stmt->execute(); $resMod = $stmt->get_result();
                if ($mod = $resMod->fetch_assoc()) $modelo = $mod['modelo'];
            }
            if (!empty($v['usuario_id'])) {
                $sqlCli = "SELECT nome_completo FROM clientes WHERE id = ?";
                $stmt = $conn->prepare($sqlCli);
                $stmt->bind_param('i', $v['usuario_id']);
                $stmt->execute(); $resCli = $stmt->get_result();
                if ($cli = $resCli->fetch_assoc()) $cliente = $cli['nome_completo'];
            }
            $pdf->MultiCell(0,8,utf8_decode('ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['criado_em'])).' | Valor: R$ '.number_format($v['valor'],2,',','.').' | Modelo: '.$modelo.' | Cliente: '.$cliente.' | Origem: Sistema (Pix) | Status: '.$v['status']));
        }
        // Boleto
        foreach ($vendas_boleto as $v) {
            $modelo = '-'; $cliente = '-';
            if (!empty($v['veiculo_id'])) {
                $sqlMod = "SELECT m.modelo FROM veiculos ve JOIN modelos m ON ve.modelo_id = m.id WHERE ve.id = ?";
                $stmt = $conn->prepare($sqlMod);
                $stmt->bind_param('i', $v['veiculo_id']);
                $stmt->execute(); $resMod = $stmt->get_result();
                if ($mod = $resMod->fetch_assoc()) $modelo = $mod['modelo'];
            }
            if (!empty($v['usuario_id'])) {
                $sqlCli = "SELECT nome_completo FROM clientes WHERE id = ?";
                $stmt = $conn->prepare($sqlCli);
                $stmt->bind_param('i', $v['usuario_id']);
                $stmt->execute(); $resCli = $stmt->get_result();
                if ($cli = $resCli->fetch_assoc()) $cliente = $cli['nome_completo'];
            }
            $pdf->MultiCell(0,8,utf8_decode('ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['data_criacao'])).' | Valor: R$ '.number_format($v['valor'],2,',','.').' | Modelo: '.$modelo.' | Cliente: '.$cliente.' | Origem: Sistema (Boleto) | Status: '.$v['status']));
        }
        // Cartão
        foreach ($vendas_cartao as $v) {
            $modelo = '-';
            if (!empty($v['veiculo_id'])) {
                $sqlMod = "SELECT m.modelo FROM veiculos ve JOIN modelos m ON ve.modelo_id = m.id WHERE ve.id = ?";
                $stmt = $conn->prepare($sqlMod);
                $stmt->bind_param('i', $v['veiculo_id']);
                $stmt->execute(); $resMod = $stmt->get_result();
                if ($mod = $resMod->fetch_assoc()) $modelo = $mod['modelo'];
            }
            $pdf->MultiCell(0,8,utf8_decode('ID: '.$v['id'].' | Data: - | Valor: R$ '.number_format($v['valor'],2,',','.').' | Modelo: '.$modelo.' | Cliente: - | Origem: Sistema (Cartão) | Status: '.$v['status']));
        }
        // Físicas
        foreach ($vendas_fisicas as $v) {
            $modelo = '-';
            if (!empty($v['veiculo_id'])) {
                $sqlMod = "SELECT m.modelo FROM veiculos ve JOIN modelos m ON ve.modelo_id = m.id WHERE ve.id = ?";
                $stmt = $conn->prepare($sqlMod);
                $stmt->bind_param('i', $v['veiculo_id']);
                $stmt->execute(); $resMod = $stmt->get_result();
                if ($mod = $resMod->fetch_assoc()) $modelo = $mod['modelo'];
            }
            $cliente = $v['cliente'] ?? '-';
            $forma = $v['forma_pagamento'] ?? '-';
            $pdf->MultiCell(0,8,utf8_decode('ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['data_venda'])).' | Valor: R$ '.number_format($v['total'],2,',','.').' | Modelo: '.$modelo.' | Cliente: '.$cliente.' | Origem: Funcionário ('.$v['funcionario'].') | Forma: '.$forma));
        }
        break;
    case 'vendas_funcionario':
        $pdf->Cell(0,8,utf8_decode('Vendas físicas por funcionários:'),0,1);
        // Busca todos os funcionários que fizeram vendas
        $sql = "SELECT c.id as funcionario_id, c.nome_completo, COUNT(v.id) as total, SUM(v.total) as valor_total FROM vendas_fisicas v JOIN clientes c ON v.usuario_id = c.id GROUP BY c.id, c.nome_completo";
        $res = $conn->query($sql);
        $percentual_comissao = 0.005; // 0,5% de comissão
        while ($row = $res->fetch_assoc()) {
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0,8,utf8_decode($row['nome_completo'].': '.$row['total'].' vendas'),0,1);
            $pdf->SetFont('Arial','',10);
            $valor_total = $row['valor_total'] ?? 0;
            $comissao = $valor_total * $percentual_comissao;
            $pdf->Cell(0,8,utf8_decode('Total vendido: R$ '.number_format($valor_total,2,',','.').' | Comissão (0,5%): R$ '.number_format($comissao,2,',','.')),0,1);
            // Detalha as vendas desse funcionário
            $sqlDet = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, ve.id as veiculo_id, m.modelo FROM vendas_fisicas v JOIN clientes cli ON v.cliente_id = cli.id JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id WHERE v.usuario_id = ? ORDER BY v.data_venda DESC";
            $stmt = $conn->prepare($sqlDet);
            $stmt->bind_param('i', $row['funcionario_id']);
            $stmt->execute(); $resDet = $stmt->get_result();
            while ($v = $resDet->fetch_assoc()) {
                $pdf->MultiCell(0,8,utf8_decode('   ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['data_venda'])).' | Valor: R$ '.number_format($v['total'],2,',','.').' | Modelo: '.$v['modelo'].' | Cliente: '.$v['cliente'].' | Forma: '.$v['forma_pagamento']));
            }
            $pdf->Ln(2);
        }
        // Funcionários sem vendas
        $sqlSemVendas = "SELECT nome_completo FROM clientes WHERE cargo = 'Funcionario' AND id NOT IN (SELECT usuario_id FROM vendas_fisicas)";
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
        break;
    case 'vendas_modelo':
        $pdf->Cell(0,8,utf8_decode('Vendas físicas por modelo de carro:'),0,1);
        // Busca
        $sql = "SELECT m.id as modelo_id, m.modelo, COUNT(v.id) as total FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id GROUP BY m.id, m.modelo";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0,8,utf8_decode($row['modelo'].': '.$row['total'].' vendas'),0,1);
            $pdf->SetFont('Arial','',10);
            // Detalha as vendas desse modelo
            $sqlDet = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, c.nome_completo as funcionario FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id JOIN clientes cli ON v.cliente_id = cli.id JOIN clientes c ON v.usuario_id = c.id WHERE m.id = ? ORDER BY v.data_venda DESC";
            $stmt = $conn->prepare($sqlDet);
            $stmt->bind_param('i', $row['modelo_id']);
            $stmt->execute(); $resDet = $stmt->get_result();
            while ($v = $resDet->fetch_assoc()) {
                $pdf->MultiCell(0,8,utf8_decode('   ID: '.$v['id'].' | Data: '.date('d/m/Y H:i', strtotime($v['data_venda'])).' | Valor: R$ '.number_format($v['total'],2,',','.').' | Cliente: '.$v['cliente'].' | Funcionário: '.$v['funcionario'].' | Forma: '.$v['forma_pagamento']));
            }
            $pdf->Ln(2);
        }
        break;
    case 'estoque':
        $pdf->Cell(0,8,utf8_decode('Situação do estoque:'),0,1);
        // Busca todos os modelos com veículos disponíveis
        $sql = "SELECT m.id as modelo_id, m.modelo, COUNT(v.id) as qtd FROM veiculos v JOIN modelos m ON v.modelo_id = m.id WHERE v.status = 'DISPONIVEL' GROUP BY m.id, m.modelo";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $pdf->SetFont('Arial','B',11);
            $pdf->Cell(0,8,utf8_decode($row['modelo'].': '.$row['qtd'].' disponíveis'),0,1);
            $pdf->SetFont('Arial','',10);
            // Detalha os chassis disponíveis desse modelo
            $sqlDet = "SELECT numero_chassi FROM veiculos WHERE modelo_id = ? AND status = 'DISPONIVEL'";
            $stmt = $conn->prepare($sqlDet);
            $stmt->bind_param('i', $row['modelo_id']);
            $stmt->execute(); $resDet = $stmt->get_result();
            $chassis = [];
            while ($v = $resDet->fetch_assoc()) {
                $chassis[] = $v['numero_chassi'];
            }
            if (count($chassis) > 0) {
                $pdf->MultiCell(0,8,utf8_decode('   Chassis disponíveis: '.implode(', ', $chassis)));
            }
            $pdf->Ln(2);
        }
        break;
    case 'promocoes':
        $pdf->Cell(0,8,utf8_decode('PROMOÇÕES ATIVAS:'),0,1);
        $hoje = date('Y-m-d');
        // Promoções ativas e não expiradas
        $sql = "SELECT p.id, m.id as modelo_id, m.modelo, p.desconto, p.preco_com_desconto, p.data_limite, p.status FROM promocoes p JOIN modelos m ON p.modelo_id = m.id WHERE p.status = 'Ativa' AND p.data_limite >= ? ORDER BY p.data_limite DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $hoje);
        $stmt->execute();
        $res = $stmt->get_result();
        $temAtiva = false;
        while ($row = $res->fetch_assoc()) {
            $temAtiva = true;
            // Calcula data de início da promoção (7 dias antes da data_limite)
            $data_fim = $row['data_limite'];
            $data_inicio = date('Y-m-d', strtotime($data_fim.' -6 days'));
            // Busca vendas do modelo no período da promoção
            $sqlVendas = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, c.nome_completo as funcionario FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN clientes cli ON v.cliente_id = cli.id JOIN clientes c ON v.usuario_id = c.id WHERE ve.modelo_id = ? AND v.data_venda BETWEEN ? AND ? UNION ALL SELECT p.id, p.criado_em as data_venda, p.valor as total, 'Pix' as forma_pagamento, cli.nome_completo as cliente, NULL as funcionario FROM pagamentos_pix p JOIN veiculos ve ON p.veiculo_id = ve.id LEFT JOIN clientes cli ON p.usuario_id = cli.id WHERE ve.modelo_id = ? AND p.status = 'APROVADO' AND p.criado_em BETWEEN ? AND ? UNION ALL SELECT b.id, b.data_criacao as data_venda, b.valor as total, 'Boleto' as forma_pagamento, cli.nome_completo as cliente, NULL as funcionario FROM pagamento_boleto b JOIN veiculos ve ON b.veiculo_id = ve.id LEFT JOIN clientes cli ON b.usuario_id = cli.id WHERE ve.modelo_id = ? AND b.status = 'APROVADO' AND b.data_criacao BETWEEN ? AND ? UNION ALL SELECT c.id, NULL as data_venda, c.valor as total, 'Cartão' as forma_pagamento, NULL as cliente, NULL as funcionario FROM pagamentos_cartao c JOIN veiculos ve ON c.veiculo_id = ve.id WHERE ve.modelo_id = ? AND c.status = 'APROVADO'";
            $stmt2 = $conn->prepare($sqlVendas);
            $stmt2->bind_param('issississi', $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id']);
            $stmt2->execute(); $resVendas = $stmt2->get_result();
            $vendas = $resVendas->fetch_all(MYSQLI_ASSOC);
            $qtdVendas = count($vendas);
            $infoVendas = $qtdVendas > 0 ? 'Vendas durante a promoção: '.$qtdVendas : 'Nenhuma venda durante a promoção';
            $pdf->MultiCell(0,8,utf8_decode($row['modelo'].' | Desconto: '.$row['desconto'].'% | Preço com desconto: R$ '.number_format($row['preco_com_desconto'],2,',','.').' | Até: '.date('d/m/Y', strtotime($row['data_limite'])).' | Status: '.$row['status'].' | '.$infoVendas));
            if ($qtdVendas > 0) {
                foreach ($vendas as $v) {
                    $origem = $v['funcionario'] ? 'Funcionário ('.$v['funcionario'].')' : 'Sistema';
                    $cliente = $v['cliente'] ?? '-';
                    $forma = $v['forma_pagamento'] ?? '-';
                    $dataVenda = $v['data_venda'] ? date('d/m/Y H:i', strtotime($v['data_venda'])) : '-';
                    $pdf->MultiCell(0,8,utf8_decode('   ID: '.$v['id'].' | Data: '.$dataVenda.' | Valor: R$ '.number_format($v['total'],2,',','.').' | Cliente: '.$cliente.' | Origem: '.$origem.' | Forma: '.$forma));
                }
            }
        }
        if (!$temAtiva) {
            $pdf->Cell(0,8,utf8_decode('Nenhuma promoção ativa.'),0,1);
        }
        $pdf->Ln(4);
        $pdf->Cell(0,8,utf8_decode('PROMOÇÕES INATIVAS OU EXPIRADAS:'),0,1);
        // Promoções inativas ou expiradas
        $sql = "SELECT p.id, m.id as modelo_id, m.modelo, p.desconto, p.preco_com_desconto, p.data_limite, p.status FROM promocoes p JOIN modelos m ON p.modelo_id = m.id WHERE p.status = 'Inativa' OR p.data_limite < ? ORDER BY p.data_limite DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $hoje);
        $stmt->execute();
        $res = $stmt->get_result();
        $temInativa = false;
        while ($row = $res->fetch_assoc()) {
            $temInativa = true;
            $data_fim = $row['data_limite'];
            $data_inicio = date('Y-m-d', strtotime($data_fim.' -6 days'));
            $sqlVendas = "SELECT v.id, v.data_venda, v.total, v.forma_pagamento, cli.nome_completo as cliente, c.nome_completo as funcionario FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN clientes cli ON v.cliente_id = cli.id JOIN clientes c ON v.usuario_id = c.id WHERE ve.modelo_id = ? AND v.data_venda BETWEEN ? AND ? UNION ALL SELECT p.id, p.criado_em as data_venda, p.valor as total, 'Pix' as forma_pagamento, cli.nome_completo as cliente, NULL as funcionario FROM pagamentos_pix p JOIN veiculos ve ON p.veiculo_id = ve.id LEFT JOIN clientes cli ON p.usuario_id = cli.id WHERE ve.modelo_id = ? AND p.status = 'APROVADO' AND p.criado_em BETWEEN ? AND ? UNION ALL SELECT b.id, b.data_criacao as data_venda, b.valor as total, 'Boleto' as forma_pagamento, cli.nome_completo as cliente, NULL as funcionario FROM pagamento_boleto b JOIN veiculos ve ON b.veiculo_id = ve.id LEFT JOIN clientes cli ON b.usuario_id = cli.id WHERE ve.modelo_id = ? AND b.status = 'APROVADO' AND b.data_criacao BETWEEN ? AND ? UNION ALL SELECT c.id, NULL as data_venda, c.valor as total, 'Cartão' as forma_pagamento, NULL as cliente, NULL as funcionario FROM pagamentos_cartao c JOIN veiculos ve ON c.veiculo_id = ve.id WHERE ve.modelo_id = ? AND c.status = 'APROVADO'";
            $stmt2 = $conn->prepare($sqlVendas);
            $stmt2->bind_param('issississi', $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id'], $data_inicio, $data_fim, $row['modelo_id']);
            $stmt2->execute(); $resVendas = $stmt2->get_result();
            $vendas = $resVendas->fetch_all(MYSQLI_ASSOC);
            $qtdVendas = count($vendas);
            $infoVendas = $qtdVendas > 0 ? 'Vendas durante a promoção: '.$qtdVendas : 'Nenhuma venda durante a promoção';
            $pdf->MultiCell(0,8,utf8_decode($row['modelo'].' | Desconto: '.$row['desconto'].'% | Preço com desconto: R$ '.number_format($row['preco_com_desconto'],2,',','.').' | Até: '.date('d/m/Y', strtotime($row['data_limite'])).' | Status: '.$row['status'].' | '.$infoVendas));
            if ($qtdVendas > 0) {
                foreach ($vendas as $v) {
                    $origem = $v['funcionario'] ? 'Funcionário ('.$v['funcionario'].')' : 'Sistema';
                    $cliente = $v['cliente'] ?? '-';
                    $forma = $v['forma_pagamento'] ?? '-';
                    $dataVenda = $v['data_venda'] ? date('d/m/Y H:i', strtotime($v['data_venda'])) : '-';
                    $pdf->MultiCell(0,8,utf8_decode('   ID: '.$v['id'].' | Data: '.$dataVenda.' | Valor: R$ '.number_format($v['total'],2,',','.').' | Cliente: '.$cliente.' | Origem: '.$origem.' | Forma: '.$forma));
                }
            }
        }
        if (!$temInativa) {
            $pdf->Cell(0,8,utf8_decode('Nenhuma promoção inativa ou expirada.'),0,1);
        }
        break;
    case 'clientes':
        $pdf->Cell(0,8,utf8_decode('Relatório detalhado dos clientes:'),0,1);
        $sql = "SELECT c.id, c.nome_completo, c.email, c.telefone, c.cidade, c.estado, c.registrado_em FROM clientes c WHERE c.cargo = 'Cliente' ORDER BY c.registrado_em DESC";
        $res = $conn->query($sql);
        while ($cli = $res->fetch_assoc()) {
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(0,8,utf8_decode('Nome: '.$cli['nome_completo']),0,1);
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(0,8,utf8_decode('Email: '.$cli['email']),0,1);
            $pdf->Cell(0,8,utf8_decode('Telefone: '.$cli['telefone']),0,1);
            $pdf->Cell(0,8,utf8_decode('Cidade/Estado: '.$cli['cidade'].'/'.$cli['estado']),0,1);
            $pdf->Cell(0,8,utf8_decode('Cadastrado em: '.date('d/m/Y', strtotime($cli['registrado_em']))),0,1);
            // Compras
            $sqlCompras = "SELECT COUNT(*) as total, SUM(total) as valor FROM vendas_fisicas WHERE cliente_id = ?";
            $stmt = $conn->prepare($sqlCompras);
            $stmt->bind_param('i', $cli['id']);
            $stmt->execute(); $resComp = $stmt->get_result();
            $compra = $resComp->fetch_assoc();
            if ($compra['total'] > 0) {
                $pdf->Cell(0,8,utf8_decode('Comprou: Sim | Qtde: '.$compra['total'].' | Valor total: R$ '.number_format($compra['valor']??0,2,',','.')),0,1);
                // Última compra
                $sqlUlt = "SELECT total, data_venda, veiculo_id FROM vendas_fisicas WHERE cliente_id = ? ORDER BY data_venda DESC LIMIT 1";
                $stmt = $conn->prepare($sqlUlt);
                $stmt->bind_param('i', $cli['id']);
                $stmt->execute(); $resUlt = $stmt->get_result();
                if ($ult = $resUlt->fetch_assoc()) {
                    $sqlMod = "SELECT m.modelo FROM veiculos v JOIN modelos m ON v.modelo_id = m.id WHERE v.id = ?";
                    $stmt2 = $conn->prepare($sqlMod);
                    $stmt2->bind_param('i', $ult['veiculo_id']);
                    $stmt2->execute(); $resMod = $stmt2->get_result();
                    $mod = $resMod->fetch_assoc();
                    $pdf->Cell(0,8,utf8_decode('Última compra: '.date('d/m/Y', strtotime($ult['data_venda'])).' | Valor: R$ '.number_format($ult['total'],2,',','.').' | Modelo: '.($mod['modelo']??'-')),0,1);
                }
                // Modelos comprados
                $sqlMods = "SELECT DISTINCT m.modelo FROM vendas_fisicas v JOIN veiculos ve ON v.veiculo_id = ve.id JOIN modelos m ON ve.modelo_id = m.id WHERE v.cliente_id = ?";
                $stmt = $conn->prepare($sqlMods);
                $stmt->bind_param('i', $cli['id']);
                $stmt->execute(); $resMods = $stmt->get_result();
                $comprados = [];
                while ($row = $resMods->fetch_assoc()) {
                    $comprados[] = $row['modelo'];
                }
                $pdf->MultiCell(0,8,utf8_decode('Modelos comprados: '.(count($comprados) ? implode(', ', $comprados) : '-')));
                // Formas de pagamento
                $sqlPag = "SELECT forma_pagamento, COUNT(*) as qtd FROM vendas_fisicas WHERE cliente_id = ? GROUP BY forma_pagamento";
                $stmt = $conn->prepare($sqlPag);
                $stmt->bind_param('i', $cli['id']);
                $stmt->execute(); $resPag = $stmt->get_result();
                $formas = [];
                while ($row = $resPag->fetch_assoc()) {
                    $formas[] = $row['forma_pagamento'].' ('.$row['qtd'].'x)';
                }
                $pdf->Cell(0,8,utf8_decode('Formas de pagamento: '.(count($formas) ? implode(', ', $formas) : '-')),0,1);
            } else {
                $pdf->Cell(0,8,utf8_decode('Comprou: Não'),0,1);
            }
            // Favoritos
            $sqlFav = "SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = ?";
            $stmt = $conn->prepare($sqlFav);
            $stmt->bind_param('i', $cli['id']);
            $stmt->execute(); $resFav = $stmt->get_result();
            $fav = $resFav->fetch_assoc();
            if ($fav['total'] > 0) {
                $pdf->Cell(0,8,utf8_decode('Favoritou: Sim | Qtde: '.$fav['total']),0,1);
                $sqlFavList = "SELECT m.modelo FROM favoritos f JOIN modelos m ON f.modelo_id = m.id WHERE f.usuario_id = ?";
                $stmt = $conn->prepare($sqlFavList);
                $stmt->bind_param('i', $cli['id']);
                $stmt->execute(); $resFavList = $stmt->get_result();
                $modelos = [];
                while ($row = $resFavList->fetch_assoc()) {
                    $modelos[] = $row['modelo'];
                }
                $pdf->MultiCell(0,8,utf8_decode('Modelos favoritados: '.(count($modelos) ? implode(', ', $modelos) : '-')));
            } else {
                $pdf->Cell(0,8,utf8_decode('Favoritou: Não'),0,1);
            }
            $pdf->Ln(2);
        }
        break;
    default:
        $pdf->Cell(0,8,utf8_decode('Tipo de relatório não reconhecido.'),0,1);
}

// Salva o PDF em disco (pasta relatorios)
$nome_arquivo = 'relatorio_'.date('Ymd_His').'_'.uniqid().'.pdf';
$caminho = __DIR__ . '/../relatorios/'.$nome_arquivo;
$pdf->Output('F', $caminho);

// Salva no histórico (tabela historico_relatorios)
$sqlHist = "INSERT INTO historico_relatorios (nome, tipo, caminho, data_geracao) VALUES (?, ?, ?, ?)";
$stmtHist = $conn->prepare($sqlHist);
$stmtHist->bind_param('ssss', $nome_relatorio, $tipo_relatorio, $nome_arquivo, $data_geracao);
$stmtHist->execute();

// Redireciona para download ou visualização
header('Location: ../relatorios/'.$nome_arquivo);
exit;

function relatorio_tipo_nome($tipo) {
    switch ($tipo) {
        case 'vendas_intervalo': return 'Total de vendas em um intervalo de datas';
        case 'vendas_funcionario': return 'Vendas por funcionários';
        case 'vendas_modelo': return 'Vendas por modelo de carro';
        case 'estoque': return 'Situação do estoque';
        case 'promocoes': return 'Relatório das promoções';
        case 'clientes': return 'Relatório total dos clientes';
        default: return 'Desconhecido';
    }
}
