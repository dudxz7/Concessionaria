<?php
session_start();

// Verificar se o usuário está logado e se é Admin ou Gerente
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioLogado'] !== true) {
    header("Location: ../login.html");
    exit;
}

$cargoUsuario = $_SESSION['usuarioCargo'] ?? '';

if ($cargoUsuario !== 'Admin' && $cargoUsuario !== 'Gerente') {
    header("Location: ../login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro BMW - Admin</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logos/logoofcbmw.png">
</head>
<body>
    <div class="container">
        <?php if ($cargoUsuario === 'Admin'): ?>
            <a href="funcoes_admin.php" class="back-button">
                <img src="../img/seta-esquerda24.png" alt="Voltar">
            </a>
        <?php else: ?>
            <a href="consultar_func_gerente.php" class="back-button">
                <img src="../img/seta-esquerda24.png" alt="Voltar">
            </a>
        <?php endif; ?>
        <h2>Criar uma conta</h2>
        <form action="register.php" method="post">
            <input type="hidden" name="redir" id="redir" value="3">
            <?php if (isset($_GET['redirect']) && $_GET['redirect'] === 'venda_manual'): ?>
                <input type="hidden" name="redirect" value="venda_manual">
            <?php endif; ?>
            <div class="input-group">
                <label>Nome completo</label>
                <div class="input-wrapper">
                    <input type="text" name="nome" placeholder="Nome completo" required>
                    <img src="../img/registro/usersemfundo.png" alt="Ícone usuário">
                </div>
            </div>

            <div class="input-group">
                <label>E-mail</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="E-mail" required>
                    <img src="../img/registro/e-mail.png" alt="Ícone e-mail">
                </div>
            </div>

            <div class="input-group">
                <label>Cargo</label>
                <div class="input-wrapper">
                    <select name="cargo" id="cargo" required>
                        <option value="">Selecione o cargo</option>
                        <option value="Cliente">Cliente</option>
                        <option value="Funcionario">Funcionário</option>
                        <?php if ($cargoUsuario === 'Admin'): ?>
                            <option value="Gerente">Gerente</option>
                        <?php endif; ?>
                    </select>
                    <img src="../img/cargo.png" alt="Ícone cargo">
                </div>
            </div>

            <div id="camposExtras" style="display: none;">
                <div class="input-group">
                    <label>PIS</label>
                    <div class="input-wrapper">
                        <input type="text" id="pis" name="pis" placeholder="Número do PIS" maxlength="11">
                        <img src="../img/registro/arquivo.png" alt="Ícone PIS">
                    </div>
                </div>

                <div class="input-group">
                    <label>Endereço</label>
                    <div class="input-wrapper">
                        <input type="text" name="endereco" id="endereco" placeholder="Endereço completo">
                        <img src="../img/registro/lugar-colocar.png" alt="Ícone endereço">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="input-group">
                    <label>CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="cpf" placeholder="CPF" id="cpf" required>
                        <img src="../img/registro/arquivo.png" alt="Ícone CPF">
                    </div>
                </div>
                <div class="input-group">
                    <label>RG</label>
                    <div class="input-wrapper">
                        <input type="text" name="rg" placeholder="RG" id="rg" required>
                        <img src="../img/registro/cartaozin.png" alt="Ícone RG">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="input-group">
                    <label>Cidade</label>
                    <div class="input-wrapper">
                        <input type="text" name="cidade" placeholder="Cidade" id="cidade" required>
                        <img src="../img/registro/construcao-da-cidade.png" alt="Ícone cidade">
                    </div>
                </div>
                <div class="input-group">
                    <label>Estado</label>
                    <div class="input-wrapper">
                        <input type="text" name="estado" placeholder="Estado Abreviado" id="estado" required
                            maxlength="2">
                        <img src="../img/registro/lugar-colocar.png" alt="Ícone estado">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="input-group">
                    <label>Telefone</label>
                    <div class="input-wrapper">
                        <input type="tel" name="telefone" placeholder="Telefone" id="telefone" required>
                        <img src="../img/registro/telefone.png" alt="Ícone telefone">
                    </div>
                </div>
                <div class="input-group">
                    <label>CNH</label>
                    <div class="input-wrapper">
                        <input type="text" name="cnh" placeholder="CNH" id="cnh" required>
                        <img src="../img/registro/carteira-de-motorista.png" alt="Ícone CNH">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="input-group">
                    <label>Senha</label>
                    <div class="input-wrapper">
                        <input type="password" name="senha" placeholder="******" id="senha" required>
                        <img src="../img/olhofechado.png" alt="Ícone senha" id="eyeIcon">
                    </div>
                </div>
                <div class="input-group">
                    <label>Confirme a senha</label>
                    <div class="input-wrapper">
                        <input type="password" name="confirma_senha" placeholder="******" id="confirmaSenha" required>
                        <img src="../img/olhofechado.png" alt="Ícone confirmar senha" id="eyeIcon2">
                    </div>
                </div>
            </div>

            <div id="error-message" style="color: red; display: none;"></div>

            <button type="submit" class="btn">
                <img src="../img/registro/verifica.png" alt="Ícone de check">
                Criar conta
            </button>
            <input type="hidden" name="origem" value="admin">
        </form>
    </div>
    <script src="../js/validacoes-registro.js"></script>
    <script src="../js/cadastro_admin.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const eyeIcon = document.getElementById("eyeIcon");
    const eyeIcon2 = document.getElementById("eyeIcon2");
    const senhaInput = document.getElementById("senha");
    const confirmaSenhaInput = document.getElementById("confirmaSenha");
    const errorMessage = document.getElementById("error-message");

    let senhaVisivel = false;

    function atualizarVisibilidade() {
        senhaInput.type = senhaVisivel ? "text" : "password";
        confirmaSenhaInput.type = senhaVisivel ? "text" : "password";
        const eyeSrc = senhaVisivel ? "../img/olhoaberto.png" : "../img/olhofechado.png";
        eyeIcon.src = eyeSrc;
        eyeIcon2.src = eyeSrc;
    }

    function togglePasswordVisibility() {
        senhaVisivel = !senhaVisivel;
        atualizarVisibilidade();
    }

    // Aplica o toggle em ambos os olhos
    eyeIcon?.addEventListener("click", togglePasswordVisibility);
    eyeIcon2?.addEventListener("click", togglePasswordVisibility);

    // Verificação de senhas
    function verificarSenhas() {
        if (senhaInput.value !== confirmaSenhaInput.value) {
            errorMessage.textContent = "As senhas não coincidem.";
            errorMessage.style.display = "block";
        } else {
            errorMessage.textContent = "";
            errorMessage.style.display = "none";
        }
    }

    senhaInput.addEventListener("input", verificarSenhas);
    confirmaSenhaInput.addEventListener("input", verificarSenhas);

    document.querySelector("form").addEventListener("submit", function (e) {
        if (senhaInput.value !== confirmaSenhaInput.value) {
            e.preventDefault();
            errorMessage.textContent = "As senhas não coincidem.";
            errorMessage.style.display = "block";
        }
    });

    // Campos extras
    const cargoSelect = document.getElementById('cargo');
    const camposExtras = document.getElementById('camposExtras');
    const redirInput = document.getElementById('redir');
    const pisInput = document.getElementById('pis');
    const enderecoInput = document.getElementById('endereco');

    function atualizarCamposExtras() {
        if (cargoSelect.value === 'Funcionario' || cargoSelect.value === 'Gerente') {
            camposExtras.style.display = '';
            pisInput.required = true;
            enderecoInput.required = true;
            redirInput.value = '3';
        } else {
            camposExtras.style.display = 'none';
            pisInput.required = false;
            enderecoInput.required = false;
            redirInput.value = '2';
        }
    }

    cargoSelect.addEventListener('change', atualizarCamposExtras);
    atualizarCamposExtras(); // inicializa corretamente
});
</script>
<!-- <script> ABRIR E FECHAR SENAHS DE FORMA UNICA ( NAO SIMUTANEAMENTE )
    // Código para abrir e fechar as senhas de forma única
        document.addEventListener("DOMContentLoaded", function () {
            const eyeIcon = document.getElementById("eyeIcon");
            const senhaInput = document.getElementById("senha");
            const confirmaSenhaInput = document.getElementById("confirmaSenha");
            const eyeIcon2 = document.getElementById("eyeIcon2");
            const errorMessage = document.getElementById("error-message");

            function togglePasswordVisibility(inputElement, eyeElement) {
                if (inputElement.type === "password") {
                    inputElement.type = "text";
                    eyeElement.src = "../img/olhoaberto.png";
                } else {
                    inputElement.type = "password";
                    eyeElement.src = "../img/olhofechado.png";
                }
            }

            if (eyeIcon && senhaInput) {
                eyeIcon.addEventListener("click", function () {
                    togglePasswordVisibility(senhaInput, eyeIcon);
                });
            }

            if (eyeIcon2 && confirmaSenhaInput) {
                eyeIcon2.addEventListener("click", function () {
                    togglePasswordVisibility(confirmaSenhaInput, eyeIcon2);
                });
            }

            function verificarSenhas() {
                if (senhaInput.value !== confirmaSenhaInput.value) {
                    errorMessage.textContent = "As senhas não coincidem.";
                    errorMessage.style.display = "block";
                } else {
                    errorMessage.textContent = "";
                    errorMessage.style.display = "none";
                }
            }

            senhaInput.addEventListener("input", verificarSenhas);
            confirmaSenhaInput.addEventListener("input", verificarSenhas);

            document.querySelector("form").addEventListener("submit", function (e) {
                if (senhaInput.value !== confirmaSenhaInput.value) {
                    e.preventDefault();
                    errorMessage.textContent = "As senhas não coincidem.";
                    errorMessage.style.display = "block";
                }
            });

            const cargoSelect = document.getElementById('cargo');
            const camposExtras = document.getElementById('camposExtras');
            const redirInput = document.getElementById('redir');
            const pisInput = document.getElementById('pis');
            const enderecoInput = document.getElementById('endereco');

            function atualizarCamposExtras() {
                if (cargoSelect.value === 'Funcionario' || cargoSelect.value === 'Gerente') {
                    camposExtras.style.display = '';
                    pisInput.required = true;
                    enderecoInput.required = true;
                    redirInput.value = '3';
                } else {
                    camposExtras.style.display = 'none';
                    pisInput.required = false;
                    enderecoInput.required = false;
                    redirInput.value = '2';
                }
            }

            cargoSelect.addEventListener('change', atualizarCamposExtras);
            atualizarCamposExtras(); // inicializa corretamente
        });
    </script>  -->
</body>
</html>