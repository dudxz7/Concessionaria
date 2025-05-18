<?php
session_start();

// Verifica login
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

// Conexão
$conn = new mysqli("localhost", "root", "", "sistema_bmw");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica permissão do usuário
$usuarioId = $_SESSION['usuarioId'] ?? null;
if ($usuarioId) {
    $sql = "SELECT cargo FROM clientes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    $dados = $res->fetch_assoc();
    $stmt->close();

    if (!$dados || !in_array($dados['cargo'], ['Admin', 'Gerente'])) {
        echo "<h2>Acesso Negado</h2><p>Você não tem permissão para acessar esta página.</p>";
        exit;
    }
} else {
    echo "Usuário não identificado.";
    exit;
}

// Pega o modelo_id da URL
$modelo_id = isset($_GET['modelo_id']) ? intval($_GET['modelo_id']) : 0;

// Busca as cores distintas cadastradas para esse modelo
$stmtCores = $conn->prepare("SELECT DISTINCT cor FROM imagens_secundarias WHERE modelo_id = ? ORDER BY LOWER(cor) ASC");
$stmtCores->bind_param("i", $modelo_id);
$stmtCores->execute();
$resCores = $stmtCores->get_result();
$coresDisponiveis = [];
while ($row = $resCores->fetch_assoc()) {
    $coresDisponiveis[] = $row['cor'];
}
$stmtCores->close();

// Define a cor com base no que foi enviado pela URL (GET) e se for válida
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['cor']) && in_array($_GET['cor'], $coresDisponiveis)) {
    $cor = $_GET['cor'];
} else {
    // Se nenhuma cor foi enviada ou não é válida, usa a primeira disponível
    $cor = $coresDisponiveis[0] ?? '';
}

if (!$modelo_id || !$cor) {
    echo "<h2>Não há nenhuma imagem cadastrada nesse modelo.</h2>";
    exit;
}

// Ao enviar o formulário para salvar a nova ordem
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ordem']) && is_array($_POST['ordem'])) {
    $ordemArray = $_POST['ordem'];

    // Atualiza no banco a ordem das imagens
    $stmtUpdate = $conn->prepare("UPDATE imagens_secundarias SET ordem = ? WHERE id = ? AND modelo_id = ? AND cor = ?");
    foreach ($ordemArray as $ordem => $id_imagem) {
        $ordemAtual = intval($ordem) + 1; // Começa a ordem em 1
        $id_imagemInt = intval($id_imagem);
        $stmtUpdate->bind_param("iiss", $ordemAtual, $id_imagemInt, $modelo_id, $cor);
        $stmtUpdate->execute();
    }
    $stmtUpdate->close();

    $mensagem = "Ordem das imagens atualizada com sucesso!";
}

// Busca as imagens desse modelo e cor, ordenadas pela ordem atual
$stmt = $conn->prepare("SELECT id, imagem FROM imagens_secundarias WHERE modelo_id = ? AND cor = ? ORDER BY ordem ASC");
$stmt->bind_param("is", $modelo_id, $cor);
$stmt->execute();
$result = $stmt->get_result();
$imagens = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Busca nome do modelo para exibir no título
$stmt = $conn->prepare("SELECT modelo FROM modelos WHERE id = ?");
$stmt->bind_param("i", $modelo_id);
$stmt->execute();
$res = $stmt->get_result();
$modeloNome = ($row = $res->fetch_assoc()) ? $row['modelo'] : 'Modelo desconhecido';
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Editar Ordem das Imagens - <?= htmlspecialchars($modeloNome) ?> (Cor: <?= htmlspecialchars($cor) ?>)</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/editar_ordem_imagens.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
    <style>
        /* Exemplo básico, adapte conforme seu CSS */
        #sortable {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        #sortable li {
            border: 1px solid #ccc;
            padding: 5px;
            background: #f9f9f9;
            cursor: move;
            width: 150px;
            text-align: center;
            position: relative;
        }
        #sortable li.dragging {
            opacity: 0.5;
        }
        #sortable img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 5px;
        }
        .btn-excluir {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 0.8em;
            position: absolute;
            top: 5px;
            right: 5px;
            border-radius: 3px;
        }
        .btn-excluir:hover {
            background-color: #c9302c;
        }
        .mensagem-sucesso {
            background: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>

        <h2>Editar Ordem das Imagens</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-sucesso"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="get" id="formCor">
            <input type="hidden" name="modelo_id" value="<?= $modelo_id ?>">

            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" id="modelo_nome" value="<?= htmlspecialchars($modeloNome) ?>" readonly>
                </div>
            </div>

            <div>
                <label for="cor">Selecione a cor:</label><br>
                <select name="cor" id="cor" onchange="document.getElementById('formCor').submit()">
                    <?php foreach ($coresDisponiveis as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $c === $cor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if (count($imagens) === 0): ?>
            <p>Não há imagens cadastradas para este modelo e cor.</p>
        <?php else: ?>
            <form method="post" action="">
                <ul id="sortable">
                    <?php foreach ($imagens as $img): ?>
                        <li data-id="<?= $img['id'] ?>" draggable="true">
                            <img src="../img/modelos/cores/<?= strtolower(preg_replace('/[^a-z0-9\-]/i', '-', $modeloNome)) ?>/<?= strtolower(preg_replace('/[^a-z0-9\-]/i', '-', $cor)) ?>/<?= htmlspecialchars($img['imagem']) ?>"
                                alt="Imagem <?= $img['id'] ?>">
                            <span><?= htmlspecialchars($img['imagem']) ?></span>

                            <button type="button" title="Excluir imagem" class="btn-excluir" onclick="excluirImagem(<?= $img['id'] ?>)">Excluir</button>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Inputs hidden para ordem -->
                <div id="inputs-ordem"></div>

                <button type="submit" class="btn-topi">Salvar nova ordem</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Drag and drop simplificado
        const sortable = document.getElementById('sortable');
        let draggedItem = null;
        const modeloId = <?= json_encode($modelo_id) ?>;
        const cor = <?= json_encode($cor) ?>;

        sortable.addEventListener('dragstart', e => {
            draggedItem = e.target;
            e.target.classList.add('dragging');
        });

        sortable.addEventListener('dragend', e => {
            e.target.classList.remove('dragging');
            updateHiddenInputs();
        });

        sortable.addEventListener('dragover', e => {
            e.preventDefault();
            const afterElement = getDragAfterElement(sortable, e.clientY);
            if (afterElement == null) {
                sortable.appendChild(draggedItem);
            } else {
                sortable.insertBefore(draggedItem, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('li:not(.dragging)')];

            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child }
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        // Atualiza os inputs hidden conforme a ordem da lista
        function updateHiddenInputs() {
            const container = document.getElementById('inputs-ordem');
            container.innerHTML = ''; // limpa

            const items = sortable.querySelectorAll('li');
            items.forEach(item => {
                const id = item.getAttribute('data-id');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ordem[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        // Inicializa os inputs na carga da página
        updateHiddenInputs();

        // Função para excluir imagem - implementar lógica via AJAX ou redirecionamento

    function excluirImagem(imagemId) {
        if (!confirm('Tem certeza que deseja excluir esta imagem?')) return;
        window.location.href = 'excluir_imagem.php?imagem_id=' + imagemId + '&modelo_id=' + modeloId + '&cor=' + encodeURIComponent(cor);
    }

    </script>
</body>
</html>