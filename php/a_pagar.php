<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$usuarioId = $_SESSION['usuarioId'] ?? null;
$totalAPagar = 0;
if ($usuarioId) {
  require_once __DIR__ . '/conexao.php';
  // NOVO: Atualiza boletos vencidos para 'expirado' antes de buscar/exibir os cards
  $sqlExpira = "UPDATE pagamento_boleto SET status = 'expirado' WHERE usuario_id = ? AND status = 'pendente' AND data_expiracao <= NOW()";
  $stmtExpira = $conn->prepare($sqlExpira);
  $stmtExpira->bind_param('i', $usuarioId);
  $stmtExpira->execute();

  // Corrigido: contar veículos a pagar nas duas tabelas
  $sqlPix = "SELECT COUNT(DISTINCT veiculo_id) as total FROM pagamentos_pix WHERE usuario_id = ? AND status = 'pendente' AND expira_em > NOW()";
  $stmtPix = $conn->prepare($sqlPix);
  $stmtPix->bind_param('i', $usuarioId);
  $stmtPix->execute();
  $resultPix = $stmtPix->get_result();
  $totalPix = ($row = $resultPix->fetch_assoc()) ? (int) $row['total'] : 0;

  $sqlBoleto = "SELECT COUNT(DISTINCT veiculo_id) as total FROM pagamento_boleto WHERE usuario_id = ? AND status = 'pendente'";
  $stmtBoleto = $conn->prepare($sqlBoleto);
  $stmtBoleto->bind_param('i', $usuarioId);
  $stmtBoleto->execute();
  $resultBoleto = $stmtBoleto->get_result();
  $totalBoleto = ($row = $resultBoleto->fetch_assoc()) ? (int) $row['total'] : 0;

  $totalAPagar = $totalPix + $totalBoleto;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Meus a pagar</title>
  <link rel="stylesheet" href="../css/favoritos.css">
  <link rel="stylesheet" href="../css/consultar_clientes.css">
  <link rel="icon" href="../img/logos/logoofcbmw.png">
  <style>
    .titulos-a-pagar {
      margin-left: 20px;
    }

    @media (min-width: 1920px) {
      .titulos-a-pagar {
        margin-left: 50px !important;
      }
    }

      /* Força o scroll vertical apenas nesta página, sem alterar o CSS global */
      .main-content {
        overflow-y: auto !important;
        overflow-x: hidden !important;
        height: 100vh;
        display: flex;
        flex-direction: column;
        width: 100%;
      }

      .content {
        min-height: 400px;
        height: auto !important;
        max-height: none !important;
        overflow-y: visible !important;
        padding-bottom: 32px !important;
        box-sizing: border-box;
      }

    /* Estilos personalizados para a barra de rolagem */
    .main-content::-webkit-scrollbar {
      width: 17px;
    }

    /* Fundo da barra de rolagem (track) */
    .main-content::-webkit-scrollbar-track {
      background: transparent;
    }

    /* "Thumb" — a parte que você arrasta */
    .main-content::-webkit-scrollbar-thumb {
      background-color: #2b2b2b; /* Cor do thumb */
      border-radius: 8px;
    }

    /* Hover no thumb (opcional) */
    .main-content::-webkit-scrollbar-thumb:hover {
      background-color: #999999;
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Sidebar -->
    <div>
      <?php include 'sidebar.php'; ?>
    </div>
    <!-- Main Content: títulos + cards -->
    <div class="main-content" style="display:flex;flex-direction:column;width:100%;height:100vh;">
      <!-- Títulos -->
      <div class="titulos-a-pagar"
        style="width:100%;display:flex;flex-direction:column;align-items:flex-start;max-width:100%;margin-left:20px;margin-top:20px;">
        <h2 class="btn-shine" style="text-align:left;margin:0 0 4px 10px;">A pagar</h2>
        <h3 id="total-a-pagar" style="text-align:left;margin:0 0 24px 20px;">Total a pagar (<?php echo $totalAPagar; ?>)
        </h3>
      </div>
      <!-- Conteúdo -->
      <div class="content" style="flex:1;">
        <?php include 'cards-a-pagar.php'; ?>
      </div>
    </div>
  </div>
  <script>
    function ativarRemoverAPagar() {
      document.querySelectorAll('.btn-a-pagar').forEach(function (btn) {
        btn.onclick = function (e) {
          e.preventDefault();
          const modeloId = btn.getAttribute('data-modelo-id');
          fetch('../php/a_pagar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'modelo_id=' + encodeURIComponent(modeloId)
          })
            .then(res => res.json())
            .then data => {
              if (data.success) {
                const img = btn.querySelector('img.a-pagar-icon');
                if (data.a_pagar) {
                  img.src = 'img/coracoes/coracao-salvo.png';
                } else {
                  img.src = 'img/coracoes/coracao-nao-salvo.png';
                  const card = btn.closest('.card');
                  if (card) card.remove();
                  atualizarTotalAPagar();
                }
                document.body.dispatchEvent(new Event('aPagarAtualizado'));
              } else if (data.error) {
                alert(data.error);
              }
            })
            .catch(() => alert('Erro ao atualizar a pagar.'));
        }
      });
    }
    function atualizarTotalAPagar() {
      const total = document.querySelectorAll('.card').length;
      document.getElementById('total-a-pagar').textContent = `Total a pagar (${total})`;
    }
    window.addEventListener('DOMContentLoaded', function () {
      ativarRemoverAPagar();
      atualizarTotalAPagar();
    });
    document.body.addEventListener('aPagarAtualizado', ativarRemoverAPagar);
  </script>
</body>

</html>