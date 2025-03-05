document.addEventListener("DOMContentLoaded", function () {
    const inputs = document.querySelectorAll(".campodoinput input");
    const eyeIcon = document.getElementById("eyeIcon");
    const senhaInput = document.getElementById("senha");
    const emailInput = document.getElementById("email");  // Referência ao campo de email

    // Validação dos inputs
    inputs.forEach(input => {
        input.addEventListener("blur", function () {
            validarCampo(this);
            if (input.id === "email") {
                autoCompletarEmail(input);  // Chama a função de auto-completar para o campo de email ao sair do campo
            }
        });

        input.addEventListener("input", function () {
            removerErro(this);
            if (input.id === "email") {
                autoCompletarEmail(input);  // Chama a função de auto-completar para o campo de email enquanto digita
            }
        });
    });

    function validarCampo(input) {
        const campodoinput = input.closest(".campodoinput");
        const mensagemErro = campodoinput.querySelector(".campo-obrigatorio");
        const iconeAlerta = campodoinput.querySelector(".icone-alerta");

        if (input.value.trim() === "") {
            campodoinput.classList.add("erro");
            mensagemErro.style.display = "block";
            iconeAlerta.style.display = "block";
        } else {
            campodoinput.classList.remove("erro");
            mensagemErro.style.display = "none";
            iconeAlerta.style.display = "none";
        }

        // Se for o campo de senha, mover o ícone do olho quando der erro
        if (input.id === "senha") {
            ajustarPosicaoOlho();
        }
    }

    function removerErro(input) {
        const campodoinput = input.closest(".campodoinput");
        campodoinput.classList.remove("erro");
        campodoinput.querySelector(".campo-obrigatorio").style.display = "none";
        campodoinput.querySelector(".icone-alerta").style.display = "none";

        // Se for o campo de senha, ajustar posição do olho
        if (input.id === "senha") {
            ajustarPosicaoOlho();
        }
    }

    // Função para mostrar/ocultar senha
    eyeIcon.addEventListener("click", function () {
        if (senhaInput.type === "password") {
            senhaInput.type = "text";
            eyeIcon.src = "img/olhoaberto.png"; // Ícone de olho aberto
        } else {
            senhaInput.type = "password";
            eyeIcon.src = "img/olhofechado.png"; // Ícone de olho fechado
        }
    });

    // Ajustar posição do olho quando o alerta aparece
    function ajustarPosicaoOlho() {
        if (document.querySelector("#senha").closest(".campodoinput").classList.contains("erro")) {
            eyeIcon.style.right = "40px"; // Move o ícone para a esquerda quando erro
        } else {
            eyeIcon.style.right = "10px"; // Posição normal quando não há erro
        }
    }

    // Função para auto-completar o campo de email com "@gmail.com"
    function autoCompletarEmail(input) {
        let value = input.value.trim();

        // Se o valor não contiver '@' e o campo não estiver vazio
        if (value && !value.includes('@')) {
            const cursorPosition = input.selectionStart; // Posição do cursor antes de inserir o domínio

            // Adiciona '@gmail.com' ao final
            input.value = value + '@gmail.com';

            // Restaura a posição do cursor para antes do domínio
            input.setSelectionRange(cursorPosition, cursorPosition); 
        }

        // Impede que o usuário digite depois do '@gmail.com' (Apaga o que for digitado após o domínio)
        if (value.includes('@gmail.com')) {
            const cursorPosition = input.selectionStart;

            // Se o cursor estiver além do "@gmail.com", corta a entrada
            if (cursorPosition > value.indexOf('@gmail.com') + '@gmail.com'.length) {
                input.value = value.slice(0, value.indexOf('@gmail.com') + '@gmail.com'.length); // Corta a entrada
                input.setSelectionRange(value.indexOf('@gmail.com') + '@gmail.com'.length, value.indexOf('@gmail.com') + '@gmail.com'.length); // Posiciona o cursor no final
            }
        }

        // Impede que o usuário apague o "@gmail.com"
        if (value.endsWith('@gmail.com')) {
            // Se o valor for menor que o "completo" do domínio, completa automaticamente
            if (value !== '@gmail.com') {
                input.value = value.slice(0, value.indexOf('@gmail.com') + '@gmail.com'.length);
            }

            // Garante que o cursor fique no final de @gmail.com
            const position = input.selectionStart;
            if (position > value.length) {
                input.setSelectionRange(value.length, value.length);
            }
        }
    }
});
