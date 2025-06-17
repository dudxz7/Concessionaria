<?php
// cards-a-pagar.php
// Exibe os cards dos veículos "a pagar" do usuário logado, vindos de pagamento_pix-pendentes e pagamento_boleto

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioId']) && isset($_SESSION['id_usuario'])) {
    $_SESSION['usuarioId'] = $_SESSION['id_usuario'];
}
if (!isset($_SESSION['usuarioId'])) {
    echo '<p>Você precisa estar logado para ver seus itens a pagar.</p>';
    return;
}
$usuarioId = $_SESSION['usuarioId'];

require_once 'conexao.php';

// Funções utilitárias (copiadas de cards-favoritos.php)
if (!function_exists('encontrarImagemVeiculo')) {
    function encontrarImagemVeiculo($modelo, $cor, $nomeArquivoBase)
    {
        $modeloFormatado = strtolower(str_replace(' ', '-', $modelo));
        $pasta_fs = __DIR__ . "/../img/modelos/cores/{$modeloFormatado}/{$cor}/";
        $pasta_web = "img/modelos/cores/{$modeloFormatado}/{$cor}/";
        $extensao = pathinfo($nomeArquivoBase, PATHINFO_EXTENSION);
        if ($extensao) {
            if (file_exists($pasta_fs . $nomeArquivoBase)) {
                return $pasta_web . $nomeArquivoBase;
            }
            return $pasta_web . $nomeArquivoBase;
        } else {
            $extensoes = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            foreach ($extensoes as $ext) {
                $arquivo = $nomeArquivoBase . '.' . $ext;
                if (file_exists($pasta_fs . $arquivo)) {
                    return $pasta_web . $arquivo;
                }
            }
            return "img/modelos/padrao.webp";
        }
    }
}
if (!function_exists('gerarAno')) {
    function gerarAno($ano)
    {
        return ($ano - 1) . '/' . $ano;
    }
}
if (!function_exists('gerarRating')) {
    function gerarRating()
    {
        $cheias = rand(3, 5);
        $estrelas = array_fill(0, $cheias, 'estrela.png');
        if (count($estrelas) < 5 && rand(0, 1))
            $estrelas[] = 'estrela-metade.png';
        while (count($estrelas) < 5)
            $estrelas[] = 'estrela-neutra.png';
        return $estrelas;
    }
}
if (!function_exists('gerarNota')) {
    function gerarNota()
    {
        return rand(1, 1500);
    }
}

// Busca os pares modelo+cor a pagar do usuário nas duas tabelas
$pagamentosAPagar = [];

// PIX pendentes
$sqlPix = "SELECT veiculo_id, cor FROM pagamentos_pix WHERE usuario_id = ? AND status = 'pendente' AND expira_em > NOW()";
$stmtPix = $conn->prepare($sqlPix);
$stmtPix->bind_param('i', $usuarioId);
$stmtPix->execute();
$resultPix = $stmtPix->get_result();
while ($row = $resultPix->fetch_assoc()) {
    $pagamentosAPagar[] = ['id' => $row['veiculo_id'], 'cor' => $row['cor']];
}

// Boleto pendentes
$sqlBoleto = "SELECT veiculo_id, cor FROM pagamento_boleto WHERE usuario_id = ? AND status = 'pendente'";
$stmtBoleto = $conn->prepare($sqlBoleto);
$stmtBoleto->bind_param('i', $usuarioId);
$stmtBoleto->execute();
$resultBoleto = $stmtBoleto->get_result();
while ($row = $resultBoleto->fetch_assoc()) {
    $pagamentosAPagar[] = ['id' => $row['veiculo_id'], 'cor' => $row['cor']];
}

// Remove duplicados exatos (modelo+cor)
$pagamentosAPagar = array_unique($pagamentosAPagar, SORT_REGULAR);

