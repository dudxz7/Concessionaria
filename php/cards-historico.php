<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioId = $_SESSION['usuarioId'] ?? null;
if (!$usuarioId) {
    echo '<p>Usuário não autenticado.</p>';
    return;
}
require_once __DIR__ . '/conexao.php';
// Funções utilitárias (basicamente a mesma merda de cards-a-pagar.php)
if (!function_exists('encontrarImagemVeiculo')) {
    function encontrarImagemVeiculo($modelo, $cor, $nomeArquivoBase)
    {
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
    function gerarAno($ano)
    {
        return ($ano - 1) . '/' . $ano;
    }
}
if (!function_exists('gerarRating')) {
    function gerarRating()
    {
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
    function gerarNota()
    {
        return rand(1, 1500);
    }
}
// Busca histórico de pagamentos PIX (todas tentativas exceto pendentes)
$sqlPix = "SELECT veiculo_id, cor, valor, status, expira_em as data, 'PIX' as tipo FROM pagamentos_pix WHERE usuario_id = ? AND status != 'pendente' ORDER BY data DESC";
$stmtPix = $conn->prepare($sqlPix);
$stmtPix->bind_param('i', $usuarioId);
$stmtPix->execute();
$resultPix = $stmtPix->get_result();
$historicoPix = $resultPix->fetch_all(MYSQLI_ASSOC);
// Busca histórico de boletos (todas tentativas exceto pendentes)
$sqlBoleto = "SELECT veiculo_id, cor, valor, status, data_expiracao as data, 'BOLETO' as tipo FROM pagamento_boleto WHERE usuario_id = ? AND status != 'pendente' ORDER BY data DESC";
$stmtBoleto = $conn->prepare($sqlBoleto);
$stmtBoleto->bind_param('i', $usuarioId);
$stmtBoleto->execute();
$resultBoleto = $stmtBoleto->get_result();
$historicoBoleto = $resultBoleto->fetch_all(MYSQLI_ASSOC);
// Junta e ordena tudo por data desc
$historico = array_merge($historicoPix, $historicoBoleto);
usort($historico, function($a, $b) { return strtotime($b['data']) - strtotime($a['data']); });
if (empty($historico)) {
    echo '<p style="margin-left:20px;">Nenhum pagamento realizado ou expirado encontrado.</p>';
    return;
}
foreach ($historico as $pagamento) {
    // Busca dados do veículo
    $id = $pagamento['veiculo_id'];
    $cor = $pagamento['cor'] ?? '';
    $sqlModelos = "SELECT m.id, m.modelo, m.ano, m.preco, d.descricao, (
        SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = ? AND i.ordem = 1 LIMIT 1
    ) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id = ? GROUP BY m.id";
    $stmtModelos = $conn->prepare($sqlModelos);
    $stmtModelos->bind_param('si', $cor, $id);
    $stmtModelos->execute();
    $resultModelos = $stmtModelos->get_result();
    if ($carro = $resultModelos->fetch_assoc()) {
        $carro['cor_selecionada'] = $cor;
        $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
        $imagemPath = encontrarImagemVeiculo($carro['modelo'], $cor, $imagemBase);
        $anoFormatado = gerarAno($carro['ano']);
        $rating = gerarRating();
        $nota = gerarNota();
        $statusLabel = $pagamento['status'] === 'pago' ? 'Pago' : '';
        $statusColor = $pagamento['status'] === 'pago' ? '#4caf50' : '#bdbdbd';
        $tipo = $pagamento['tipo'];
        $valor = number_format($pagamento['valor'], 2, ',', '.');
        $data = date('d/m/Y H:i', strtotime($pagamento['data']));
        // Card igual ao de a_pagar.php
        echo '<div class="card">';
        echo '<div class="img-container-card-pagamento" style="position:relative;display:inline-block;width:100%;max-width:320px;">';
        // Badge de tipo de pagamento
        if ($tipo === 'PIX') {
            echo '<span class="forma-pagamento-overlay"><span class="forma-pagamento-card pix"><img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Pix"></span></span>';
        } else {
            echo '<span class="forma-pagamento-overlay"><span class="forma-pagamento-card boleto"><img src="../img/formas-de-pagamento/boletov2.png" alt="Boleto"></span></span>';
        }
        // Badge de status
        if ($statusLabel) {
            echo '<span style="position:absolute;top:10px;right:10px;background:' . $statusColor . ';color:#fff;padding:4px 12px;border-radius:12px;font-weight:bold;font-size:0.98em;z-index:2;">' . $statusLabel . '</span>';
        }
        echo '</div>';
        echo '<img src="' . htmlspecialchars('../' . $imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '" style="width:100%;max-width:320px;">';
        echo '<h2>' . htmlspecialchars($carro['modelo']) . '</h2>';
        echo '<p>' . htmlspecialchars($carro['descricao']) . '</p>';
        echo '<p><img src="../img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="../img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>';
        echo '<div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="../img/cards/' . $estrela . '" alt="estrela">';
        }
        echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span></div>';
        echo '<h2>R$ ' . $valor . '</h2>';
        // Botão STATUS mostrando o status real (Aprovado, Expirado, Cancelado) com cor forte e !important
        if ($pagamento['status'] === 'aprovado') {
            $statusBtn = 'Aprovado';
            $btnColor = 'background:linear-gradient(90deg,#43a047 0%,#90EE90 100%)!important;color:#fff!important;border:none!important;box-shadow:0 2px 12px 0 #1de9b655!important;';
        } elseif ($pagamento['status'] === 'expirado') {
            $statusBtn = 'Expirado';
            $btnColor = 'background:linear-gradient(90deg, #9e9e9e, #bdbdbd)!important;color:#fff!important;border:none!important;box-shadow:0 2px 12px 0 #ff910055!important;';
        } elseif ($pagamento['status'] === 'cancelado') {
            $statusBtn = 'Cancelado';
            $btnColor = 'background:linear-gradient(90deg, #e53935, #ef5350)!important;color:#fff!important;border:none!important;box-shadow:0 2px 12px 0 #ff616f55!important;';
        } else {
            $statusBtn = ucfirst($pagamento['status']);
            $btnColor = 'background:linear-gradient(90deg, #8b0000, #b71c1c)!important;color:#fff!important;border:none!important;';
        }
        // Pega data de criação corretamente para cada tipo
        $dataCriacao = '';
        if ($tipo === 'PIX' && isset($pagamento['expira_em'])) {
            // Para PIX, pega a data de criação do campo criado_em se existir
            $sqlPixData = "SELECT criado_em FROM pagamentos_pix WHERE veiculo_id = ? AND usuario_id = ? AND expira_em = ? LIMIT 1";
            $stmtPixData = $conn->prepare($sqlPixData);
            $stmtPixData->bind_param('iis', $id, $usuarioId, $pagamento['expira_em']);
            $stmtPixData->execute();
            $resPixData = $stmtPixData->get_result();
            if ($rowPixData = $resPixData->fetch_assoc()) {
                $dataCriacao = $rowPixData['criado_em'];
            }
        } elseif ($tipo === 'BOLETO' && isset($pagamento['data'])) {
            // Para BOLETO, pega a data de criação do campo data_criacao se existir
            $sqlBoletoData = "SELECT data_criacao FROM pagamento_boleto WHERE veiculo_id = ? AND usuario_id = ? AND data_expiracao = ? LIMIT 1";
            $stmtBoletoData = $conn->prepare($sqlBoletoData);
            $stmtBoletoData->bind_param('iis', $id, $usuarioId, $pagamento['data']);
            $stmtBoletoData->execute();
            $resBoletoData = $stmtBoletoData->get_result();
            if ($rowBoletoData = $resBoletoData->fetch_assoc()) {
                $dataCriacao = $rowBoletoData['data_criacao'];
            }
        }
        $relatorioUrl = '../php/gerar_relatorio.php?tipo=' . urlencode($tipo) . '&veiculo_id=' . urlencode($id) . '&status=' . urlencode($pagamento['status']) . '&data=' . urlencode($pagamento['data']) . '&data_criacao=' . urlencode($dataCriacao);
        echo '<a href="' . $relatorioUrl . '" target="_blank" style="text-decoration:none;display:inline-block;">';
        echo '<button class="btn-send" style="margin-bottom:8px;'.$btnColor.'font-weight:bold !important;cursor:pointer;width:100%;width:240px;">' . $statusBtn . '</button>';
        echo '</a>';
        echo '</div>';
    }
}
?>
<style>
    /* Modal Cancelar Pagamento - Moderno e elegante */
    .modal-cancelar-pagamento {
        display: none;
        position: fixed;
        z-index: 1000;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(8px);
        justify-content: center;
        align-items: center;
        width: 100vw;
        height: 100vh;
        display: flex;
    }

    .modal-cancelar-pagamento[style*="block"] {
        display: flex !important;
    }

    .modern-modal {
        background: #fff;
        border-radius: 22px;
        padding: 38px 32px 32px 32px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
        width: 95vw;
        max-width: 370px;
        position: relative;
        text-align: center;
        border: none;
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        animation: modalPopIn 0.18s cubic-bezier(.4, 1.4, .6, 1) 1;
    }

    @keyframes modalPopIn {
        0% {
            transform: scale(0.92);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-cancelar-icon {
        margin-bottom: 12px;
    }

    .modal-cancelar-content h3 {
        margin: 0 0 8px 0;
        font-size: 1.35rem;
        color: #222;
        font-weight: 700;
    }

    .modal-cancelar-texto {
        color: #444;
        font-size: 1.08rem;
        margin-bottom: 32px;
        /* aumentado para dar mais espaço */
        margin-top: 18px;
        /* espaço extra acima do texto */
    }

    .modal-cancelar-botoes {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin-bottom: 10px;
    }

    .btn-modal-sim {
        background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 1.08rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(244, 67, 54, 0.08);
        transition: background 0.2s;
    }

    .btn-modal-sim:hover {
        background: linear-gradient(90deg, #d32f2f 0%, #f44336 100%);
    }

    .btn-modal-nao {
        background: #e0e0e0;
        color: #444;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 1.08rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-modal-nao:hover {
        background: #bdbdbd;
    }

    .modal-cancelar-status {
        margin-top: 10px;
        font-size: 0.98rem;
        color: rgb(0, 0, 0);
        min-height: 18px;
    }

    @media (max-width: 500px) {
        .modern-modal {
            padding: 22px 6vw 22px 6vw;
            max-width: 98vw;
        }

        .modal-cancelar-botoes {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
