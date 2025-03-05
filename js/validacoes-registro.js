document.addEventListener("DOMContentLoaded", function() {
    // Máscara CPF
    const cpfInput = document.getElementById('cpf');
    cpfInput.addEventListener('input', function(event) {
        let inputValue = cpfInput.value.replace(/\D/g, '');  // Remove caracteres não numéricos

        if (inputValue.length > 11) {
            inputValue = inputValue.slice(0, 11);  // Limita a 11 caracteres
        }

        if (inputValue.length <= 3) {
            cpfInput.value = inputValue;
        } else if (inputValue.length <= 6) {
            cpfInput.value = inputValue.slice(0, 3) + '.' + inputValue.slice(3);
        } else if (inputValue.length <= 9) {
            cpfInput.value = inputValue.slice(0, 3) + '.' + inputValue.slice(3, 6) + '.' + inputValue.slice(6);
        } else if (inputValue.length <= 11) {
            cpfInput.value = inputValue.slice(0, 3) + '.' + inputValue.slice(3, 6) + '.' + inputValue.slice(6, 9) + '-' + inputValue.slice(9, 11);
        }
    });

    // Máscara RG do Ceará (10 dígitos + hífen + 1 número verificador)
    const rgInput = document.getElementById('rg');
    rgInput.addEventListener('input', function(event) {
        let inputValue = rgInput.value.replace(/\D/g, '');  // Remove caracteres não numéricos

        if (inputValue.length > 11) {
            inputValue = inputValue.slice(0, 11);  // Limita a 11 caracteres
        }

        if (inputValue.length <= 10) {
            rgInput.value = inputValue;
        } else if (inputValue.length === 11) {
            rgInput.value = inputValue.slice(0, 10) + '-' + inputValue.slice(10, 11);
        }
    });

    // Máscara Telefone
    const telefoneInput = document.getElementById('telefone');
    telefoneInput.addEventListener('input', function(event) {
        let telefoneValue = telefoneInput.value.replace(/\D/g, ''); // Remove não numéricos

        if (telefoneValue.length > 11) {
            telefoneValue = telefoneValue.slice(0, 11);  // Limita a 11 caracteres
        }

        if (telefoneValue.length <= 2) {
            telefoneInput.value = telefoneValue;
        } else if (telefoneValue.length <= 6) {
            telefoneInput.value = `(${telefoneValue.slice(0, 2)}) ${telefoneValue.slice(2)}`;
        } else if (telefoneValue.length <= 10) {
            telefoneInput.value = `(${telefoneValue.slice(0, 2)}) ${telefoneValue.slice(2, 7)}-${telefoneValue.slice(7)}`;
        } else if (telefoneValue.length <= 11) {
            telefoneInput.value = `(${telefoneValue.slice(0, 2)}) ${telefoneValue.slice(2, 7)}-${telefoneValue.slice(7, 11)}`;
        }
    });

    // Máscara CNH (somente números)
    const cnhInput = document.getElementById('cnh');
    cnhInput.addEventListener('input', function(event) {
        let inputValue = cnhInput.value.replace(/\D/g, '');  // Remove caracteres não numéricos

        if (inputValue.length > 11) {
            inputValue = inputValue.slice(0, 11);  // Limita a 11 caracteres
        }

        cnhInput.value = inputValue;  // Atualiza o valor do input com a máscara aplicada
    });

    // Permitir apenas letras para os campos Cidade e Estado
    const cidadeInput = document.querySelector('input[name="cidade"]');
    const estadoInput = document.querySelector('input[name="estado"]');

    // Função para permitir somente letras
    function allowOnlyLetters(event) {
        const regex = /[^a-zA-Z\s]/g;
        event.target.value = event.target.value.replace(regex, '');  // Remove qualquer coisa que não seja letra ou espaço
    }

    cidadeInput.addEventListener('input', allowOnlyLetters);
    estadoInput.addEventListener('input', allowOnlyLetters);

    // Garantir que os valores de Cidade e Estado fiquem sempre em maiúsculo
    cidadeInput.addEventListener('input', function() {
        cidadeInput.value = cidadeInput.value.toUpperCase(); // Converte para maiúsculo
    });

    estadoInput.addEventListener('input', function() {
        estadoInput.value = estadoInput.value.toUpperCase(); // Converte para maiúsculo
    });

    // Validando Senhas
    const senhaInput = document.getElementById("senha");
    const confirmaSenhaInput = document.getElementById("confirmaSenha");
    const errorMessage = document.createElement("p");
    errorMessage.id = "error-message";
    errorMessage.style.color = "red";
    errorMessage.style.fontSize = "14px";
    document.querySelector('form').appendChild(errorMessage);

    // Quando o formulário for enviado, verifica as senhas
    document.querySelector("form").addEventListener("submit", function(event) {
        if (senhaInput.value !== confirmaSenhaInput.value) {
            event.preventDefault(); // Impede o envio do formulário
            errorMessage.textContent = "As senhas não coincidem."; // Exibe a mensagem de erro
        } else {
            errorMessage.textContent = ""; // Limpa a mensagem de erro
        }
    });
});
