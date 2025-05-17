<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

// Conexão com o banco de dados
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

$mensagem = '';
$mensagem_tipo = '';

$modelo_id_get = isset($_GET['modelo_id']) ? intval($_GET['modelo_id']) : (isset($_POST['modelo_id']) ? intval($_POST['modelo_id']) : null);
$modeloSelecionado = '';
$coresDisponiveis = [];
$corPrincipalAtual = null;
$limiteMaximo = 9;
$imagensJaCadastradas = 0;
$limiteRestante = $limiteMaximo;

if ($modelo_id_get) {
    // Busca dados do modelo e cores disponíveis
    $stmt = $conn->prepare("SELECT modelo, cor FROM modelos WHERE id = ?");
    $stmt->bind_param("i", $modelo_id_get);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $modeloSelecionado = $row['modelo'];
        $coresDisponiveis = array_map('trim', explode(',', $row['cor']));
    }
    $stmt->close();

    // Busca cor principal atual no detalhes_modelos
    $stmt2 = $conn->prepare("SELECT cor_principal FROM detalhes_modelos WHERE modelo_id = ?");
    $stmt2->bind_param("i", $modelo_id_get);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    if ($row2 = $res2->fetch_assoc()) {
        $corPrincipalAtual = $row2['cor_principal'];
    }
    $stmt2->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo_id = intval($_POST['modelo_id'] ?? 0);
    $cor_principal = $_POST['cor_principal'] ?? null;
    $cor_upload = $_POST['cor_upload'] ?? null;
    $imagens = $_FILES['imagens'] ?? null;

    $quantidadeNovas = ($imagens && !empty($imagens['name'][0])) ? count($imagens['name']) : 0;

    // Validações básicas
    if (!$modelo_id || !$cor_principal) {
        $mensagem = "Preencha todos os campos obrigatórios.";
        $mensagem_tipo = "erro";
    } elseif (!in_array($cor_principal, $coresDisponiveis)) {
        $mensagem = "Cor principal inválida.";
        $mensagem_tipo = "erro";
    } elseif ($quantidadeNovas > 0) {
        if (!$cor_upload || !in_array($cor_upload, $coresDisponiveis)) {
            $mensagem = "Cor para upload inválida.";
            $mensagem_tipo = "erro";
        } else {
            // Conta quantas imagens já existem para essa cor e modelo
            $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM imagens_secundarias WHERE modelo_id = ? AND cor = ?");
            $stmt->bind_param("is", $modelo_id, $cor_upload);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $imagensJaCadastradas = intval($row['total']);
            $stmt->close();

            $limiteRestante = $limiteMaximo - $imagensJaCadastradas;

            if ($limiteRestante <= 0) {
                $mensagem = "Você já cadastrou o limite de $limiteMaximo imagens para esta cor.";
                $mensagem_tipo = "erro";
            } elseif ($quantidadeNovas > $limiteRestante) {
                $mensagem = "Você só pode enviar mais $limiteRestante imagem(ns) para esta cor.";
                $mensagem_tipo = "erro";
            }
        }
    }

    if (empty($mensagem)) {
        // Atualiza cor principal, se já existir atualiza, senão insere
        $stmt = $conn->prepare("UPDATE detalhes_modelos SET cor_principal = ? WHERE modelo_id = ?");
        $stmt->bind_param("si", $cor_principal, $modelo_id);
        $stmt->execute();

        // Se não atualizou nenhuma linha, só faz o INSERT se não existir
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM detalhes_modelos WHERE modelo_id = ?");
            $stmt->bind_param("i", $modelo_id);
            $stmt->execute();
            $stmt->bind_result($existe);
            $stmt->fetch();
            $stmt->close();
            if ($existe == 0) {
                $stmt = $conn->prepare("INSERT INTO detalhes_modelos (modelo_id, cor_principal) VALUES (?, ?)");
                $stmt->bind_param("is", $modelo_id, $cor_principal);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt->close();
        }

        // Se tiver imagens para upload
        if ($quantidadeNovas > 0) {
            $modelo_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($modeloSelecionado));
            $cor_slug = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($cor_upload));
            $pasta_base = __DIR__ . "/../img/modelos/cores/{$modelo_slug}/{$cor_slug}/";

            if (!is_dir($pasta_base) && !mkdir($pasta_base, 0777, true)) {
                $mensagem = "Erro ao criar a pasta para upload.";
                $mensagem_tipo = "erro";
            } else {
                $indice = $imagensJaCadastradas + 1;

                for ($i = 0; $i < $quantidadeNovas; $i++) {
                    if ($imagens['error'][$i] === UPLOAD_ERR_OK) {
                        $tmp_name = $imagens['tmp_name'][$i];
                        $extensao = strtolower(pathinfo($imagens['name'][$i], PATHINFO_EXTENSION));
                        $nome_arquivo = "{$indice}.{$extensao}";
                        $destino = $pasta_base . $nome_arquivo;

                        if (move_uploaded_file($tmp_name, $destino)) {
                            $ordem = $indice; 
                            $stmt = $conn->prepare("INSERT INTO imagens_secundarias (modelo_id, imagem, cor, ordem) VALUES (?, ?, ?, ?)");
                            $stmt->bind_param("issi", $modelo_id, $nome_arquivo, $cor_upload, $ordem);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            $mensagem = "Erro ao mover o arquivo: $nome_arquivo.";
                            $mensagem_tipo = "erro";
                            break;
                        }
                        $indice++;
                    } else {
                        $mensagem = "Erro no upload do arquivo: " . $imagens['name'][$i];
                        $mensagem_tipo = "erro";
                        break;
                    }
                }
            }
        }

        if (empty($mensagem)) {
            $mensagem = "Cor principal atualizada";
            if ($quantidadeNovas > 0) {
                $mensagem .= " e imagens enviadas com sucesso!";
            } else {
                $mensagem .= " com sucesso!";
            }
            $mensagem_tipo = "sucesso";
            $corPrincipalAtual = $cor_principal;
            $limiteRestante -= $quantidadeNovas;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Upload Imagens Secundárias - Definir Cor Principal</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="stylesheet" href="../css/drag-and-drop.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <a href="consultar_modelos.php" class="back-button">
            <img src="../img/seta-esquerda24.png" alt="Voltar">
        </a>
        <h2>Upload de Imagens Secundárias</h2>

        <form action="cadastrar_imagens_secundarias.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="modelo_id" value="<?= htmlspecialchars($modelo_id_get) ?>">

            <!-- Modelo (não editável) -->
            <div class="input-group">
                <label>Modelo</label>
                <div class="input-wrapper">
                    <input type="text" value="<?= htmlspecialchars($modeloSelecionado) ?>" readonly>
                    <img src="../img/veiculos/carro.png" alt="Ícone modelo">
                </div>
            </div>

            <!-- Cor Principal -->
            <div class="input-group">
                <label>Cor Principal (exibida nos cards)</label>
                <div class="input-wrapper">
                    <?php if (count($coresDisponiveis) === 1): ?>
                        <input type="text" name="cor_principal" value="<?= htmlspecialchars($coresDisponiveis[0]) ?>"
                            readonly>
                    <?php else: ?>
                        <select name="cor_principal" required>
                            <option value="">Selecione a cor principal</option>
                            <?php
                            $cor_principal_selecionada = $_POST['cor_principal'] ?? $corPrincipalAtual ?? '';
                            foreach ($coresDisponiveis as $cor) {
                                $selected = ($cor === $cor_principal_selecionada) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($cor) . "\" $selected>" . htmlspecialchars($cor) . "</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                    <img src="../img/veiculos/cor.png" alt="Ícone cor">
                </div>
            </div>

            <!-- Cor para Upload de Imagens -->
            <div class="input-group">
                <label>Cor para Upload das Imagens</label>
                <div class="input-wrapper">
                    <?php if (count($coresDisponiveis) === 1): ?>
                        <input type="text" name="cor_upload" value="<?= htmlspecialchars($coresDisponiveis[0]) ?>" readonly>
                    <?php else: ?>
                        <select name="cor_upload">
                            <option value="">Selecione uma cor para o upload</option>
                            <?php
                            $cor_upload_selecionada = $_POST['cor_upload'] ?? '';
                            foreach ($coresDisponiveis as $cor) {
                                $selected = ($cor === $cor_upload_selecionada) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($cor) . "\" $selected>" . htmlspecialchars($cor) . "</option>";
                            }
                            ?>
                        </select>
                    <?php endif; ?>
                    <img src="../img/veiculos/cor.png" alt="Ícone cor">
                </div>
            </div>

            <label>Imagens:</label>
            <div class="custom-file-upload">
                <button type="button" id="btn-upload">Selecionar arquivo</button>
                <span id="file-names">Nenhum arquivo selecionado</span>
                <input type="file" id="file-input" name="imagens[]" accept="image/*" multiple style="display:none;">
            </div>

            <div id="preview-container"></div>
            <div id="ordem-campos"></div>

            <!-- Instrução sobre a ordem das imagens -->
            <div class="input-group">
                <label style="margin-bottom: 15px;">Ordem recomendada das imagens</label>
                <p style="font-size: 14px; color: #555; margin-top: -10px; margin-bottom: 20px;">
                    As imagens devem seguir um giro de <strong>360° ao redor do carro</strong>, começando pela mesma
                    posição da imagem principal. A última imagem pode ser do <strong>interior do carro</strong>
                    (opcional).
                </p>
            </div>

            <!-- Mensagem -->
            <?php if (!empty($mensagem)): ?>
                <div id="error-message" class="<?= $mensagem_tipo ?>" style="display:block;">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Salvar alterações
            </button>
        </form>
    </div>
    <script src="../js/multiplas-drag-and-drop.js"></script>
</body>
</html>