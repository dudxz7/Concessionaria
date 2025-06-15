<?php
// Iniciar a sessão (certifique-se de que a sessão não foi iniciada anteriormente)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pega os dados da sessão com segurança
$cargo_usuario = $_SESSION['usuarioCargo'] ?? 'Cliente'; // Se o cargo não estiver setado, assume 'Cliente'
$nome_completo = $_SESSION['usuarioNome'] ?? 'Usuário';
$email = $_SESSION['usuarioEmail'] ?? '';
$usuario_id = $_SESSION['usuarioId'] ?? null;
$foto_perfil = '';

// Busca a foto do banco de dados se houver id
if ($usuario_id) {
    $conn = new mysqli('localhost', 'root', '', 'sistema_bmw');
    if ($conn->connect_error) {
        $foto_perfil = '';
    } else {
        $stmt = $conn->prepare('SELECT foto_perfil FROM clientes WHERE id = ?');
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $stmt->bind_result($foto_perfil_db);
        if ($stmt->fetch() && !empty($foto_perfil_db)) {
            $foto_perfil = basename($foto_perfil_db); // só o nome do arquivo
        }
        $stmt->close();
    }
}
?>
<div class="sidebar">
    <?php if ($cargo_usuario === 'Admin'): ?>
        <video autoplay loop muted>
            <source src="../videos/overlay_branca.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        <div class="profile-icon<?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)) echo ' has-image'; ?>" id="sidebar-profile-icon" style="position:relative; width:90px; height:90px; margin:0 auto 10px auto; cursor:pointer;">
            <?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)): ?>
                <img class="profile-upload-icon" src="<?php echo htmlspecialchars('../img/perfis/' . $foto_perfil); ?>" alt="Foto de perfil" style="width:90px;height:90px;object-fit:cover;border-radius:50%;display:block;position:absolute;top:0;left:0;box-shadow:none;transition:box-shadow 0.3s;z-index:1;" />
                <span class="profile-letter" style="display:none;"></span>
            <?php else: ?>
                <span class="profile-letter">
                    <?php echo strtoupper(substr($nome_completo, 0, 1)); ?>
                </span>
                <img class="profile-upload-icon" src="../img/pasta.png" alt="Upload" style="display:block;position:absolute;width:32px;height:32px;object-fit:cover;cursor:pointer;opacity:0;transition:opacity 0.2s;top:29px;left:29px;z-index:2;" />
            <?php endif; ?>
            <?php if ($cargo_usuario === 'Admin'): ?>
                <input type="file" id="sidebar-profile-image-input" name="profile_image" accept="image/*" style="display:none;" />
            <?php endif; ?>
        </div>
        <p><strong><?php echo htmlspecialchars($nome_completo); ?></strong></p>
        <p><?php echo htmlspecialchars($email); ?></p>
        <div class="icons">
            <div class="icon-item" onclick="window.location.href='admin_dashboard.php'">
                <img src="../img/casa.png" alt="Dashboard">
                <span>Dashboard</span>
            </div>
            <div class="icon-item" onclick="window.location.href='cadastro_admin.php'">
                <img src="../img/novo-usuario.png" alt="Cadastro do admin">
                <span>Cadastrar</span>
            </div>
            <div class="icon-item" onclick="window.location.href='funcoes_admin.php'">
                <img src="../img/referencia.png" alt="Funções">
                <span>Funções</span>
            </div>
            <div class="icon-item" onclick="window.location.href='redefinir_a_senha.php'">
                <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                <span>Esqueceu a Senha</span>
            </div>
            <div class="icon-item" onclick="window.location.href='logout.php'">
                <img src="../img/sairr.png" alt="Sair">
                <span>Sair</span>
            </div>
        </div>
    <?php elseif ($cargo_usuario === 'Cliente'): ?>
        <video autoplay loop muted>
            <source src="../videos/overlay_azul.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        <div class="profile-icon<?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)) echo ' has-image'; ?>" id="sidebar-profile-icon" style="position:relative; width:90px; height:90px; margin:0 auto 10px auto; cursor:pointer;">
            <?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)): ?>
                <img class="profile-upload-icon" src="<?php echo htmlspecialchars('../img/perfis/' . $foto_perfil); ?>" alt="Foto de perfil" style="width:90px;height:90px;object-fit:cover;border-radius:50%;display:block;position:absolute;top:0;left:0;box-shadow:none;transition:box-shadow 0.3s;z-index:1;" />
                <span class="profile-letter" style="display:none;"></span>
            <?php else: ?>
                <span class="profile-letter">
                    <?php echo strtoupper(substr($nome_completo, 0, 1)); ?>
                </span>
                <img class="profile-upload-icon" src="../img/pasta.png" alt="Upload" style="display:block;position:absolute;width:32px;height:32px;object-fit:cover;cursor:pointer;opacity:0;transition:opacity 0.2s;top:29px;left:29px;z-index:2;" />
            <?php endif; ?>
        </div>
        <p><strong><?php echo htmlspecialchars($nome_completo); ?></strong></p>
        <p><?php echo htmlspecialchars($email); ?></p>
        <div class="icons">
            <div class="icon-item" onclick="window.location.href='../perfil.php'">
                <img src="../img/usersembarra.png" alt="Minha Conta">
                <span>Minha conta</span>
            </div>
            <div class="icon-item" onclick="window.location.href='favoritos.php'">
                <img src="../img/navbar/heart.png" alt="Favoritos">
                <span>Favoritos</span>
            </div>
            <div class="icon-item" onclick="window.location.href='redefinir_a_senha.php'">
                <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                <span>Esqueceu a Senha</span>
            </div>
            <div class="icon-item" onclick="window.location.href='logout.php'">
                <img src="../img/sairr.png" alt="Sair">
                <span>Sair</span>
            </div>
        </div>
    <?php else: ?>
        <video autoplay loop muted>
            <source src="../videos/overlay_azul.mp4" type="video/mp4">
            Seu navegador não suporta vídeos.
        </video>
        <div class="profile-icon<?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)) echo ' has-image'; ?>" id="sidebar-profile-icon" style="position:relative; width:90px; height:90px; margin:0 auto 10px auto; cursor:pointer;">
            <?php if (!empty($foto_perfil) && file_exists(__DIR__ . '/../img/perfis/' . $foto_perfil)): ?>
                <img class="profile-upload-icon" src="<?php echo htmlspecialchars('../img/perfis/' . $foto_perfil); ?>" alt="Foto de perfil" style="width:90px;height:90px;object-fit:cover;border-radius:50%;display:block;position:absolute;top:0;left:0;box-shadow:none;transition:box-shadow 0.3s;z-index:1;" />
                <span class="profile-letter" style="display:none;"></span>
            <?php else: ?>
                <span class="profile-letter" style="display:flex;align-items:center;justify-content:center;width:90px;height:90px;background:#2196f3;color:#fff;font-size:2.5rem;font-weight:bold;border-radius:50%;user-select:none;position:absolute;top:0;left:0;z-index:1;">
                    <?php echo strtoupper(substr($nome_completo, 0, 1)); ?>
                </span>
                <img class="profile-upload-icon" src="../img/pasta.png" alt="Upload" style="display:block;position:absolute;width:32px;height:32px;object-fit:cover;cursor:pointer;opacity:0;transition:opacity 0.2s;top:29px;left:29px;z-index:2;" />
            <?php endif; ?>
            <!-- Removido input file para usuários não-Admin -->
        </div>
        <p><strong><?php echo htmlspecialchars($nome_completo); ?></strong></p>
        <p><?php echo htmlspecialchars($email); ?></p>
        <div class="icons">
            <div class="icon-item" onclick="window.location.href='../perfil.php'">
                <img src="../img/usersembarra.png" alt="Minha Conta">
                <span>Minha conta</span>
            </div>
            <div class="icon-item" onclick="window.location.href='redefinir_a_senha.php'">
                <img src="../img/ajudando.png" alt="Esqueceu a Senha">
                <span>Esqueceu a Senha</span>
            </div>
            <div class="icon-item" onclick="window.location.href='consultar_clientes.php'">
                <img src="../img/lupa.png" alt="Consultar clientes">
                <span>Consultar Clientes</span>
            </div>
            <?php if ($cargo_usuario === 'Gerente' || $cargo_usuario === 'Admin'): ?>
            <div class="icon-item" onclick="window.location.href='consultar_func_gerente.php'">
                <img src="../img/homem-de-negocios.png" alt="Consultar Funcionários e Gerentes">
                <span>Consultar Funcionários</span>
            </div>
            <?php endif; ?>
            <div class="icon-item" onclick="window.location.href='consultar_modelos.php'">
                <img src="../img/referencia.png" alt="Consultar Modelos">
                <span>Consultar Modelos</span>
            </div>
            <div class="icon-item" onclick="window.location.href='consultar_veiculos.php'">
                <img src="../img/carro_de_frente.png" alt="Consultar Veículos">
                <span>Consultar Veículos</span>
            </div>
            <div class="icon-item" onclick="window.location.href='consultar_promocoes.php'">
                <img src="../img/promocoes.png" alt="Consultar promoções">
                <span>Consultar Promoções</span>
            </div>
            <div class="icon-item" onclick="window.location.href='logout.php'">
                <img src="../img/sairr.png" alt="Sair">
                <span>Sair</span>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php if ($cargo_usuario === 'Admin'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var icon = document.getElementById('sidebar-profile-icon');
    var uploadIcon = icon.querySelector('.profile-upload-icon');
    var letter = icon.querySelector('.profile-letter');
    var fileInput = icon.querySelector('#sidebar-profile-image-input');

    // Efeito partículas ao passar mouse na imagem de perfil (igual perfil.php)
    if (icon.classList.contains('has-image')) {
        icon.addEventListener('mouseenter', function() {
            for (let i = 0; i < 12; i++) {
                let particle = document.createElement('div');
                particle.className = 'profile-particle';
                let angle = Math.random() * 2 * Math.PI;
                let distance = 60 + Math.random() * 30;
                let x = Math.cos(angle) * distance;
                let y = Math.sin(angle) * distance;
                // Cor dinâmica conforme cargo
                particle.style.background = 'rgb(243, 33, 33)';
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

    // Hover: mostra ícone de upload se não houver imagem
    icon.addEventListener('mouseover', function() {
        if (uploadIcon.src.includes('img/pasta.png')) {
            uploadIcon.style.opacity = '1';
            if (letter) letter.style.display = 'none';
        }
    });
    icon.addEventListener('mouseout', function() {
        if (uploadIcon.src.includes('img/pasta.png')) {
            uploadIcon.style.opacity = '0';
            if (letter) letter.style.display = '';
        }
    });

    // Clique: abre seletor de arquivo
    icon.addEventListener('click', function(e) {
        fileInput.click();
    });

    // Preview da imagem ao selecionar e faz upload automático
    fileInput.addEventListener('change', function(e) {
        if (fileInput.files && fileInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(ev) {
                uploadIcon.src = ev.target.result;
                uploadIcon.style.opacity = '1';
                uploadIcon.style.width = '90px';
                uploadIcon.style.height = '90px';
                uploadIcon.style.top = '0';
                uploadIcon.style.left = '0';
                uploadIcon.style.transform = '';
                uploadIcon.style.borderRadius = '50%';
                if (letter) letter.style.display = 'none';
            };
            reader.readAsDataURL(fileInput.files[0]);

            // Upload automático via AJAX
            var formData = new FormData();
            formData.append('profile_image', fileInput.files[0]);
            fetch('/Sistema_BMW/php/upload_foto_perfil.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.caminho) {
                    uploadIcon.src = data.caminho + '?t=' + Date.now();
                    icon.classList.add('has-image');
                    if (letter) letter.style.display = 'none';
                } else {
                    alert(data.error || 'Erro ao fazer upload da imagem.');
                }
            })
            .catch((err) => {
                alert('Erro ao enviar imagem.');
            });
        }
    });
});
</script>
<?php endif; ?>
<?php if ($cargo_usuario === 'Admin'): ?>
<style>
.profile-icon.has-image:hover .profile-upload-icon {
    box-shadow: 0 0 0 5px rgb(226, 2, 2), 0 0 20px 10px rgba(243, 33, 33, 0.67) !important;
    transition: box-shadow 0.3s;
}
.profile-particle {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    background:rgb(243, 33, 33);
    border-radius: 50%;
    pointer-events: none;
    opacity: 0.8;
    z-index: 10;
}
</style>
<?php else: ?>
<style>
.profile-icon.has-image:hover .profile-upload-icon {
    box-shadow: 0 0 0 5px #2196f3, 0 0 20px 10px #2196f3aa !important;
    transition: box-shadow 0.3s;
}
.profile-icon.has-image {
    border: none !important;
    background: none !important;
}
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
}
</style>
<?php endif; ?>