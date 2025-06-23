<?php
session_start();
require_once __DIR__ . '/conexao.php';

// Passo 1: Verifica se o usuário está logado e é funcionário
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true || !in_array($_SESSION['usuarioCargo'], ['Admin', 'Gerente', 'Funcionario'])) {
    header('Location: ../login.html');
    exit;
}

// Passo 2: Fluxo de cadastro/seleção de cliente
$etapa = $_GET['etapa'] ?? 'cliente';
$cliente_id = $_GET['cliente_id'] ?? null;
$modelo_id = $_GET['modelo_id'] ?? null;
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($etapa === 'cliente') {
        $cpf = trim($_POST['cpf'] ?? '');
        if ($cpf) {
            $stmt = $conn->prepare('SELECT id, nome_completo FROM clientes WHERE cpf = ?');
            $stmt->bind_param('s', $cpf);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($cli = $res->fetch_assoc()) {
                header('Location: venda_manual.php?etapa=modelo&cliente_id=' . $cli['id']);
                exit;
            } else {
                // Redireciona para cadastro de cliente já com CPF preenchido e origem para retorno
                header('Location: cadastro_admin.php?redirect=venda_manual' . urlencode($cpf));
                exit;
            }
        }
    } elseif ($etapa === 'modelo') {
        $modelo_id = $_POST['modelo_id'] ?? '';
        if ($modelo_id) {
            header('Location: venda_manual.php?etapa=pagamento&cliente_id=' . $cliente_id . '&modelo_id=' . $modelo_id);
            exit;
        }
    } elseif ($etapa === 'pagamento') {
        // Recebe dados do formulário
        $forma_pagamento = $_POST['forma_pagamento'] ?? '';
        $desconto = floatval($_POST['desconto'] ?? 0);
        $total = floatval($_POST['total'] ?? 0);
        $cliente_id = intval($_POST['cliente_id']);
        $modelo_id = intval($_POST['modelo_id']);
        $veiculo_id = intval($_POST['veiculo_id']);
        $servicos_adicionais = $_POST['servicos_adicionais'] ?? null;
        $cor_veiculo = $_POST['cor_veiculo'] ?? ''; // Recebe a cor do veículo
        $usuario_id = $_SESSION['usuarioId'] ?? null;
        // Atualiza estoque e registra venda
        $conn->begin_transaction();
        $ok = true;
        $sqlEstoque = "UPDATE veiculos SET status = 'vendido_M' WHERE id = ? AND status = 'disponivel' LIMIT 1";
        $stmt = $conn->prepare($sqlEstoque);
        $stmt->bind_param('i', $veiculo_id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $ok = false;
            $mensagem = 'Veículo não disponível.';
        }
        if ($ok) {
            $sqlVenda = "INSERT INTO vendas_fisicas (cliente_id, veiculo_id, cor_veiculo, forma_pagamento, desconto, total, servicos_adicionais, usuario_id, data_venda) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt2 = $conn->prepare($sqlVenda);
            $stmt2->bind_param('iissddsi', $cliente_id, $veiculo_id, $cor_veiculo, $forma_pagamento, $desconto, $total, $servicos_adicionais, $usuario_id);
            $ok = $stmt2->execute();
            if (!$ok) $mensagem = 'Erro ao registrar venda.';
        }
        if ($ok) {
            $venda_id = $stmt2->insert_id;
            // Calcula comissão do funcionário (0,5% do total)
            $comissao = round($total * 0.005, 2);
            // Cria tabela de comissão se não existir
            $conn->query("CREATE TABLE IF NOT EXISTS comissoes_vendas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                venda_id INT NOT NULL,
                funcionario_id INT NOT NULL,
                valor_comissao DECIMAL(10,2) NOT NULL,
                data_comissao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (venda_id) REFERENCES vendas_fisicas(id),
                FOREIGN KEY (funcionario_id) REFERENCES clientes(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            // Insere comissão
            $stmtCom = $conn->prepare('INSERT INTO comissoes_vendas (venda_id, funcionario_id, valor_comissao) VALUES (?, ?, ?)');
            $stmtCom->bind_param('iid', $venda_id, $usuario_id, $comissao);
            $stmtCom->execute();
            $stmtCom->close();
            $conn->commit();
            // Redireciona para cupom fiscal
            header('Location: cupom_fiscal.php?venda_id=' . $venda_id);
            exit;
        } else {
            $conn->rollback();
        }
    }
}

// HTML das etapas
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Venda Manual</title>
    <link rel="stylesheet" href="../css/venda_manual.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>
<body>
<div class="container">
    <h2>Venda Manual (Física)</h2>
    <?php if ($etapa === 'cliente'): ?>
        <form method="post">
            <label>O cliente já está cadastrado? Informe o CPF:</label>
            <input type="text" name="cpf" required maxlength="14" placeholder="CPF do cliente">
            <span style="margin-left:0px;font-size:0.em;">
                O cliente não está cadastrado? <a href="cadastro_admin.php?redirect=venda_manual" style="color:#1a4ed8;text-decoration:underline;">Registre-o</a>
            </span>
            <button type="submit">Buscar Cliente</button>
        </form>
    <?php elseif ($etapa === 'modelo' && $cliente_id): ?>
        <?php
        // Busca nome e CPF do cliente
        $stmt = $conn->prepare('SELECT nome_completo, cpf FROM clientes WHERE id = ?');
        $stmt->bind_param('i', $cliente_id);
        $stmt->execute();
        $stmt->bind_result($nome_cliente, $cpf_cliente);
        $stmt->fetch();
        $stmt->close();
        ?>
        <form method="post">
            <input type="hidden" name="cliente_id" value="<?= htmlspecialchars($cliente_id) ?>">
            <label>Cliente:</label>
            <input type="text" value="<?= htmlspecialchars($nome_cliente) ?>" disabled>
            <label>CPF:</label>
            <input type="text" value="<?= htmlspecialchars($cpf_cliente) ?>" disabled>
            <label>Selecione o modelo do veículo:</label>
            <select name="modelo_id" required>
                <option value="">Selecione</option>
                <?php
                $res = $conn->query("SELECT m.id, m.modelo, m.ano FROM modelos m WHERE EXISTS (SELECT 1 FROM veiculos v WHERE v.modelo_id = m.id AND v.status = 'disponivel') ORDER BY m.modelo");
                while ($m = $res->fetch_assoc()): ?>
                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['modelo']) ?> - <?= $m['ano'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Avançar</button>
        </form>
    <?php elseif ($etapa === 'pagamento' && $cliente_id && $modelo_id): ?>
        <?php
        // Busca preço do modelo
        $stmt = $conn->prepare('SELECT preco FROM modelos WHERE id = ?');
        $stmt->bind_param('i', $modelo_id);
        $stmt->execute();
        $stmt->bind_result($preco_modelo);
        $stmt->fetch();
        $stmt->close();
        ?>
        <form method="post" id="form-pagamento">
            <input type="hidden" name="cliente_id" value="<?= htmlspecialchars($cliente_id) ?>">
            <input type="hidden" name="modelo_id" value="<?= htmlspecialchars($modelo_id) ?>">
            <label>Selecione o veículo disponível:</label>
            <select name="veiculo_id" required>
                <option value="">Selecione</option>
                <?php
                $res = $conn->query("SELECT id, numero_chassi FROM veiculos WHERE modelo_id = $modelo_id AND status = 'disponivel'");
                while ($v = $res->fetch_assoc()): ?>
                    <option value="<?= $v['id'] ?>">Chassi: <?= htmlspecialchars($v['numero_chassi']) ?></option>
                <?php endwhile; ?>
            </select>
            <label>Cor do veículo:</label>
            <select name="cor_veiculo" required>
                <option value="">Selecione</option>
                <?php
                $stmtCores = $conn->prepare("SELECT cor FROM modelos WHERE id = ?");
                $stmtCores->bind_param('i', $modelo_id);
                $stmtCores->execute();
                $stmtCores->bind_result($cor_modelo);
                if ($stmtCores->fetch() && $cor_modelo) {
                    $cores = array_map('trim', explode(',', $cor_modelo));
                    foreach ($cores as $cor) {
                        echo '<option value="' . htmlspecialchars($cor) . '">' . htmlspecialchars($cor) . '</option>';
                    }
                }
                $stmtCores->close();
                ?>
            </select>
            <label>Forma de Pagamento:</label>
            <select name="forma_pagamento" required>
                <option value="">Selecione</option>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Cartão">Cartão</option>
                <option value="Transferência">Transferência</option>
                <option value="Outro">Outro</option>
            </select>
            <label>Desconto (%)</label>
            <input type="number" name="desconto" id="desconto" step="0.01" min="0" max="100" value="0">
            <label>Serviços Adicionais:</label>
            <input type="text" name="servicos_adicionais" maxlength="255" placeholder="Ex: Emplacamento, seguro, etc.">
            <label>Preço do veículo (R$):</label>
            <input type="text" id="preco_modelo" value="<?= number_format($preco_modelo, 2, ',', '.') ?>" readonly>
            <label>Total a Pagar (R$):</label>
            <input type="text" name="total" id="total" readonly style="background:#e9eefa;font-weight:600;">
            <button type="submit">Confirmar Venda</button>
        </form>
        <script>
        // Atualiza o total automaticamente ao digitar desconto
        // e formata como moeda
        function formatarMoeda(valor) {
            return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const preco = <?= floatval($preco_modelo) ?>;
            const descontoInput = document.getElementById('desconto');
            const totalInput = document.getElementById('total');
            function atualizarTotal() {
                let desconto = parseFloat(descontoInput.value.replace(',', '.')) || 0;
                if (desconto < 0) desconto = 0;
                if (desconto > 100) desconto = 100;
                const valorFinal = preco * (1 - desconto/100);
                totalInput.value = formatarMoeda(valorFinal);
            }
            descontoInput.addEventListener('input', atualizarTotal);
            atualizarTotal();
            // Ao submeter, envia o valor numérico correto para o backend
            document.getElementById('form-pagamento').addEventListener('submit', function(e) {
                // Cria um input hidden com o valor numérico
                let valorNumerico = preco * (1 - (parseFloat(descontoInput.value.replace(',', '.')) || 0)/100);
                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'total';
                hidden.value = valorNumerico.toFixed(2);
                this.appendChild(hidden);
            });
        });
        </script>
    <?php endif; ?>
    <?php if ($mensagem): ?><p style="color:red;"><?= htmlspecialchars($mensagem) ?></p><?php endif; ?>
</div>
</body>
</html>