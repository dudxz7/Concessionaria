<?php
session_start();
require_once 'conexao.php';

// Função para gerar uma chave única para cada tentativa de pagamento
function gerarChavePagamento($id_veiculo, $cor, $usuarioId) {
    return 'pagamento_' . $usuarioId . '_' . $id_veiculo . '_' . md5(strtolower($cor));
}

$modelo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cor_param = (isset($_GET['cor']) && trim($_GET['cor']) !== '') ? trim($_GET['cor']) : null;
$cor_principal = $cor_param;
$usuarioId = isset($_SESSION['usuarioId']) ? $_SESSION['usuarioId'] : null;
$redir = isset($_GET['redir']) ? intval($_GET['redir']) : 0;

// Garantir que o usuarioId está presente na session
if (!isset($_SESSION['usuarioId']) || empty($_SESSION['usuarioId'])) {
    // Redireciona para login ou página de erro
    header('Location: ../login.html');
    exit;
}

// --- REDIRECIONAMENTO AUTOMÁTICO PARA BOLETO OU PIX PENDENTE (INDEPENDENTE DO TEMPO) ---
if (
    isset($usuarioId, $modelo_id, $cor_param) &&
    $modelo_id > 0 &&
    trim($cor_param) !== ''
) {
    // Verifica boleto pendente
    $sqlBoleto = "SELECT data_expiracao FROM pagamento_boleto WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' ORDER BY data_criacao DESC LIMIT 1";
    $stmtBoleto = $conn->prepare($sqlBoleto);
    $stmtBoleto->bind_param("iis", $usuarioId, $modelo_id, $cor_param);
    $stmtBoleto->execute();
    $stmtBoleto->bind_result($data_expiracao_boleto);
    if ($stmtBoleto->fetch()) {
        $chave_boleto = gerarChavePagamento($modelo_id, $cor_param, $usuarioId);
        $_SESSION[$chave_boleto] = [ 'expira_em' => strtotime($data_expiracao_boleto) ];
        $_SESSION['pagamento_autorizado_boleto'] = true;
        $_SESSION['pagamento_id'] = $modelo_id;
        $_SESSION['pagamento_cor'] = $cor_param;
        $stmtBoleto->close();
        header('Location: realizar_pagamento_boleto.php?id=' . $modelo_id . '&cor=' . urlencode($cor_param));
        exit;
    }
    $stmtBoleto->close();
    // Verifica pix pendente
    $sqlPix = "SELECT expira_em FROM pagamentos_pix WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' LIMIT 1";
    $stmtPix = $conn->prepare($sqlPix);
    $stmtPix->bind_param("iis", $usuarioId, $modelo_id, $cor_param);
    $stmtPix->execute();
    $stmtPix->bind_result($expira_em_pix);
    if ($stmtPix->fetch()) {
        $chave = gerarChavePagamento($modelo_id, $cor_param, $usuarioId);
        date_default_timezone_set('America/Sao_Paulo');
        $expira_em_ts = strtotime($expira_em_pix);
        $agora = time();
        $_SESSION[$chave] = [ 'expira_em' => $expira_em_ts ];
        $_SESSION['pagamento_autorizado'] = true;
        $_SESSION['pagamento_id'] = $modelo_id;
        $_SESSION['pagamento_cor'] = $cor_param;
        if ($expira_em_ts > $agora && $redir != 1) {
            $stmtPix->close();
            header('Location: realizar_pagamento_pix.php?id=' . $modelo_id . '&cor=' . urlencode($cor_param) . '&redir=1');
            exit;
        }
    }
    $stmtPix->close();
}

// Só faz redirect automático se redir=1, cor válida e cor EXISTE no banco para o modelo
$cor_existe = false;
if ($modelo_id > 0 && $cor_param && trim($cor_param) !== '' && $usuarioId && $redir === 1) {
    // Verifica se a cor existe para o modelo
    $sqlCor = "SELECT 1 FROM modelos WHERE id = ? AND FIND_IN_SET(?, cor) > 0";
    $stmtCor = $conn->prepare($sqlCor);
    $stmtCor->bind_param("is", $modelo_id, $cor_param);
    $stmtCor->execute();
    $stmtCor->store_result();
    if ($stmtCor->num_rows > 0) {
        $cor_existe = true;
    }
    $stmtCor->close();

    if ($cor_existe) {
        $sqlPix = "SELECT expira_em FROM pagamentos_pix WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' LIMIT 1";
        $stmtPix = $conn->prepare($sqlPix);
        $stmtPix->bind_param("iis", $usuarioId, $modelo_id, $cor_param);
        $stmtPix->execute();
        $stmtPix->bind_result($expira_em_pix);
        $temPixValido = false;
        if ($stmtPix->fetch()) {
            $agora = time();
            if (strtotime($expira_em_pix) > $agora) {
                $temPixValido = true;
            }
        }
        $stmtPix->close();
        // NÃO REDIRECIONA MAIS AQUI! Apenas exibe a tela normalmente se não houver Pix válido
        if ($temPixValido) {
            // Recria sessão de autorização para o Pix pendente
            $chave = gerarChavePagamento($modelo_id, $cor_param, $usuarioId);
            $_SESSION[$chave] = [ 'expira_em' => strtotime($expira_em_pix) ];
            $_SESSION['pagamento_autorizado'] = true;
            $_SESSION['pagamento_id'] = $modelo_id;
            $_SESSION['pagamento_cor'] = $cor_param;
            header('Location: realizar_pagamento_pix.php?id=' . $modelo_id . '&cor=' . urlencode($cor_param));
            exit;
        }
    }
}

