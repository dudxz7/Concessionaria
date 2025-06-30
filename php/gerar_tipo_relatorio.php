<?php
session_start();
if (!isset($_SESSION['usuarioCargo']) || !in_array($_SESSION['usuarioCargo'], ['Admin', 'Gerente'])) {
    echo "<h2>Acesso Negado</h2>";
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar relatórios</title>
    <link rel="stylesheet" href="../css/gerar_relatorio.css">
    <link rel="icon" href="../img/grafico2.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap">
</head>
<body>
    <div class="modal-overlay">
        <div class="modal-container">
            <button class="close-btn" title="Close">&times;</button>
            <h2>Gerar relatórios</h2>
            <form class="event-form" method="post" action="processar_relatorio.php">
                <label for="report-name">Nome do relatório</label>
                <input type="text" id="report-name" name="report-name" placeholder="Ex: Relatório de Pagamentos" value="Relatório de Pagamentos">

                <label for="report-type">Tipo de relatório</label>
                <select id="report-type" name="report-type">
                    <option value="vendas_intervalo" selected>Total de vendas em um intervalo de datas</option>
                    <option value="vendas_funcionario">Vendas por funcionários</option>
                    <option value="vendas_modelo">Vendas por modelo de carro</option>
                    <option value="estoque">Situação do estoque</option>
                    <option value="promocoes">Relatório das promoções</option>
                    <option value="clientes">Relatório total dos clientes</option>
                </select>

                <div id="date-range-fields" style="display:none;">
                    <label for="data-inicio">Data início</label>
                    <input type="date" id="data-inicio" name="data-inicio" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" disabled>
                    <label for="data-fim">Data fim</label>
                    <input type="date" id="data-fim" name="data-fim" autocomplete="off" readonly onfocus="this.removeAttribute('readonly');" disabled>
                </div>

                <div class="file-upload-section">
                    <div class="file-box">
                        <img src="../img/grafico2.png" alt="PDF" class="pdf-icon">
                        <span>Relatório em PDF</span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-btn">Cancelar</button>
                    <button type="submit" class="update-btn">Visualizar relatório</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<script>
const reportType = document.getElementById('report-type');
const dateFields = document.getElementById('date-range-fields');
const dataInicio = document.getElementById('data-inicio');
const dataFim = document.getElementById('data-fim');
const closeBtn = document.querySelector('.close-btn');
const cancelBtn = document.querySelector('.cancel-btn');
function toggleDateFields(force) {
    if (reportType.value === 'vendas_intervalo') {
        dateFields.style.display = 'block';
        dataInicio.disabled = false;
        dataFim.disabled = false;
        dataInicio.readOnly = false;
        dataFim.readOnly = false;
        dataInicio.setAttribute('required', 'required');
        dataFim.setAttribute('required', 'required');
    } else {
        dateFields.style.display = 'none';
        dataInicio.value = '';
        dataFim.value = '';
        dataInicio.disabled = true;
        dataFim.disabled = true;
        dataInicio.readOnly = true;
        dataFim.readOnly = true;
        dataInicio.removeAttribute('required');
        dataFim.removeAttribute('required');
    }
}
reportType.addEventListener('change', toggleDateFields);
window.addEventListener('pageshow', function() { toggleDateFields(true); });
window.addEventListener('DOMContentLoaded', function() { toggleDateFields(true); });

closeBtn.addEventListener('click', function() {
    window.location.href = '../perfil.php';
});
cancelBtn.addEventListener('click', function() {
    window.location.href = '../perfil.php';
});
</script>
<style>
.close-btn {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    border-radius: 50%;
    transition: background 0.2s;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 20px;
    right: 20px;
}
.close-btn:hover {
    background: #e0e0e0;
    color: #003366;
}
</style>
