// Ajuste automático de dia/mês/ano para limites válidos no campo data de nascimento
// Usa as funções isBissexto e diaValidoParaMes do validacao-payment.js

function ajustarAnoDataNascimento() {
    const input = document.getElementById('data_nasc');
    let val = input.value.trim();
    let match = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (match) {
        let dia = parseInt(match[1], 10);
        let mes = parseInt(match[2], 10);
        let ano = parseInt(match[3], 10);
        if (mes > 12) mes = 12;
        if (ano > 2025) ano = 2025;
        // Corrige dia para o máximo do mês/ano
        let maxDia = 31;
        if (mes >= 1 && mes <= 12) {
            maxDia = [31, (isBissexto(ano) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][mes - 1];
        }
        if (dia > maxDia) dia = maxDia;
        // Garante sempre 2 dígitos para dia e mês
        let diaStr = dia.toString().padStart(2, '0');
        let mesStr = mes.toString().padStart(2, '0');
        input.value = `${diaStr}/${mesStr}/${ano}`;
    }
}

const dataNascInput = document.getElementById('data_nasc');
if (dataNascInput) {
    dataNascInput.addEventListener('input', ajustarAnoDataNascimento);
    dataNascInput.addEventListener('blur', ajustarAnoDataNascimento);
}
