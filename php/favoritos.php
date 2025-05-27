<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$usuarioId = $_SESSION['usuarioId'] ?? null;
$totalFavoritos = 0;
if ($usuarioId) {
    require_once __DIR__ . '/conexao.php';
    $sqlCount = "SELECT COUNT(*) as total FROM favoritos WHERE usuario_id = ?";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->bind_param('i', $usuarioId);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    if ($row = $resultCount->fetch_assoc()) {
        $totalFavoritos = (int)$row['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus favoritados</title>
    <link rel="stylesheet" href="../css/favoritos.css">
    <link rel="stylesheet" href="../css/consultar_clientes.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div>
            <?php include 'sidebar.php'; ?>
        </div>
        <!-- Main Content: títulos + cards -->
        <div class="main-content" style="display:flex;flex-direction:column;width:100%;height:100vh;overflow:hidden;">
            <!-- Títulos -->
            <div style="width:100%;display:flex;flex-direction:column;align-items:flex-start;">
                <h2 class="btn-shine" style="text-align:left;margin:0 0 4px 10px;">Meus favoritos</h2>
                <h3 id="total-favoritos" style="text-align:left;margin:0 0 24px 20px;">Total de favoritos (<?php echo $totalFavoritos; ?>)</h3>
            </div>
            <!-- Conteúdo -->
            <div class="content" style="flex:1;">
                <?php include 'cards-favoritos.php'; ?>
            </div>
        </div>
    </div>
    <script>
function ativarRemoverFavorito() {
  document.querySelectorAll('.btn-favoritar').forEach(function(btn) {
    btn.onclick = function(e) {
      e.preventDefault();
      const modeloId = btn.getAttribute('data-modelo-id');
      fetch('../php/favoritar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'modelo_id=' + encodeURIComponent(modeloId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const img = btn.querySelector('img.heart-icon');
          if (data.favorito) {
            img.src = 'img/coracoes/coracao-salvo.png';
          } else {
            img.src = 'img/coracoes/coracao-nao-salvo.png';
            const card = btn.closest('.card');
            if (card) card.remove();
            atualizarTotalFavoritos();
          }
          document.body.dispatchEvent(new Event('favoritoAtualizado'));
        } else if (data.error) {
          alert(data.error);
        }
      })
      .catch(() => alert('Erro ao favoritar.'));
    }
  });
}
function atualizarTotalFavoritos() {
  const total = document.querySelectorAll('.card').length;
  document.getElementById('total-favoritos').textContent = `Total de favoritos (${total})`;
}
window.addEventListener('DOMContentLoaded', function() {
  ativarRemoverFavorito();
  atualizarTotalFavoritos();
});
document.body.addEventListener('favoritoAtualizado', ativarRemoverFavorito);
    </script>
</body>

</html>