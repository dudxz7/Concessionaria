<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioId = $_SESSION['usuarioId'] ?? null;
$totalHistorico = 0;
if ($usuarioId) {
    require_once __DIR__ . '/conexao.php';
    // Conta quantos pagamentos já foram feitos (status pago ou expirado)
    $sqlPix = "SELECT COUNT(DISTINCT veiculo_id) as total FROM pagamentos_pix WHERE usuario_id = ? AND (status = 'pago' OR status = 'expirado')";
    $stmtPix = $conn->prepare($sqlPix);
    $stmtPix->bind_param('i', $usuarioId);
    $stmtPix->execute();
    $resultPix = $stmtPix->get_result();
    $totalPix = ($row = $resultPix->fetch_assoc()) ? (int) $row['total'] : 0;

    $sqlBoleto = "SELECT COUNT(DISTINCT veiculo_id) as total FROM pagamento_boleto WHERE usuario_id = ? AND (status = 'pago' OR status = 'expirado')";
    $stmtBoleto = $conn->prepare($sqlBoleto);
    $stmtBoleto->bind_param('i', $usuarioId);
    $stmtBoleto->execute();
    $resultBoleto = $stmtBoleto->get_result();
    $totalBoleto = ($row = $resultBoleto->fetch_assoc()) ? (int) $row['total'] : 0;

    $totalHistorico = $totalPix + $totalBoleto;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Pagamentos</title>
    <link rel="stylesheet" href="../css/favoritos.css">
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        .titulos-historico {
            margin-left: 20px;
        }

        @media (min-width: 1920px) {
            .titulos-historico {
                margin-left: 50px !important;
            }
        }
        .content {
        overflow-x: hidden !important;
        overflow-y: visible !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <div>
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="main-content" style="display:flex;flex-direction:column;width:100%;height:100vh;">
            <div class="titulos-historico"
                style="width:100%;display:flex;flex-direction:column;align-items:flex-start;max-width:100%;margin-left:20px;margin-top:20px;">
                <h2 class="btn-shine" style="text-align:left;margin:0 0 4px 10px;">Histórico de Pagamentos</h2>
                <h3 id="total-historico" style="text-align:left;margin:0 0 24px 20px;">Total no histórico
                    (<?php echo $totalHistorico; ?>)
                </h3>
            </div>
            <div class="content" style="flex:1;">
                <?php include 'cards-historico.php'; ?>
            </div>
        </div>
    </div>
    <script>
        function atualizarTotalHistorico() {
            const total = document.querySelectorAll('.card').length;
            document.getElementById('total-historico').textContent = `Total no histórico (${total})`;
        }
        window.addEventListener('DOMContentLoaded', function () {
            atualizarTotalHistorico();
        });
    </script>
</body>

</html>