$sql = "
SELECT 
    mo.modelo, 
    mo.preco, 
    dt.cor_principal, 
    pr.desconto, 
    pr.preco_com_desconto, 
    pr.status
FROM 
    modelos mo
JOIN 
    detalhes_modelos dt ON mo.id = dt.modelo_id
LEFT JOIN 
    promocoes pr ON pr.modelo_id = mo.id AND pr.ativo = 1 AND pr.status = 'Ativa'
WHERE 
    mo.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $modelo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Modelo não encontrado.");
}

$dados = $result->fetch_assoc();

$preco = $dados['preco'];
$cor_principal = $dados['cor_principal'];

$tem_promocao = !is_null($dados['desconto']) && $dados['status'] === 'Ativa';

if ($tem_promocao) {
    $desconto = floatval($dados['desconto']);
    $total = $dados['preco_com_desconto'];
} else {
    $desconto = 0;
    $total = $preco;
}

$cor_param = isset($_GET['cor']) ? trim($_GET['cor']) : null;
if ($cor_param) {
    $cor_principal = $cor_param;
}

// Criar slug do modelo (exemplo simples)
$modelo_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $dados['modelo']));
$cor_principal_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $cor_principal));

// Buscar imagem principal da cor escolhida, ordem = 1
$sql_img = "
    SELECT imagem 
    FROM imagens_secundarias 
    WHERE modelo_id = ? AND cor = ? AND ordem = 1
    LIMIT 1
";

$stmt_img = $conn->prepare($sql_img);
$stmt_img->bind_param("is", $modelo_id, $cor_principal);
$stmt_img->execute();
$result_img = $stmt_img->get_result();

if ($result_img->num_rows > 0) {
    $row_img = $result_img->fetch_assoc();
    // Monta o caminho da imagem com slug do modelo e cor
    $imagem = "../img/modelos/cores/{$modelo_slug}/{$cor_principal_slug}/{$row_img['imagem']}";
} else {
    $imagem = "../img/modelos/padrao.webp";
}