if (empty($pagamentosAPagar)) {
    echo '<div style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;min-height:60vh;width:100vw;padding-left:27vw;">';
    echo '<img src="../videos/giphy2.webp" alt="Sem veículos" style="width:450px;height:400px;margin-bottom:24px;display:block;">';
    echo '<p class="espaco-galactico-flutuante" style="position:relative;">Você não possui veículos a pagar no momento.';
    // Estrelas animadas sobre o p
    for ($i = 0; $i < 32; $i++) {
        $size = rand(3, 5);
        $top = rand(10, 90);
        $left = rand(5, 95);
        $duration = rand(7, 15);
        $delay = rand(0, 10) / 10;
        echo '<span class="star-galaxy" style="pointer-events:none;position:absolute;top:' . $top . '%;left:' . $left . '%;width:' . $size . 'px;height:' . $size . 'px;border-radius:50%;background:rgba(255,255,255,' . (rand(7, 10) / 10) . ');box-shadow:0 0 8px #fff,0 0 16px #fff;animation:galaxyStar ' . $duration . 's linear ' . $delay . 's infinite;z-index:0;"></span>';
    }
    echo '</p>';
    echo '<style>
.star-galaxy {
    animation: galaxyStar 10s linear infinite;
}

@keyframes galaxyStar {
    0% { opacity: 0.7; }
    50% { opacity: 1; filter: blur(1.5px); }
    100% { opacity: 0.7; }
}

.espaco-galactico-flutuante {
    font-size: 20px;
    font-weight: bold;

    animation: pulsarTexto 2.5s ease-in-out infinite;
}

@keyframes pulsarTexto {
    0%, 100% {
    transform: scale(1);
}
    50% {
    transform: scale(1.03);
}
}
</style>';
    echo '</div>';
    return;
}

// Busca dados dos modelos a pagar
$cards = [];
foreach ($pagamentosAPagar as $par) {
    $id = $par['id'];
    $cor = $par['cor'];
    $sqlModelos = "SELECT m.id, m.modelo, m.ano, m.preco, d.descricao, (
        SELECT i.imagem FROM imagens_secundarias i WHERE i.modelo_id = m.id AND i.cor = ? AND i.ordem = 1 LIMIT 1
    ) AS imagem_padrao FROM modelos m LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id WHERE m.id = ? GROUP BY m.id";
    $stmtModelos = $conn->prepare($sqlModelos);
    $stmtModelos->bind_param('si', $cor, $id);
    $stmtModelos->execute();
    $resultModelos = $stmtModelos->get_result();
    if ($carro = $resultModelos->fetch_assoc()) {
        $carro['cor_selecionada'] = $cor;
        $cards[] = $carro;
    }
}

if (empty($cards)) {
    echo '<p style="margin:2rem;font-size:1.2rem;">Nenhum veículo encontrado.</p>';
    return;
}

