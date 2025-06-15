<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'conexao.php';

// Função para gerar uma chave única para cada tentativa de pagamento
function gerarChavePagamento($id_veiculo, $cor, $usuarioId) {
    return 'pagamento_' . $usuarioId . '_' . $id_veiculo . '_' . md5(strtolower($cor));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['cor'])) {
    $id = intval($_POST['id']);
    $cor = trim($_POST['cor']);
    $usuarioId = $_SESSION['usuarioId'] ?? null;
    if (!$usuarioId) {
        echo 'erro';
        exit;
    }
    $expira_em = time() + 72 * 60 * 60; // 72 horas
    $chave = gerarChavePagamento($id, $cor, $usuarioId);
    $_SESSION[$chave] = [
        'expira_em' => $expira_em
    ];
    $_SESSION['pagamento_autorizado'] = true;
    $_SESSION['pagamento_id'] = $id;
    $_SESSION['pagamento_cor'] = $cor;
    echo 'ok';
    exit;
}
echo 'erro';
exit;
