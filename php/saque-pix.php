<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Saque • Via Pix</title>
    <link rel="stylesheet" href="../css/saque-pix.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="saque-container">
    <h2>Saque <span class="saque-via">• Via Pix</span></h2>
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
</body>
</html>