foreach ($cards as $carro) {
    $imagemBase = $carro['imagem_padrao'] ?? 'padrao';
    $corSelecionada = $carro['cor_selecionada'] ?? '';
    $imagemPath = encontrarImagemVeiculo($carro['modelo'], $corSelecionada, $imagemBase);
    $anoFormatado = gerarAno($carro['ano']);
    $rating = gerarRating();
    $nota = gerarNota();

    // Busca promoção ativa para o modelo
    $sqlPromo = "SELECT preco_com_desconto FROM promocoes WHERE modelo_id = ? AND status = 'Ativa' AND data_limite > NOW() LIMIT 1";
    $stmtPromo = $conn->prepare($sqlPromo);
    $stmtPromo->bind_param('i', $carro['id']);
    $stmtPromo->execute();
    $promo = $stmtPromo->get_result()->fetch_assoc();
    $precoExibir = $promo && !empty($promo['preco_com_desconto']) ? $promo['preco_com_desconto'] : $carro['preco'];

    // Determina a forma de pagamento (Pix ou Boleto)
    $formaPagamento = '';
    $formaPagamentoTexto = '';
    $sqlPixCheck = "SELECT 1 FROM pagamentos_pix WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' AND expira_em > NOW() LIMIT 1";
    $stmtPixCheck = $conn->prepare($sqlPixCheck);
    $stmtPixCheck->bind_param('iis', $usuarioId, $carro['id'], $corSelecionada);
    $stmtPixCheck->execute();
    $stmtPixCheck->store_result();
    if ($stmtPixCheck->num_rows > 0) {
        $formaPagamento = '<span class="forma-pagamento-card pix"><img src="../img/formas-de-pagamento/icons8-foto-240.png" alt="Pix"></span>';
        $formaPagamentoTexto = 'Pix';
    } else {
        $sqlBoletoCheck = "SELECT 1 FROM pagamento_boleto WHERE usuario_id = ? AND veiculo_id = ? AND cor = ? AND status = 'pendente' LIMIT 1";
        $stmtBoletoCheck = $conn->prepare($sqlBoletoCheck);
        $stmtBoletoCheck->bind_param('iis', $usuarioId, $carro['id'], $corSelecionada);
        $stmtBoletoCheck->execute();
        $stmtBoletoCheck->store_result();
        if ($stmtBoletoCheck->num_rows > 0) {
            $formaPagamento = '<span class="forma-pagamento-card boleto"><img src="../img/formas-de-pagamento/boletov2.png" alt="Boleto"></span>';
            $formaPagamentoTexto = 'Boleto';
        }
        $stmtBoletoCheck->close();
    }
    $stmtPixCheck->close();

    echo '<div class="card">';
    echo '<div class="img-container-card-pagamento" style="position:relative;display:inline-block;width:100%;max-width:320px;">';
    if ($formaPagamento) {
        echo '<span class="forma-pagamento-overlay">' . $formaPagamento . '</span>';
    }
    echo '</div>';
    echo '<img src="' . htmlspecialchars('../' . $imagemPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($carro['modelo'], ENT_QUOTES) . '" style="width:100%;max-width:320px;">';
    echo '<h2>' . htmlspecialchars($carro['modelo']) . '</h2>';
    echo '<p>' . htmlspecialchars($carro['descricao']) . '</p>';
    echo '<p><img src="../img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="../img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>';
    echo '<div class="rating">';
    foreach ($rating as $estrela) {
        echo '<img src="../img/cards/' . $estrela . '" alt="estrela">';
    }
    echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span></div>';
    echo '<h2>R$ ' . number_format($precoExibir, 2, ',', '.') . '</h2>';
    echo '<button class="btn-send">STATUS</button>';
    echo '<a href="../php/pagamento.php?id=' . $carro['id'] . '&cor=' . urlencode($corSelecionada) . '&redir=1" class="btn-link">';
    echo '<button class="btn-send">Pagar</button>';
    echo '</a>';
    // Botão Cancelar sem link, com data-id e data-cor
    $cancelarId = htmlspecialchars($carro['id'], ENT_QUOTES);
    $cancelarCor = htmlspecialchars($corSelecionada, ENT_QUOTES);
    // Adiciona dados para o modal de confirmação
    echo '<button class="btn-send cancelar btn-cancelar-modal" data-id="' . $cancelarId . '" data-cor="' . $cancelarCor . '" data-nome="' . htmlspecialchars($carro['modelo']) . '" data-forma="' . $formaPagamentoTexto . '" data-corveic="' . htmlspecialchars($corSelecionada) . '">Cancelar</button>';
    echo '</div>';
}
?>
<!-- Modal de confirmação de cancelamento -->
<div id="modal-cancelar-pagamento" class="modal-cancelar-pagamento" style="display:none;">
    <div class="modal-cancelar-content modern-modal">
        <div class="modal-cancelar-icon">
            <img src="../img/alerta.png" alt="Atenção" style="width:48px;height:48px;">
        </div>
        <h3>Cancelar pagamento?</h3>
        <p class="modal-cancelar-texto">Tem certeza que deseja cancelar este pagamento?<br><span
                style="color:#f44336;font-weight:500;">Esta ação não poderá ser desfeita.</span></p>
        <div class="modal-cancelar-botoes">
            <button id="confirmar-cancelar-pagamento" class="btn-send cancelar btn-modal-sim">Sim, cancelar</button>
            <button id="cancelar-cancelar-pagamento" class="btn-send btn-modal-nao">Não</button>
        </div>
        <div id="cancelar-pagamento-status" class="modal-cancelar-status"></div>
    </div>
</div>
<script>
    // Modal logic
    let cancelarId = null;
    let cancelarCor = null;
    const modal = document.getElementById('modal-cancelar-pagamento');
    const btnsCancelar = document.querySelectorAll('.btn-cancelar-modal');
    const btnSim = document.getElementById('confirmar-cancelar-pagamento');
    const statusDiv = document.getElementById('cancelar-pagamento-status');

    btnsCancelar.forEach(btn => {
        btn.addEventListener('click', function () {
            cancelarId = this.getAttribute('data-id');
            cancelarCor = this.getAttribute('data-cor');
            const nomeVeiculo = this.getAttribute('data-nome');
            const corVeiculo = this.getAttribute('data-corveic');
            const formaPagamento = this.getAttribute('data-forma');
            statusDiv.textContent = '';
            // Mensagem personalizada
            document.querySelector('.modal-cancelar-texto').innerHTML =
                'Você deseja cancelar a tentativa de pagamento do veículo <b>' + nomeVeiculo +
                '</b> da cor <b>' + corVeiculo + '</b> com a forma de pagamento <b>' + formaPagamento +
                '</b>?<br><span style="color:#f44336;font-weight:500;">Esta ação não poderá ser desfeita.</span>';
            modal.style.display = 'block';
        });
    });
    btnNao.onclick = function () {
        modal.style.display = 'none';
    };
    window.onclick = function (event) {
        if (event.target == modal) modal.style.display = 'none';
    };
    btnSim.onclick = function () {
        statusDiv.textContent = 'Cancelando...';
        fetch('../php/cancelar_pagamento.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(cancelarId) + '&cor=' + encodeURIComponent(cancelarCor)
        })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    statusDiv.textContent = 'Pagamento cancelado com sucesso!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                } else {
                    statusDiv.textContent = data.erro || 'Erro ao cancelar.';
                }
            })
            .catch(() => {
                statusDiv.textContent = 'Erro ao cancelar.';
            });
    };
