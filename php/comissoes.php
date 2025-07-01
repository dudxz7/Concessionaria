<?php
session_start();
if (!isset($_SESSION['usuarioCargo']) || !in_array($_SESSION['usuarioCargo'], ['Admin', 'Gerente'])) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}
require_once 'conexao.php';
// Busca funcionários para o select
$funcionarios = [];
$usuarioCargo = $_SESSION['usuarioCargo'] ?? '';
if ($usuarioCargo === 'Admin') {
    // Admin vê funcionários e gerentes (mas não admins)
    $sql = "SELECT id, nome_completo, cargo FROM clientes WHERE cargo IN ('Funcionario','Gerente') ORDER BY nome_completo";
} elseif ($usuarioCargo === 'Gerente') {
    // Gerente vê apenas funcionários
    $sql = "SELECT id, nome_completo, cargo FROM clientes WHERE cargo = 'Funcionario' ORDER BY nome_completo";
} else {
    // Outros não podem acessar
    $sql = "SELECT id, nome_completo, cargo FROM clientes WHERE 1=0";
}
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $funcionarios[] = $row;
    }
}
// Processa o relatório se enviado
$relatorio = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_func = intval($_POST['funcionario']);
    $data_ini = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    // Busca comissões e valor da venda APENAS de vendas_fisicas que tenham pagamento aprovado
    $sql = "SELECT c.data_comissao, v.total AS valor_venda, c.valor_comissao
    FROM comissoes_vendas c
    INNER JOIN vendas_fisicas v ON c.venda_id = v.id
    LEFT JOIN pagamentos_pix pix ON v.id = pix.veiculo_id AND pix.status = 'aprovado'
    LEFT JOIN pagamentos_cartao cartao ON v.id = cartao.veiculo_id AND cartao.status = 'aprovado'
    LEFT JOIN pagamento_boleto boleto ON v.id = boleto.veiculo_id AND boleto.status = 'aprovado'
    WHERE c.funcionario_id = $id_func
      AND c.data_comissao BETWEEN '$data_ini' AND '$data_fim'
      AND (
        pix.id IS NOT NULL OR cartao.id IS NOT NULL OR boleto.id IS NOT NULL
      )
    ORDER BY c.data_comissao DESC";
    $res = $conn->query($sql);
    $relatorio = [];
    $total_vendas = 0;
    $total_comissao = 0;
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $relatorio[] = $row;
            $total_vendas += $row['valor_venda'];
            $total_comissao += $row['valor_comissao'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Comissões</title>
    <link rel="stylesheet" href="../css/gerar_relatorio.css">
    <link rel="icon" href="../img/grafico2.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap">
    <style>
        .event-form select {
            width: 100% !important;
        }
        .event-form input[type="text"],
        .event-form input[type="date"] {
            width: 93%;
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-container">
            <button class="close-btn" title="Fechar">&times;</button>
            <h2>Relatório de Comissões</h2>
            <form class="event-form" method="post" action="gerar_comissoes_pdf.php" target="_blank">
                <label for="funcionario">Funcionário</label>
                <select name="funcionario" id="funcionario" required>
                    <option value="">Selecione...</option>
                    <?php foreach($funcionarios as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= (isset($_POST['funcionario']) && $_POST['funcionario']==$f['id'])?'selected':'' ?>><?= htmlspecialchars($f['nome_completo']) ?><?= ($f['cargo'] !== 'Funcionario') ? ' (' . $f['cargo'] . ')' : '' ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="data_inicio">Data início</label>
                <input type="date" name="data_inicio" id="data_inicio" required value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>">
                <label for="data_fim">Data fim</label>
                <input type="date" name="data_fim" id="data_fim" required value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>">
                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancelar</button>
                    <button type="submit" class="update-btn">Visualizar relatório</button>
                </div>
            </form>
            <?php if ($relatorio !== null): ?>
            <div class="comissoes-table">
                <h3>Resultado</h3>
                <table>
                    <thead>
                        <tr><th>Data</th><th>Venda (R$)</th><th>Comissão Unitária (R$)</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($relatorio) > 0): ?>
                            <?php foreach($relatorio as $linha): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($linha['data_comissao'])) ?></td>
                                <td><?= number_format($linha['valor_venda'],2,',','.') ?></td>
                                <td><?= number_format($linha['valor_comissao'],2,',','.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" style="text-align:center;color:#888;">Nenhum resultado encontrado para o período e funcionário selecionados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr><td>Total</td><td><b><?= number_format($total_vendas,2,',','.') ?></b></td><td><b><?= number_format($total_comissao,2,',','.') ?></b></td></tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
    const closeBtn = document.querySelector('.close-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    closeBtn.addEventListener('click', function() {
        window.location.href = '../perfil.php';
    });
    cancelBtn.addEventListener('click', function() {
        window.location.href = '../perfil.php';
    });
    </script>
</body>
</html>
