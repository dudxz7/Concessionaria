<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: login.html");
    exit;
}

// Conectar com o banco de dados
$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_bmw";

$conn = new mysqli($host, $user, $pass, $db);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Buscar os dados do usuário (agora incluindo foto_perfil)
$id = $_SESSION['usuarioId'];
$sql = "SELECT nome_completo, email, cpf, rg, cidade, estado, telefone, cnh, cargo, endereco, pis, foto_perfil FROM clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($nome_completo, $email, $cpf, $rg, $cidade, $estado, $telefone, $cnh, $cargo, $endereco, $pis, $foto_perfil);
    $stmt->fetch();

    // Se o usuário for Admin, redireciona para o painel de admin
    if ($cargo === 'Admin') {
        header("Location: php/admin_dashboard.php");
        exit;
    }

} else {
    echo "Erro ao recuperar os dados!";
    exit;
}

// Limpa pagamentos Pix pendentes expirados sempre que um funcionário acessar o perfil
if ($conn) {
    $conn->query("DELETE FROM pagamentos_pix WHERE expira_em <= NOW() AND status = 'pendente'");
}

// Verificando se houve atualização no perfil OU upload de imagem
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoEstado = $_POST['estado'];
    $novaCidade = $_POST['cidade'];
    $novoEndereco = $_POST['endereco'];
    $novoCaminhoFoto = $foto_perfil; // valor padrão

    // Se enviou imagem de perfil
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $tipos_mime_permitidos = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif'
        ];
        $max_tamanho = 2 * 1024 * 1024; // 2MB
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Verifica extensão
        if (in_array($ext, $extensoes_permitidas) && $file['size'] <= $max_tamanho) {
            // Verifica MIME real
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (in_array($mime_type, $tipos_mime_permitidos)) {
                $novo_nome = 'perfil_' . $id . '_' . time() . '.' . $ext;
                $destino = 'img/perfis/' . $novo_nome;

                // Remove imagem anterior se existir
                if (!empty($foto_perfil) && file_exists($foto_perfil)) {
                    unlink($foto_perfil);
                }
                if (move_uploaded_file($file['tmp_name'], $destino)) {
                    $novoCaminhoFoto = $destino;
                }
            } else {
                $_SESSION['mensagemSucesso'] = "Arquivo de imagem inválido! Apenas JPG, PNG, WEBP ou GIF são permitidos.";
                header("Location: perfil.php");
                exit;
            }
        } else if (!in_array($ext, $extensoes_permitidas)) {
            $_SESSION['mensagemSucesso'] = "Extensão de arquivo não permitida! Apenas JPG, PNG, WEBP ou GIF.";
            header("Location: perfil.php");
            exit;
        } else if ($file['size'] > $max_tamanho) {
            $_SESSION['mensagemSucesso'] = "Arquivo muito grande! O limite é 2MB.";
            header("Location: perfil.php");
            exit;
        }
    }

    // Atualizar os dados no banco de dados (incluindo foto_perfil)
    $updateSql = "UPDATE clientes SET estado = ?, cidade = ?, endereco = ?, foto_perfil = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssi", $novoEstado, $novaCidade, $novoEndereco, $novoCaminhoFoto, $id);
    $updateStmt->execute();

    // Atualizando a sessão com os novos dados
    $_SESSION['usuarioEstado'] = $novoEstado;
    $_SESSION['usuarioCidade'] = $novaCidade;

    // Definir mensagem de sucesso na sessão
    $_SESSION['mensagemSucesso'] = "Dados atualizados com sucesso!";

    // Redirecionando para atualizar os dados na página
    header("Location: perfil.php");
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil BMW</title>
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="icon" href="img/logos/logoofcbmw.png">
    <style>
    /* Aura azul ao passar o mouse na imagem de perfil */
    .profile-icon.has-image:hover .profile-upload-icon {
        box-shadow: 0 0 0 5px #2196f3, 0 0 20px 10px #2196f3aa;
        transition: box-shadow 0.3s;
    }
    /* Partículas */
    .profile-particle {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 8px;
        height: 8px;
        background: #2196f3;
        border-radius: 50%;
        pointer-events: none;
        opacity: 0.8;
        z-index: 10;
        /* Removendo animation fixa, será inline */
    }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <video autoplay loop muted>
                <source src="videos/overlay_azul.mp4" type="video/mp4">
                Seu navegador não suporta vídeos.
            </video>
            <div class="profile-icon<?php if (!empty($foto_perfil) && file_exists($foto_perfil)) echo ' has-image'; ?>" style="position:relative; cursor:pointer;"<?php if (!empty($foto_perfil) && file_exists($foto_perfil)) echo ' class="profile-icon has-image"'; ?>>
                <?php if (!empty($foto_perfil) && file_exists($foto_perfil)): ?>
                    <img class="profile-upload-icon" src="<?php echo $foto_perfil; ?>" alt="Foto de perfil" style="display:block;position:absolute;width:90px;height:90px;object-fit:cover;top:-5px;left:-5px;border-radius:50%;" />
                    <span class="profile-letter" style="display:none;"></span>
                <?php else: ?>
                    <span class="profile-letter"><?php echo strtoupper(substr($nome_completo, 0, 1)); ?></span>
                    <img class="profile-upload-icon" src="img/pasta.png" alt="Upload" style="display:block;position:absolute;width:32px;height:32px;object-fit:cover;cursor:pointer;opacity:0;transition:opacity 0.2s;" />
                <?php endif; ?>
                <input type="file" id="profile-image-input" name="profile_image" accept="image/*" style="display:none;" form="form-perfil" />
            </div>
            <p><strong><?php echo $nome_completo; ?></strong></p>
            <p><?php echo $email; ?></p>
            <div class="icons">
                <div class="icon-item" onclick="window.location.href='perfil.php'">
                    <img src="img/usersembarra.png" alt="Minha Conta">
                    <span>Minha conta</span>
                </div>
                <?php if ($cargo === 'Cliente'): ?>
                <div class="icon-item" onclick="window.location.href='php/favoritos.php'">
                    <img src="img/coracoes/coracao.png" alt="Favoritos">
                    <span>Favoritos</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/a_pagar.php'">
                    <img src="img/apagar.png" alt="A pagar">
                    <span>A pagar</span>
                </div>
                <div class="icon-item" onclick="window.location.href='php/historico_pagamentos.php'">
                    <img src="img/historico.png" alt="Historico de vendas">
                    <span>Histórico</span>
                </div>
                <?php endif; ?>
                <div class="icon-item" onclick="window.location.href='php/redefinir_a_senha.php'">
                    <img src="img/ajudando.png" alt="Esqueceu a Senha">
                    <span>Esqueceu a Senha</span>
                </div>
                <?php if ($cargo !== 'Cliente'): ?>
                    <div class="icon-item" onclick="window.location.href='php/venda_manual.php'">
                        <img src="img/pagar.png" alt="Realizar Venda">
                        <span>Realizar Venda</span>
                    </div>
                    <div class="icon-item" onclick="window.location.href='php/consultar_clientes.php'">
                        <img src="img/lupa.png" alt="Consultar clientes">
                        <span>Consultar Clientes</span>
                    </div>
                    <?php if ($cargo === 'Gerente' || $cargo === 'Admin'): ?>
                        <div class="icon-item" onclick="window.location.href='php/consultar_func_gerente.php'">
                            <img src="img/homem-de-negocios.png" alt="Consultar Funcionários e Gerentes">
                            <span>Consultar Funcionários</span>
                        </div>
                    <?php endif; ?>
                    <div class="icon-item" onclick="window.location.href='php/consultar_modelos.php'">
                        <img src="img/referencia.png" alt="Consultar Modelos">
                        <span>Consultar Modelos</span>
                    </div>
                    <div class="icon-item" onclick="window.location.href='php/consultar_veiculos.php'">
                        <img src="img/carro_de_frente.png" alt="Consultar Veículos">
                        <span>Consultar Veículos</span>
                    </div>
                    <div class="icon-item" onclick="window.location.href='php/consultar_promocoes.php'">
                        <img src="img/promocoes.png" alt="Consultar promoções">
                        <span>Consultar Promoções</span>
                    </div>
                    <div class="icon-item" onclick="window.location.href='php/consultar_vendas.php'">
                        <img src="img/venda.png" alt="Consultar Vendas">
                        <span>Consultar Vendas</span>
                    </div> 
                <?php endif; ?>
                <div class="icon-item" onclick="window.location.href='php/logout.php'">
                    <img src="img/sairr.png" alt="Sair">
                    <span>Sair</span>
                </div>
            </div>
        </div>
        <div class="content">
            <!-- Imagem da seta para voltar -->
            <a href="index.php" class="back-button">
                <img src="img/seta-esquerdabranca.png" alt="Voltar">
            </a>

            <h2>Meus dados</h2>
            <p id="descricao">Campos com (*) não podem ser alterados</p>

            <form method="POST" action="" enctype="multipart/form-data" id="form-perfil">
                <div class="form-grid">
                    <div class="left-column">
                        <div class="input-container">
                            <label for="nome">Nome*</label>
                            <input type="text" id="nome" value="<?php echo $nome_completo; ?>" readonly
                                class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="email">Email*</label>
                            <input type="email" id="email" value="<?php echo $email; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="cpf">CPF*</label>
                            <input type="text" id="cpf" value="<?php echo $cpf; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="rg">RG*</label>
                            <input type="text" id="rg" value="<?php echo $rg; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="cnh">CNH*</label>
                            <input type="text" id="cnh" value="<?php echo $cnh; ?>" readonly class="com-asterisco">
                        </div>
                        <div class="input-container">
                            <label for="telefone">Telefone*</label>
                            <input type="text" id="telefone" value="<?php echo $telefone; ?>" readonly
                                class="com-asterisco">
                        </div>
                    </div>
                    <div class="right-column">
                        <div class="input-container">
                            <label for="cargo">Cargo*</label>
                            <input type="text" id="cargo" value="<?php echo $cargo; ?>" readonly class="com-asterisco">
                        </div>
                        <?php if ($cargo !== 'Cliente'): ?>
                            <div class="input-container">
                                <label for="pis">Pis*</label>
                                <input type="text" id="pis" name="pis" value="<?php echo $pis; ?>" readonly
                                    class="com-asterisco">
                            </div>
                        <?php endif; ?>
                        <div class="input-container">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>" maxlength="2"
                                required>
                        </div>
                        <div class="input-container">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" value="<?php echo $cidade; ?>" maxlength="28"
                                required>
                        </div>
                        <?php if ($cargo !== 'Cliente'): ?>
                            <div class="input-container">
                                <label for="endereco">Endereço</label>
                                <input type="text" id="endereco" name="endereco" value="<?php echo $endereco; ?>"
                                    maxlength="100">
                            </div>
                        <?php endif; ?>
                        <div class="button-container">
                            <button type="submit" class="salvar-btn" id="btn-salvar" disabled>Salvar alterações</button>
                        </div>
                    </div>
                </div>

                <!-- Mensagem de sucesso (se presente) -->
                <?php if (isset($_SESSION['mensagemSucesso'])): ?>
                    <div class="mensagem-tratamento">
                        <?php
                        $msg = $_SESSION['mensagemSucesso'];
                        $icone = '';
                        // Detecta tipo de mensagem
                        if (strpos($msg, 'sucesso') !== false || strpos($msg, 'atualizados') !== false) {
                            $icone = '<img src="videos/escudo.gif" alt="Sucesso" style="width:30px;height:30px;vertical-align:middle;margin-right:7px;">';
                        } else {
                            $icone = '<img src="videos/alarme.gif" alt="Erro" style="width:30px;height:30px;vertical-align:middle;margin-right:7px;">';
                        }
                        echo $icone . htmlspecialchars($msg);
                        unset($_SESSION['mensagemSucesso']);
                        ?>
                    </div>
                <?php endif; ?>

            </form>
        </div>
    </div>
    <script src="js/function-perfil.js"></script>
    <script src="js/mudar-letra.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    var icon = document.querySelector('.profile-icon');
    var uploadIcon = icon.querySelector('.profile-upload-icon');
    var letter = icon.querySelector('.profile-letter');
    var fileInput = icon.querySelector('#profile-image-input');
    var btnSalvar = document.getElementById('btn-salvar');
    var campos = [
        document.getElementById('estado'),
        document.getElementById('cidade'),
        document.getElementById('endereco')
    ].filter(Boolean); // só campos existentes

    // Efeito partículas ao passar mouse na imagem de perfil (em todas as direções)
    if (icon.classList.contains('has-image')) {
        icon.addEventListener('mouseenter', function() {
            for (let i = 0; i < 12; i++) {
                let particle = document.createElement('div');
                particle.className = 'profile-particle';
                let angle = Math.random() * 2 * Math.PI; // 0 a 2PI radianos
                let distance = 60 + Math.random() * 30; // 60 a 90px
                let x = Math.cos(angle) * distance;
                let y = Math.sin(angle) * distance;
                particle.style.background = '#2196f3';
                particle.style.transition = 'transform 1.5s cubic-bezier(.22,1.02,.36,.99), opacity 1.5s';
                particle.style.transform = 'translate(-50%, -50%) scale(1)';
                icon.appendChild(particle);
                setTimeout(() => {
                    particle.style.transform = `translate(${x}px, ${y}px) scale(0.5)`;
                    particle.style.opacity = '0';
                }, 10);
                setTimeout(() => {
                    particle.remove();
                }, 1550);
            }
        });
    }

    // só mostra ícone de upload se não houver imagem de perfil e esconde a letra
    icon.addEventListener('mouseover', function() {
        if (uploadIcon.src.includes('img/pasta.png')) {
            uploadIcon.style.opacity = '1';
            letter.style.display = 'none';
        }
    });
    icon.addEventListener('mouseout', function() {
        if (uploadIcon.src.includes('img/pasta.png')) {
            uploadIcon.style.opacity = '0';
            letter.style.display = '';
        }
    });

    // Clique: abre seletor de arquivo
    icon.addEventListener('click', function(e) {
        fileInput.click();
    });

    // Habilita botão se trocar imagem
    fileInput.addEventListener('change', function(e) {
        if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                uploadIcon.src = ev.target.result;
                uploadIcon.style.opacity = '1';
                uploadIcon.style.width = '90px';
                uploadIcon.style.height = '90px';
                uploadIcon.style.top = '-5px';
                uploadIcon.style.left = '-5px';
                uploadIcon.style.transform = '';
                uploadIcon.style.borderRadius = '50%';
                letter.style.display = 'none';
                // Habilita o botão após o preview
                btnSalvar.disabled = false;
                btnSalvar.classList.remove('disabled');
                btnSalvar.style.opacity = '1';
                btnSalvar.style.pointerEvents = 'auto';
                btnSalvar.style.filter = 'none';
            };
            reader.readAsDataURL(fileInput.files[0]);
            // Fallback: habilita o botão imediatamente também
            btnSalvar.disabled = false;
            btnSalvar.classList.remove('disabled');
            btnSalvar.style.opacity = '1';
            btnSalvar.style.pointerEvents = 'auto';
            btnSalvar.style.filter = 'none';
        }
    });

    // Habilita botão se alterar qualquer campo editável
    campos.forEach(function(campo) {
        campo.addEventListener('input', function() {
            btnSalvar.disabled = false;
        });
    });
});
</script>
</body>
</html>