</script>
<style>
    /* Modal Cancelar Pagamento - Moderno e elegante */
    .modal-cancelar-pagamento {
        display: none;
        position: fixed;
        z-index: 1000;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(8px);
        justify-content: center;
        align-items: center;
        width: 100vw;
        height: 100vh;
        display: flex;
    }

    .modal-cancelar-pagamento[style*="block"] {
        display: flex !important;
    }

    .modern-modal {
        background: #fff;
        border-radius: 22px;
        padding: 38px 32px 32px 32px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
        width: 95vw;
        max-width: 370px;
        position: relative;
        text-align: center;
        border: none;
        font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        animation: modalPopIn 0.18s cubic-bezier(.4, 1.4, .6, 1) 1;
    }

    @keyframes modalPopIn {
        0% {
            transform: scale(0.92);
            opacity: 0;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .modal-cancelar-icon {
        margin-bottom: 12px;
    }

    .modal-cancelar-content h3 {
        margin: 0 0 8px 0;
        font-size: 1.35rem;
        color: #222;
        font-weight: 700;
    }

    .modal-cancelar-texto {
        color: #444;
        font-size: 1.08rem;
        margin-bottom: 32px;
        /* aumentado para dar mais espaço */
        margin-top: 18px;
        /* espaço extra acima do texto */
    }

    .modal-cancelar-botoes {
        display: flex;
        gap: 16px;
        justify-content: center;
        margin-bottom: 10px;
    }

    .btn-modal-sim {
        background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 1.08rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(244, 67, 54, 0.08);
        transition: background 0.2s;
    }

    .btn-modal-sim:hover {
        background: linear-gradient(90deg, #d32f2f 0%, #f44336 100%);
    }

    .btn-modal-nao {
        background: #e0e0e0;
        color: #444;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: 1.08rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-modal-nao:hover {
        background: #bdbdbd;
    }

    .modal-cancelar-status {
        margin-top: 10px;
        font-size: 0.98rem;
        color: rgb(0, 0, 0);
        min-height: 18px;
    }

    @media (max-width: 500px) {
        .modern-modal {
            padding: 22px 6vw 22px 6vw;
            max-width: 98vw;
        }

        .modal-cancelar-botoes {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>