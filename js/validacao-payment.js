// Validação de campos do formulário de pagamento

// Checa se o ano é bissexto
function isBissexto(ano) {
    return (ano % 4 === 0 && ano % 100 !== 0) || (ano % 400 === 0);
}

// Valida se o dia é válido para o mês/ano
function diaValidoParaMes(dia, mes, ano) {
    const diasNoMes = [31, (isBissexto(ano) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    return dia >= 1 && dia <= diasNoMes[mes - 1];
}

// Aplica/remover classe de erro em todos os inputs obrigatórios
function aplicarClasseErroTodosInputs() {
    const campos = [
        document.getElementById('nome'),
        document.getElementById('email'),
        document.getElementById('cpf'),
        document.getElementById('data_nasc'),
        document.getElementById('telefone')
    ];
    campos.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('input-erro');
        } else {
            input.classList.remove('input-erro');
        }
    });
}

// Validação extra de email
const emailInput = document.getElementById('email');
if (emailInput) {
    emailInput.addEventListener('blur', function() {
        const emailVal = emailInput.value.trim();
        const emailValido = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(emailVal);
        emailInput.classList.toggle('input-erro', !emailValido);
    });
}

// Validação extra de data de nascimento (formato DD/MM/AAAA, datas válidas, bissexto, mínimo 21/02/1875)
const dataInput = document.getElementById('data_nasc');
if (dataInput) {
    dataInput.addEventListener('blur', function() {
        const dataVal = dataInput.value.trim();
        let match = dataVal.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        let dataValida = false;
        if (match) {
            let dia = parseInt(match[1], 10);
            let mes = parseInt(match[2], 10);
            let ano = parseInt(match[3], 10);
            const data = new Date(ano, mes - 1, dia);
            const hoje = new Date();
            const minData = new Date(1875, 1, 21); // 21/02/1875
            dataValida = data.getFullYear() === ano && data.getMonth() === (mes - 1) && data.getDate() === dia && data < hoje && (data > minData || (ano === 1875 && mes === 2 && dia >= 21)) && diaValidoParaMes(dia, mes, ano);
        }
        dataInput.classList.toggle('input-erro', !dataValida);
    });
}

// Validação em tempo real dos campos obrigatórios
['nome','email','cpf','data_nasc','telefone'].forEach(function(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.addEventListener('input', function() {
        if (id === 'email') {
            const emailValido = /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(input.value.trim());
            input.classList.toggle('input-erro', !emailValido);
        } else if (id === 'data_nasc') {
            const dataVal = input.value.trim();
            let match = dataVal.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            let dataValida = false;
            if (match) {
                let dia = parseInt(match[1], 10);
                let mes = parseInt(match[2], 10);
                let ano = parseInt(match[3], 10);
                const data = new Date(ano, mes - 1, dia);
                const hoje = new Date();
                const minData = new Date(1875, 1, 21);
                dataValida = data.getFullYear() === ano && data.getMonth() === (mes - 1) && data.getDate() === dia && data < hoje && (data > minData || (ano === 1875 && mes === 2 && dia >= 21)) && diaValidoParaMes(dia, mes, ano);
            }
            input.classList.toggle('input-erro', !dataValida);
        } else if (id === 'cpf') {
            const cpfValido = validarCPFReal(input.value);
            input.classList.toggle('input-erro', !cpfValido);
        } else if (id === 'telefone') {
            const telLen = input.value.replace(/\D/g, '').length;
            const telValido = telLen === 10 || telLen === 11;
            input.classList.toggle('input-erro', !telValido);
        } else {
            input.classList.toggle('input-erro', !input.value.trim());
        }
    });
    input.addEventListener('blur', function() {
        input.dispatchEvent(new Event('input'));
    });
});

// Validação dos campos de cartão (número, validade, cvv)
function validarCamposCartao() {
    let valido = true;
    const numero = document.getElementById('numero_cartao');
    const numeroValido = numero.value.replace(/\D/g, '').length === 16;
    numero.classList.toggle('input-erro', !numeroValido);
    if (!numeroValido) valido = false;
    const validade = document.getElementById('validade');
    let validadeValida = false;
    const match = validade.value.match(/^(0[1-9]|1[0-2])\/(\d{2})$/);
    if (match) {
        const mes = parseInt(match[1], 10);
        const ano = 2000 + parseInt(match[2], 10);
        const hoje = new Date();
        const dataVal = new Date(ano, mes - 1, 1);
        validadeValida = dataVal >= new Date(hoje.getFullYear(), hoje.getMonth(), 1);
    }
    validade.classList.toggle('input-erro', !validadeValida);
    if (!validadeValida) valido = false;
    const cvv = document.getElementById('cvv');
    const cvvValido = /^\d{3,4}$/.test(cvv.value);
    cvv.classList.toggle('input-erro', !cvvValido);
    if (!cvvValido) valido = false;
    return valido;
}

['numero_cartao','validade','cvv'].forEach(function(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.addEventListener('input', validarCamposCartao);
    input.addEventListener('blur', validarCamposCartao);
});

// Validação real de CPF (cálculo dos dígitos verificadores)
function validarCPFReal(cpf) {
    cpf = cpf.replace(/\D/g, '');
    if (cpf.length !== 11) return false;
    // Bloqueia CPFs com todos os dígitos iguais
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    let soma = 0, resto;
    for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;
    soma = 0;
    for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) return false;
    return true;
}

// Exporta funções globais para uso em outros scripts
window.aplicarClasseErroTodosInputs = aplicarClasseErroTodosInputs;
window.validarCamposCartao = validarCamposCartao;
