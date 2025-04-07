<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro BMW - Admin</title>
    <link rel="stylesheet" href="../css/registro.css">
    <link rel="icon" href="../img/logoofcbmw.png">
</head>
<body>
    <div class="container">
            <a href="admin_dashboard.php" class="back-button">
                <img src="../img/seta-esquerda24.png" alt="Voltar">
            </a>
        <h2>Criar uma conta</h2>
        <form action="register.php" method="post">
            <div class="input-group">
                <label>Nome completo</label>
                <div class="input-wrapper">
                    <input type="text" name="nome" placeholder="Nome completo" required>
                    <img src="../img/usersemfundo.png" alt="Ícone usuário">
                </div>
            </div>
            
            <div class="input-group">
                <label>E-mail</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="E-mail" required>
                    <img src="../img/e-mail.png" alt="Ícone e-mail">
                </div>
            </div>

            <div class="input-group">
                <label>Cargo</label>
                <div class="input-wrapper">
                    <select name="cargo" id="cargo" required>
                        <option value="">Selecione o cargo</option>
                        <option value="Cliente">Cliente</option>
                        <option value="Funcionario">Funcionário</option>
                        <option value="Gerente">Gerente</option>
                    </select>
                    <img src="../img/cargo.png" alt="Ícone cargo">
                </div>
            </div>

            <div id="camposExtras" style="display: none;">
                <div class="input-group">
                    <label>PIS</label>
                    <div class="input-wrapper">
                        <input type="text" id="pis" name="pis" placeholder="Número do PIS" required maxlength="11">
                        <img src="../img/arquivo.png" alt="Ícone PIS">
                    </div>
                </div>

                <div class="input-group">
                    <label>Endereço</label>
                    <div class="input-wrapper">
                        <input type="text" name="endereco" placeholder="Endereço completo" required>
                        <img src="../img/lugar-colocar.png" alt="Ícone endereço">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="input-group">
                    <label>CPF</label>
                    <div class="input-wrapper">
                        <input type="text" name="cpf" placeholder="CPF" id="cpf" required>
                        <img src="../img/arquivo.png" alt="Ícone CPF">
                    </div>
                </div>
                <div class="input-group">
                    <label>RG</label>
                    <div class="input-wrapper">
                        <input type="text" name="rg" placeholder="RG" id="rg" required>
                        <img src="../img/cartaozin.png" alt="Ícone RG">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="input-group">
                    <label>Cidade</label>
                    <div class="input-wrapper">
                        <input type="text" name="cidade" placeholder="Cidade" id="cidade" required>
                        <img src="../img/construcao-da-cidade.png" alt="Ícone cidade">
                    </div>
                </div>
                <div class="input-group">
                    <label>Estado</label>
                    <div class="input-wrapper">
                        <input type="text" name="estado" placeholder="Estado Abreviado" id="estado" required maxlength="2">
                        <img src="../img/lugar-colocar.png" alt="Ícone estado">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="input-group">
                    <label>Telefone</label>
                    <div class="input-wrapper">
                        <input type="tel" name="telefone" placeholder="Telefone" id="telefone" required>
                        <img src="../img/telefone.png" alt="Ícone telefone">
                    </div>
                </div>
                <div class="input-group">
                    <label>CNH</label>
                    <div class="input-wrapper">
                        <input type="text" name="cnh" placeholder="CNH" id="cnh">
                        <img src="../img/carteira-de-motorista.png" alt="Ícone CNH">
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
                <img src="../img/verifica.png" alt="Ícone de check">
                Criar conta
            </button>
        </form>
    </div>

    <script src="../js/icon-de-olho-registro.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.7/inputmask.min.js"></script>
    <script src="../js/validacoes-registro.js"></script>

    <script>
    const cargoSelect = document.getElementById("cargo");
    const camposExtras = document.getElementById("camposExtras");

    // Mostra ou oculta os campos extras com base no cargo
    cargoSelect.addEventListener("change", function() {
        if (this.value === "Funcionario" || this.value === "Gerente") {
            camposExtras.style.display = "block";
        } else {
            camposExtras.style.display = "none";
        }
    });

    // Validação PIS
    const pisInput = document.getElementById("pis");
    const erroPis = document.getElementById("erroPis");

    pisInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, ''); // Remove tudo que não for número
        if (this.value.length === 11) {
            erroPis.style.display = "none";
        }
    });

    pisInput.addEventListener("blur", function () {
        if (this.value.length !== 11) {
            erroPis.style.display = "block";
        } else {
            erroPis.style.display = "none";
        }
    });
</script>
</body>
</html>
