<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Saque - Escolha seu método</title>
    <link rel="stylesheet" href="../css/saque.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="saque-container" id="saque-container">
    <h2>Saque</h2>
    <p class="saque-sub">Escolha seu método de saque</p>
    <div class="saque-metodos" id="saque-metodos">
        <div class="saque-metodo recomendado">
            <div class="saque-metodo-label">RECOMENDADO</div>
            <button class="saque-btn saque-btn-pix">
                <span class="saque-btn-title">Pix</span>
                <img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Pix" class="saque-btn-icon">
                <span class="saque-btn-arrow"><img src="../img/jogar.png" alt="Seta"></span>
            </button>
        </div>
        <div class="saque-metodo outros">
            <div class="saque-metodo-label">OUTROS MÉTODOS</div>
            <button class="saque-btn saque-btn-paypal">
                <span class="saque-btn-title">Paypal</span>
                <img src="../img/formas-de-pagamento/paypal.png" alt="Paypal" class="saque-btn-icon">
                <span class="saque-btn-arrow"><img src="../img/jogar.png" alt="Seta"></span>
            </button>
            <button class="saque-btn saque-btn-astropay">
                <span class="saque-btn-title">AstroPay</span>
                <img src="../img/formas-de-pagamento/astropay.png" alt="AstroPay" class="saque-btn-icon">
                <span class="saque-btn-arrow"><img src="../img/jogar.png" alt="Seta"></span>
            </button>
        </div>
    </div>
</div>
<div class="saque-form-container" id="saque-form-container" style="display:none;">
    <button type="button" class="saque-btn-voltar" id="saque-btn-voltar" title="Voltar para opções de saque">&#8592;</button>
    <h2 class="saque-form-title">Saque <span class="saque-via" id="saque-via"></span></h2>
    <div class="saque-saldo">
        Saldo disponível: <span class="saque-saldo-valor">R$ 0,00</span>
        <img src="../img/copia.png" alt="Copiar" class="saque-copiar-icon">
    </div>
    <form class="saque-form">
        <div class="saque-form-group">
            <select class="saque-input saque-select" required>
                <option value="">Selecione um tipo de chave</option>
                <option value="cpf">CPF</option>
                <option value="email">E-mail</option>
                <option value="telefone">Telefone</option>
                <option value="aleatoria">Chave Aleatória</option>
            </select>
        </div>
        <div class="saque-form-group">
            <input type="text" class="saque-input" placeholder="Digite sua chave *" required>
        </div>
        <div class="saque-form-group">
            <input type="number" class="saque-input" placeholder="Valor *" required>
            <div class="saque-info">( Transferência mínima: R$ 1.000,00 | Transferência máxima: R$ 100.000,00 )</div>
        </div>
        <div class="saque-form-group">
            <input type="text" class="saque-input" placeholder="Banco *" required>
        </div>
        <div class="saque-form-group">
            <input type="text" class="saque-input" placeholder="Agência *" required>
        </div>
        <div class="saque-form-group">
            <input type="text" class="saque-input" placeholder="nº da conta *" required>
        </div>
        <button type="submit" class="saque-btn-submit">Solicitar Saque</button>
    </form>
    <div class="saque-alerta">
        <img src="../img/alerta.png" alt="Atenção" class="saque-alerta-icon">
        Atenção! Você só pode realizar até 2 saques por dia, com valores entre R$ 1.000,00 e R$ 100.000,00. O limite total de saque diário é de R$ 100.000,00. Todos os saques precisam passar por aprovação do setor financeiro.
    </div>
</div>
<script>
// Exibe o formulário e esconde a tela de métodos ao clicar em um método
const container = document.getElementById('saque-container');
const formContainer = document.getElementById('saque-form-container');
document.querySelectorAll('.saque-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        container.style.display = 'none';
        formContainer.style.display = 'block';
        // Define o nome do método na tela
        var via = btn.querySelector('.saque-btn-title').textContent.trim();
        document.getElementById('saque-via').textContent = '• Via ' + via;
    });
});
// Botão de voltar
const btnVoltar = document.getElementById('saque-btn-voltar');
btnVoltar.addEventListener('click', function() {
    formContainer.style.display = 'none';
    container.style.display = 'block';
});
</script>
</body>
</html>
