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

// --- BLOQUEIO DE ACESSO SE VEÍCULO SEM ESTOQUE ---
function modeloTemEstoque($conn, $modelo_id) {
    $sql = "SELECT 1 FROM estoque WHERE modelo_id = ? AND quantidade > 0 LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $modelo_id);
    $stmt->execute();
    $stmt->store_result();
    $tem = $stmt->num_rows > 0;
    $stmt->close();
    return $tem;
}

if ($modelo_id > 0 && !modeloTemEstoque($conn, $modelo_id)) {
    header('Location: pagina_veiculo.php?id=' . $modelo_id);
    exit;
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

            <div id="notificacao-pagamento" style="display:none;">
                <span id="notificacao-icone"></span>
                <span id="notificacao-texto"></span>
            </div>

            <form>
                <!-- Campos de cartão, inicialmente ocultos, mas no topo do formulário -->
                <div id="campos-cartao" style="display:none;">
                    <div class="campo-form campo-cartao-numero" style="position: relative;">
                        <label for="numero_cartao">Número do cartão</label>
                        <img id="bandeira-cartao" src="../img/formas-de-pagamento/creditcard_mb.png"
                            alt="Bandeira do cartão"
                            style="position: absolute; left: 8px; top: 37px; width: 48px; height: 32px; object-fit: contain; transition: 0.2s;" />
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

                <div class="campo-form" id="campo-nome-cartao" style="display:none;">
                    <label for="nome_cartao">Nome impresso no cartão</label>
                    <input type="text" id="nome_cartao" name="nome_cartao" placeholder="Nome impresso no cartão" maxlength="50" />
                </div>

                <div class="campo-form">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" placeholder="Nome completo" />
                </div>

                <div class="campo-form">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" />
                </div>

                <div class="linha-dupla">
                    <div>
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="CPF" />
                    </div>

                    <div>
                        <label for="data_nasc">Data de nascimento</label>
                        <input type="text" id="data_nasc" name="data_nasc" placeholder="DD/MM/AAAA" maxlength="10" pattern="\\d{2}/\\d{2}/\\d{4}" />
                    </div>
                </div>

                <div class="campo-form">
                    <label for="telefone">Número de telefone</label>
                    <input type="text" id="telefone" name="telefone" placeholder="Número de telefone" />
                </div>

                <div class="campo-form" id="campo-parcelamento" style="margin-top: -10px; display: none;">
                    <label for="parcelamento">Parcelamento</label>
                    <select id="parcelamento" name="parcelamento">
                        <option value="1">1 x R$ 209,99 = R$ 209,99 (sem juros)</option>
                        <option value="2">2 x R$ 105,00 = R$ 210,00 (sem juros)</option>
                        <option value="3">3 x R$ 70,00 = R$ 210,00 (sem juros)</option>
                        <option value="4">4 x R$ 52,50 = R$ 210,00 (sem juros)</option>
                        <option value="5">5 x R$ 42,00 = R$ 210,00 (sem juros)</option>
                        <option value="6">6 x R$ 35,00 = R$ 210,00 (sem juros)</option>
                        <option value="7">7 x R$ 30,00 = R$ 210,00 (sem juros)</option>
                        <option value="8">8 x R$ 26,25 = R$ 210,00 (sem juros)</option>
                        <option value="9">9 x R$ 23,33 = R$ 209,97 (sem juros)</option>
                        <option value="10">10 x R$ 21,00 = R$ 210,00 (sem juros)</option>
                        <option value="11">11 x R$ 20,50 = R$ 225,50 (com juros)</option>
                        <option value="12">12 x R$ 19,00 = R$ 228,00 (com juros)</option>
                        <option value="24">24 x R$ 12,50 = R$ 300,00 (com juros)</option>
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
    <script>
    // Função única para detectar bandeira do cartão (Visa, Master, Amex, Elo, Hipercard)
    function detectarBandeira(numero) {
        numero = numero.replace(/\D/g, '');
        if (/^4/.test(numero)) return 'visa';
        if (/^(5[1-5]|2[2-7])/.test(numero)) return 'mastercard';
        if (/^3[47]/.test(numero)) return 'amex';
        if (/^(606282|3841)/.test(numero)) return 'hipercard';
        if (/^(4011|4312|4389|4514|4576|5041|5066|5067|5090|6277|6362|6363|6504|6505|6509|6516|6550)/.test(numero)) return 'elo';
        return null;
    }

    // Esconde campos pessoais quando "Cartão" está selecionado e mostra nome impresso no cartão
    function toggleCamposCartao() {
        const isCartao = document.getElementById('cartao').checked;
        document.getElementById('cpf').parentElement.style.display = isCartao ? 'none' : '';
        document.getElementById('email').parentElement.style.display = isCartao ? 'none' : '';
        document.getElementById('data_nasc').parentElement.style.display = isCartao ? 'none' : '';
        document.getElementById('telefone').parentElement.style.display = isCartao ? 'none' : '';
        document.getElementById('nome').parentElement.style.display = isCartao ? 'none' : '';
        document.getElementById('campo-nome-cartao').style.display = isCartao ? '' : 'none';
        document.getElementById('campos-cartao').style.display = isCartao ? '' : 'none';
        // Corrige: sempre habilita os inputs de cartão ao selecionar Cartão
        const cartaoInputs = document.querySelectorAll('#campos-cartao input, #campo-nome-cartao input');
        cartaoInputs.forEach(input => {
            input.disabled = !isCartao ? false : false; // Sempre habilita
        });
    }
    document.getElementById('cartao').addEventListener('change', toggleCamposCartao);
    document.getElementById('pix').addEventListener('change', toggleCamposCartao);
    document.getElementById('boleto').addEventListener('change', toggleCamposCartao);
    toggleCamposCartao();

    // Validação dinâmica dos campos required conforme a forma de pagamento
    function atualizarRequiredCampos() {
        const isCartao = document.getElementById('cartao').checked;
        // Campos cartão
        document.getElementById('numero_cartao').required = isCartao;
        document.getElementById('validade').required = isCartao;
        document.getElementById('cvv').required = isCartao;
        document.getElementById('nome_cartao').required = isCartao;
        // Campos pessoais
        document.getElementById('nome').required = !isCartao;
        document.getElementById('email').required = !isCartao;
        document.getElementById('cpf').required = !isCartao;
        document.getElementById('data_nasc').required = !isCartao;
        document.getElementById('telefone').required = !isCartao;
    }
    document.getElementById('cartao').addEventListener('change', atualizarRequiredCampos);
    document.getElementById('pix').addEventListener('change', atualizarRequiredCampos);
    document.getElementById('boleto').addEventListener('change', atualizarRequiredCampos);
    atualizarRequiredCampos();

    // Corrige o comportamento do botão para cada forma de pagamento
    const botaoPagamento = document.getElementById('botao-pagamento');
    botaoPagamento.addEventListener('click', function(e) {
        const isCartao = document.getElementById('cartao').checked;
        const isPix = document.getElementById('pix').checked;
        const isBoleto = document.getElementById('boleto').checked;
        let camposObrigatorios = [];
        if (isCartao) {
            camposObrigatorios = [
                'numero_cartao',
                'validade',
                'cvv',
                'nome_cartao'
            ];
        } else {
            camposObrigatorios = [
                'nome',
                'email',
                'cpf',
                'data_nasc',
                'telefone'
            ];
        }
        let faltando = false;
        for (const id of camposObrigatorios) {
            const el = document.getElementById(id);
            if (!el || el.disabled || el.offsetParent === null) continue; // ignora campos ocultos/desabilitados
            if (!el.value || el.value.trim() === '') {
                faltando = true;
                break;
            }
        }
        if (faltando) {
            e.preventDefault();
            mostrarNotificacao('erro', null, 'Preencha todos os campos obrigatórios.');
            return false;
        }
        if (isCartao) {
            e.preventDefault();
            mostrarNotificacao('pendente');
            setTimeout(function() {
                const numero = document.getElementById('numero_cartao').value.trim();
                const bandeira = detectarBandeira(numero);
                if (/^1+$/.test(numero) || !bandeira) {
                    mostrarNotificacao('recusado');
                } else {
                    mostrarNotificacao('aprovado', bandeira);
                }
                setTimeout(function() {
                    document.getElementById('notificacao-pagamento').style.display = 'none';
                }, 2000);
            }, 2000);
            return false;
        }
        // Para Pix/Boleto, deixa o form submeter normalmente
    });

    // Adiciona CSS para animação das bolinhas e estilos fiéis ao exemplo
    (function(){
        const style = document.createElement('style');
        style.innerHTML = `
        #notificacao-pagamento {
            position: fixed !important;
            top: 0 !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 99999 !important;
            min-width: 340px !important;
            min-height: 56px !important;
            max-width: 95vw !important;
            padding: 16px 28px !important;
            /* Border radius apenas no lado esquerdo */
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 25px !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 25px !important;
            font-size: 1.08rem !important;
            font-weight: 500 !important;
            text-align: left !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin-top: 12px !important;
            margin: 0 !important;
            pointer-events: none !important;
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            transition: border 0.2s, box-shadow 0.2s, background 0.2s;
            box-sizing: border-box !important;
            height: 56px !important;
        }
        #notificacao-icone, .notif-icone, .notif-check-animado, .notif-bolinhas {
            min-width: 32px;
            min-height: 32px;
            max-width: 32px;
            max-height: 32px;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .notif-check-animado svg {
            width: 32px;
            height: 32px;
            display: block;
        }
        .notif-x-animado {
            font-size: 1.6rem !important;
            line-height: 32px !important;
            width: 32px;
            height: 32px;
            text-align: center;
        }
        .notif-bolinhas {
            height: 32px;
            align-items: center;
        }
        .notif-bolinha {
            width: 8px;
            height: 8px;
        }
        #notificacao-texto {
            flex: 1 1 0%;
            min-width: 0;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            display: flex;
            align-items: center;
            /* Removido height: 100% para evitar corte de texto */
            line-height: 1.3;
            font-size: 1.08rem;
            font-weight: 500;
        }
        #notificacao-pagamento.notif-erro,
        #notificacao-pagamento.notif-pendente,
        #notificacao-pagamento.notif-sucesso {
            border-width: 0 2px 2px 2px !important;
            border-style: solid !important;
            box-shadow: 0 6px 32px rgba(0,0,0,0.18) !important;
        }
        #notificacao-pagamento:not(.notif-erro):not(.notif-pendente):not(.notif-sucesso) {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
        #notificacao-pagamento.notif-erro {
            background: #ffd6d6 !important;
            color: #b30000 !important;
            border-color: transparent #b30000 #b30000 #b30000 !important;
        }
        #notificacao-pagamento.notif-pendente {
            background: #fff7c2 !important;
            color: #3a3a00 !important;
            border-color: transparent #e6c200 #e6c200 #e6c200 !important;
        }
        #notificacao-pagamento.notif-sucesso {
            background: #d6f5d6 !important;
            color: #217a2b !important;
            border-color: transparent #217a2b #217a2b #217a2b !important;
        }
        .notif-icone {
            font-size: 2.2rem !important;
            margin-right: 0 !important;
            vertical-align: middle !important;
            display: flex !important;
            align-items: center !important;
            height: 2.2rem !important;
        }
        .notif-bolinhas {
            display: inline-flex;
            gap: 4px;
            vertical-align: middle;
            margin-right: 8px;
        }
        .notif-bolinha {
            width: 10px;
            height: 10px;
            background: #3a3a00;
            border-radius: 50%;
            display: inline-block;
            animation: notif-bounce 1s infinite;
        }
        .notif-bolinha:nth-child(2) { animation-delay: 0.2s; }
        .notif-bolinha:nth-child(3) { animation-delay: 0.4s; }
        @keyframes notif-bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .notif-x-animado {
            display: inline-block;
            animation: notif-x-shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        @keyframes notif-x-shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
            100% { transform: translateX(0); }
        }
        /* Animação do check SVG */
        .notif-check-animado {
            display: inline-block;
            width: 38px;
            height: 38px;
        }
        .notif-check-animado svg {
            width: 38px;
            height: 38px;
            display: block;
        }
        .notif-check-circle {
            stroke-dasharray: 120;
            stroke-dashoffset: 120;
            animation: notif-circle-draw 0.5s ease-out forwards;
        }
        .notif-check-mark {
            stroke-dasharray: 30;
            stroke-dashoffset: 30;
            animation: notif-check-draw 0.4s 0.4s cubic-bezier(.65,.05,.36,1) forwards;
        }
        @keyframes notif-circle-draw {
            to { stroke-dashoffset: 0; }
        }
        @keyframes notif-check-draw {
            to { stroke-dashoffset: 0; }
        }
        `;
        document.head.appendChild(style);
    })();

    // Substitui o HTML da notificação para não ter NENHUM estilo inline
    const notifDiv = document.getElementById('notificacao-pagamento');
    if (notifDiv) {
      notifDiv.removeAttribute('style');
      notifDiv.innerHTML = '<span id="notificacao-icone"></span><span id="notificacao-texto"></span>';
      notifDiv.style.display = 'none';
    }

    // Altera a função para sempre esconder a notificação após 2s (ou tempo customizado)
    function mostrarNotificacao(status, bandeira, mensagemCustom) {
        const notificacao = document.getElementById('notificacao-pagamento');
        const icone = document.getElementById('notificacao-icone');
        const texto = document.getElementById('notificacao-texto');
        notificacao.className = '';
        let msg = '';
        let icon = '';
        let tempo = 2000;
        if (mensagemCustom) {
            notificacao.classList.add('notif-erro');
            icon = '<span class="notif-icone notif-x-animado">❌</span>';
            msg = '<b>Seu pagamento foi recusado, tente novamente!</b>';
        } else if (status === 'pendente') {
            notificacao.classList.add('notif-pendente');
            icon = `<span class="notif-bolinhas">
                <span class="notif-bolinha"></span>
                <span class="notif-bolinha"></span>
                <span class="notif-bolinha"></span>
            </span>`;
            msg = '<b>Aguardando  a confirmação do pagamento</b>';
            tempo = 3000;
        } else if (status === 'aprovado') {
            notificacao.classList.add('notif-sucesso');
            // SVG animado do check
            icon = `<span class="notif-icone notif-check-animado">
                <svg viewBox="0 0 38 38">
                  <circle class="notif-check-circle" cx="19" cy="19" r="18" fill="none" stroke="#217a2b" stroke-width="3"/>
                  <polyline class="notif-check-mark" points="10,20 17,28 28,13" fill="none" stroke="#217a2b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>`;
            msg = '<b>Sucesso.</b> Pagamento realizado com sucesso!';
        } else if (status === 'recusado' || status === 'erro') {
            notificacao.classList.add('notif-erro');
            icon = '<span class="notif-icone notif-x-animado">❌</span>';
            msg = '<b>Seu pagamento foi recusado, tente novamente!</b>';
        }
        notificacao.style.display = '';
        icone.innerHTML = icon;
        texto.innerHTML = msg;
        // Remove a animação do X após 0.6s para permitir repetir se mostrar de novo
        if (notificacao.classList.contains('notif-erro')) {
            setTimeout(()=>{
                const x = icone.querySelector('.notif-x-animado');
                if(x) x.classList.remove('notif-x-animado');
            }, 600);
        }
        // Reinicia a animação do check SVG sempre que mostrar
        if (notificacao.classList.contains('notif-sucesso')) {
            setTimeout(()=>{
                const svg = icone.querySelector('svg');
                if(svg) {
                    svg.querySelector('.notif-check-circle').style.strokeDashoffset = 120;
                    svg.querySelector('.notif-check-mark').style.strokeDashoffset = 30;
                    // Força reflow para reiniciar animação
                    void svg.offsetWidth;
                    svg.querySelector('.notif-check-circle').style.animation = 'notif-circle-draw 0.5s ease-out forwards';
                    svg.querySelector('.notif-check-mark').style.animation = 'notif-check-draw 0.4s 0.4s cubic-bezier(.65,.05,.36,1) forwards';
                }
            }, 10);
        }
        // Esconde a notificação após o tempo, exceto se for pendente
        clearTimeout(window._notifTimeout);
        if (status !== 'pendente') {
            window._notifTimeout = setTimeout(()=>{
                notificacao.style.display = 'none';
                notificacao.className = '';
                notificacao.style.background = 'transparent';
                notificacao.style.border = 'none';
                notificacao.style.boxShadow = 'none';
                icone.innerHTML = '';
                texto.innerHTML = '';
            }, tempo);
        }
    }
    // Bloqueia qualquer submit do form se for Cartão (protege contra scripts externos)
    // REMOVIDO: listeners de submit duplicados e fetch de exemplo. O fluxo é controlado apenas pelo payment-submit.js
    </script>
</body>
</html>