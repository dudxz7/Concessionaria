// Habilita o botão de pagamento apenas se todos os campos obrigatórios estiverem preenchidos corretamente
// Agora também exige os campos de cartão se a opção "Cartão" estiver selecionada

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const botao = document.querySelector('.botao');
    const radioCartao = document.getElementById('cartao');
    const requiredFields = [
        document.getElementById('nome'),
        document.getElementById('email'),
        document.getElementById('cpf'),
        document.getElementById('data_nasc'),
        document.getElementById('telefone')
    ];
    const camposCartao = document.getElementById('campos-cartao');
    const cartaoFields = [
        document.getElementById('numero_cartao'),
        document.getElementById('validade'),
        document.getElementById('cvv'),
        document.getElementById('nome_cartao')
    ];

    function validarCampos() {
        let todosPreenchidos = true;
        if (radioCartao.checked) {
            // Só exige campos do cartão
            todosPreenchidos = cartaoFields.every(input => input && !input.disabled && input.value.trim() !== '');
            // Validação extra: número 19 caracteres, validade MM/AA válida, CVV válido, nome impresso só letras
            const numero = document.getElementById('numero_cartao').value.trim();
            const validade = document.getElementById('validade').value.trim();
            const cvv = document.getElementById('cvv').value.trim();
            const nomeCartao = document.getElementById('nome_cartao').value.trim();
            // Nome impresso: só letras e espaços, pelo menos 2 palavras, sem números
            const nomeValido = /^[A-Za-zÀ-ÿ' ]{5,}$/.test(nomeCartao) && nomeCartao.split(' ').filter(w => w.length > 1).length >= 2 && !/\d/.test(nomeCartao);
            // Número cartão: 16 a 19 dígitos (com ou sem espaços)
            const numeroValido = /^\d{4} ?\d{4} ?\d{4} ?\d{4,7}$/.test(numero);
            // Validade: MM/AA e mês de 01 a 12, e ano >= ano atual
            let validadeValida = false;
            const validadeMatch = validade.match(/^((0[1-9])|(1[0-2]))\/(\d{2})$/);
            if (validadeMatch) {
                const mes = parseInt(validadeMatch[1], 10);
                const ano = parseInt(validadeMatch[4], 10);
                const dataAtual = new Date();
                const anoAtual = dataAtual.getFullYear() % 100; // dois últimos dígitos
                const mesAtual = dataAtual.getMonth() + 1;
                if (ano > anoAtual || (ano === anoAtual && mes >= mesAtual)) {
                    validadeValida = true;
                }
            }
            // Detecta bandeira do cartão
            let bandeira = null;
            if (typeof detectarBandeira === 'function') {
                bandeira = detectarBandeira(numero);
            }
            // CVV: Amex = 4 dígitos, outros = 3 dígitos
            let cvvValido = false;
            if (bandeira === 'amex') {
                cvvValido = /^\d{4}$/.test(cvv);
            } else {
                cvvValido = /^\d{3}$/.test(cvv);
            }
            todosPreenchidos = todosPreenchidos && numeroValido && validadeValida && cvvValido && nomeValido;
        } else {
            // Só exige campos pessoais, ignora campos de cartão
            todosPreenchidos = requiredFields.every(input => input && !input.disabled && input.value.trim() !== '');
            // Validação extra: data de nascimento precisa ter 10 caracteres, regex DD/MM/AAAA, data válida e maior de 18 anos
            const dataNasc = document.getElementById('data_nasc');
            let dataValida = false;
            if (dataNasc) {
                const val = dataNasc.value.trim();
                let match = val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (match) {
                    let dia = parseInt(match[1], 10);
                    let mes = parseInt(match[2], 10);
                    let ano = parseInt(match[3], 10);
                    const data = new Date(ano, mes - 1, dia);
                    const hoje = new Date();
                    const idade = hoje.getFullYear() - ano - (hoje.getMonth() + 1 < mes || (hoje.getMonth() + 1 === mes && hoje.getDate() < dia) ? 1 : 0);
                    dataValida = data.getFullYear() === ano && data.getMonth() === (mes - 1) && data.getDate() === dia && idade >= 18 && data < hoje;
                }
            }
            todosPreenchidos = todosPreenchidos && dataValida;
        }
        botao.disabled = !todosPreenchidos;
        botao.style.opacity = todosPreenchidos ? '1' : '0.5';
        botao.style.cursor = todosPreenchidos ? 'pointer' : 'not-allowed';
        // Garante que o botão nunca muda de tamanho
        botao.style.minWidth = '260px';
        botao.style.minHeight = '48px';
    }

    [...requiredFields, ...cartaoFields, radioCartao].forEach(input => {
        if (input) {
            input.addEventListener('input', validarCampos);
            input.addEventListener('blur', validarCampos);
        }
    });
    document.getElementById('pix').addEventListener('change', validarCampos);
    document.getElementById('boleto').addEventListener('change', validarCampos);

    // Impede digitação de números no campo Nome impresso no cartão
    const nomeCartaoInput = document.getElementById('nome_cartao');
    if (nomeCartaoInput) {
        nomeCartaoInput.addEventListener('keypress', function(e) {
            // Permite apenas letras, espaço, acentos e apóstrofo
            if (!/[A-Za-zÀ-ÿ' ]/.test(e.key)) {
                e.preventDefault();
            }
        });
        nomeCartaoInput.addEventListener('paste', function(e) {
            const texto = (e.clipboardData || window.clipboardData).getData('text');
            if (/\d/.test(texto)) {
                e.preventDefault();
            }
        });
    }

    // Inicializa estado do botão ao carregar
    validarCampos();
});
