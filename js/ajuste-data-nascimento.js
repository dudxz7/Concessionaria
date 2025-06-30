// Ajuste automático de dia/mês/ano para limites válidos no campo data de nascimento
// Usa as funções isBissexto e diaValidoParaMes do validacao-payment.js

function ajustarAnoDataNascimento() {
    const input = document.getElementById('data_nasc');
    let val = input.value.trim();
    // Só aceita formato DD/MM/AAAA
    let match = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) {
        input.classList.add('input-erro');
        return;
    }
    let dia = parseInt(match[1], 10);
    let mes = parseInt(match[2], 10);
    let ano = parseInt(match[3], 10);
    // Verifica se a data existe
    const data = new Date(ano, mes - 1, dia);
    const hoje = new Date();
    const idade = hoje.getFullYear() - ano - (hoje.getMonth() + 1 < mes || (hoje.getMonth() + 1 === mes && hoje.getDate() < dia) ? 1 : 0);
    const dataValida = data.getFullYear() === ano && data.getMonth() === (mes - 1) && data.getDate() === dia && idade >= 18 && data < hoje;
    if (!dataValida) {
        input.classList.add('input-erro');
        return;
    } else {
        input.classList.remove('input-erro');
    }
    // Não corrige mais datas absurdas, só aceita se for válida e maior de 18 anos
}

const dataNascInput = document.getElementById('data_nasc');
if (dataNascInput) {
    dataNascInput.addEventListener('input', ajustarAnoDataNascimento);
    dataNascInput.addEventListener('blur', ajustarAnoDataNascimento);
}