// Adiciona expiração do boleto para o JS, se houver
$expiraEmJs = null;
$temBoletoPendente = false;
if (isset($usuarioId, $modelo_id, $cor_param) && $modelo_id > 0 && trim($cor_param) !== '') {
    $sqlBoleto = "SELECT data_expiracao FROM pagamento_boleto WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' ORDER BY data_criacao DESC LIMIT 1";
    $stmtBoleto = $conn->prepare($sqlBoleto);
    $stmtBoleto->bind_param("iis", $usuarioId, $modelo_id, $cor_param);
    $stmtBoleto->execute();
    $stmtBoleto->bind_result($data_expiracao_boleto);
    if ($stmtBoleto->fetch()) {
        $expiraEmJs = strtotime($data_expiracao_boleto);
        $temBoletoPendente = true;
    }
    $stmtBoleto->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="../img/logos/logoofcbmw.png" />
    <link rel="stylesheet" href="../css/payment.css" />
    <title>Pagamento</title>
</head>
<body data-total="<?= htmlspecialchars($total) ?>" data-id="<?= $modelo_id ?>" data-cor="<?= htmlspecialchars($cor_principal) ?>">
<div class="container">
    <!-- Parte superior: imagem e dados -->
    <div class="superior">
        <div class="topo">
            <button class="voltar" onclick="history.back(); return false;">
                <img src="../img/seta-branca-left.png" alt="Voltar" />
                Voltar
            </button>
        </div>

        <div class="carro">
            <img src="<?= htmlspecialchars($imagem) ?>" alt="Carro" class="car-img" />
        </div>

        <div class="dados">
            <p><span>Preço</span><span>R$ <?= number_format($preco, 2, ',', '.') ?></span></p>
            <p><span>Desconto aplicado</span><span><?= $desconto > 0 ? "-" . $desconto . "%" : "0%" ?></span></p>
            <p><span>Total</span><span>R$ <?= number_format($total, 2, ',', '.') ?></span></p>
            <p><span>Cor</span><span><?= htmlspecialchars($cor_principal) ?></span></p>
        </div>


        <!-- Parte inferior: formas de pagamento e formulário -->
        <div class="inferior">
            <div class="forma-pagamento">
                <h4>Escolha uma das formas de pagamento abaixo</h4>
                <div class="formas">
                    <input type="radio" id="pix" name="forma" checked />
                    <label for="pix">
                        <img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Pix" />
                        Pix
                    </label>

                    <input type="radio" id="boleto" name="forma" />
                    <label for="boleto">
                        <img src="../img/formas-de-pagamento/boletov2.png" alt="Boleto" />
                        Boleto
                    </label>

                    <input type="radio" id="cartao" name="forma" />
                    <label for="cartao">
                        <img src="../img/formas-de-pagamento/creditcard_mb.png" alt="Cartão" />
                        Cartão
                    </label>
                </div>
            </div>

            <form>
                <!-- Campos de cartão, inicialmente ocultos, mas no topo do formulário -->
                <div id="campos-cartao" style="display:none;">
                    <div class="campo-form campo-cartao-numero" style="position: relative;">
                        <label for="numero_cartao">Número do cartão</label>
                        <img id="bandeira-cartao" src="../img/formas-de-pagamento/creditcard_mb.png"
                            alt="Bandeira do cartão"
                            style="position: absolute; left: 8px; top: 35px; width: 48px; height: 32px; object-fit: contain; transition: 0.2s;" />
                        <input type="text" id="numero_cartao" name="numero_cartao" placeholder="Número do cartão"
                            maxlength="19" style="padding-left: 60px;" />
                    </div>
                    <div class="linha-dupla">
                        <div>
                            <label for="validade">Validade</label>
                            <input type="text" id="validade" name="validade" placeholder="MM/AA" maxlength="5" />
                        </div>
                        <div>
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" placeholder="CVV" maxlength="4" />
                        </div>
                    </div>
                </div>

                <div class="campo-form">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome completo" required />
                </div>

                <div class="campo-form">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email" required pattern="[^@\s]+@[^@\s]+\.[^@\s]+" />
                </div>

                <div class="linha-dupla">
                    <div>
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="CPF" required />
                    </div>

                    <div>
                        <label for="data_nasc">Data de nascimento</label>
                        <input type="text" id="data_nasc" name="data_nasc" placeholder="DD/MM/AAAA" required />
                    </div>
                </div>

                <div class="campo-form">
                    <label for="telefone">Número de telefone</label>
                    <input type="text" id="telefone" name="telefone" placeholder="Número de telefone" required />
                </div>

                <div class="campo-form" id="campo-parcelamento" style="margin-top: 10px; display: none;">
                    <label for="parcelamento">Parcelamento</label>
                    <select id="parcelamento" name="parcelamento">
                        <option value="1">1 x R$ 209,99 = R$ 209,99</option>
                        <option value="2">2 x R$ 105,00 = R$ 210,00</option>
                        <option value="3">3 x R$ 70,00 = R$ 210,00</option>
                        <option value="4">4 x R$ 54,33 = R$ 217,32 (Com juros)</option>
                        <option value="5">5 x R$ 44,00 = R$ 220,00 (Com juros)</option>
                        <option value="6">6 x R$ 37,00 = R$ 222,00 (Com juros)</option>
                        <option value="7">7 x R$ 32,00 = R$ 224,00 (Com juros)</option>
                        <option value="8">8 x R$ 28,00 = R$ 224,00 (Com juros)</option>
                        <option value="9">9 x R$ 25,00 = R$ 225,00 (Com juros)</option>
                        <option value="10">10 x R$ 22,50 = R$ 225,00 (Com juros)</option>
                        <option value="11">11 x R$ 20,50 = R$ 225,50 (Com juros)</option>
                        <option value="12">12 x R$ 19,00 = R$ 228,00 (Com juros)</option>
                    </select>
                </div>

                <p class="termos">
                    Ao clicar em “Prosseguir para Pagamento”, declaro que li e concordo
                    com os <a href="#">termos e condições</a> da Loja BMW Motors.
                </p>
                <button type="button" class="botao" id="botao-pagamento">Prosseguir para pagamento</button>
            </form>

        </div>
    </div>
    <?php if ($temBoletoPendente && $expiraEmJs && $expiraEmJs > time()): ?>
    <script>
    (function() {
        var expiraEm = <?php echo intval($expiraEmJs); ?>;
        var agora = Math.floor(Date.now() / 1000);
        var tempoRestante = expiraEm - agora;
        function redirecionarAoExpirar() {
            if (tempoRestante <= 0) {
                window.location.reload();
                return;
            }
            tempoRestante--;
            setTimeout(redirecionarAoExpirar, 1000);
        }
        redirecionarAoExpirar();
    })();
    </script>
    <?php endif; ?>
    <script src="../js/payment-main.js" type="module"></script>
    <script src="../js/habilita-botao-payment.js"></script>
    <script src="../js/toggle-campos-cartao.js"></script>
    <script src="../js/parcelamento-payment.js"></script>
    <script src="../js/redirecionar-pagamento.js"></script>
    <script src="../js/validacao-payment.js"></script>
    <script src="../js/ajuste-data-nascimento.js"></script>
    <script src="../js/payment-submit.js"></script>
</body>
</html>