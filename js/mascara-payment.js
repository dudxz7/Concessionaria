document.addEventListener("DOMContentLoaded", function () {
    const cpfInput = document.getElementById("cpf");
    const dataInput = document.getElementById("data_nasc");
    const telefoneInput = document.getElementById("telefone");
    const form = document.querySelector("form");

    function aplicarMascaraCPF(valor) {
        let v = valor.replace(/\D/g, "").slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        return v;
    }

    function aplicarMascaraTelefone(valor) {
        let v = valor.replace(/\D/g, "").slice(0, 11);
        if (v.length <= 10) {
            v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
        } else {
            v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, "($1) $2-$3");
        }
        return v;
    }

    function aplicarMascaraData(valor) {
        let v = valor.replace(/\D/g, "").slice(0, 8);
        v = v.replace(/(\d{2})(\d)/, "$1/$2");
        v = v.replace(/(\d{2})(\d)/, "$1/$2");
        return v;
    }

    function validarData(input) {
        const dataStr = input.value;
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;

        if (!regex.test(dataStr)) {
            input.classList.add("input-erro");
            return;
        }

        const [, dia, mes, ano] = dataStr.match(regex).map(Number);
        const dataValida = new Date(ano, mes - 1, dia);
        const ehValida = (
            dataValida.getFullYear() === ano &&
            dataValida.getMonth() === mes - 1 &&
            dataValida.getDate() === dia
        );

        input.classList.toggle("input-erro", !ehValida);
    }

    function validarCPF(input) {
        const valido = input.value.replace(/\D/g, "").length === 11;
        input.classList.toggle("input-erro", !valido);
    }

    function validarTelefone(input) {
        const len = input.value.replace(/\D/g, "").length;
        const valido = len === 10 || len === 11;
        input.classList.toggle("input-erro", !valido);
    }

    // Aplicação de máscaras + validação em tempo real
    cpfInput.addEventListener("input", function () {
        cpfInput.value = aplicarMascaraCPF(cpfInput.value);
        validarCPF(cpfInput);
    });

    dataInput.addEventListener("input", function () {
        dataInput.value = aplicarMascaraData(dataInput.value);
        if (dataInput.value.length === 10) validarData(dataInput);
        else dataInput.classList.add("input-erro");
    });

    telefoneInput.addEventListener("input", function () {
        telefoneInput.value = aplicarMascaraTelefone(telefoneInput.value);
        validarTelefone(telefoneInput);
    });

    // Validação final ao enviar o formulário
    form.addEventListener("submit", function (e) {
        validarCPF(cpfInput);
        validarData(dataInput);
        validarTelefone(telefoneInput);

        const cpfValido = cpfInput.value.replace(/\D/g, "").length === 11;
        const dataValida = !dataInput.classList.contains("input-erro");
        const telLen = telefoneInput.value.replace(/\D/g, "").length;
        const telValido = telLen === 10 || telLen === 11;

        if (!cpfValido || !dataValida || !telValido) {
            e.preventDefault();
        }
    });
});
