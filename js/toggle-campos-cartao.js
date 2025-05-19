// Exibe os campos de cartão apenas quando a opção "Cartão" está selecionada
// Habilita/desabilita inputs de cartão conforme necessário

document.addEventListener('DOMContentLoaded', function () {
    const radioCartao = document.getElementById('cartao');
    const radioPix = document.getElementById('pix');
    const radioBoleto = document.getElementById('boleto');
    const camposCartao = document.getElementById('campos-cartao');
    const inputsCartao = camposCartao.querySelectorAll('input');
    const numeroCartao = document.getElementById('numero_cartao');
    const validade = document.getElementById('validade');
    const cvv = document.getElementById('cvv');
    const selectParcelamento = document.getElementById('parcelamento');
    const bandeiraCartao = document.getElementById('bandeira-cartao');

    // Exibe/oculta o campo de parcelamento junto com os campos de cartão
    function atualizarCamposCartao() {
        if (radioCartao.checked) {
            camposCartao.style.display = 'block';
            document.getElementById('campo-parcelamento').style.display = 'flex';
            inputsCartao.forEach(input => input.disabled = false);
        } else {
            camposCartao.style.display = 'none';
            document.getElementById('campo-parcelamento').style.display = 'none';
            inputsCartao.forEach(input => {
                input.value = '';
                input.disabled = true;
            });
        }
    }

    // Máscara para número do cartão (formato: 0000 0000 0000 0000)
    numeroCartao.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '');
        value = value.slice(0, 16);
        value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
        this.value = value;
    });

    // Máscara para validade (formato: MM/AA)
    validade.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 4) value = value.slice(0, 4);
        if (value.length > 2) value = value.replace(/(\d{2})(\d{1,2})/, '$1/$2');
        this.value = value;
    });

    // Máscara para CVV (apenas números, até 4 dígitos)
    cvv.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '');
        this.value = value.slice(0, 4);
    });

    // Validação de validade e CVV conforme a bandeira
    function validarValidade(val) {
        // Aceita apenas MM/AA, mês 01-12, ano >= atual
        if (!/^\d{2}\/\d{2}$/.test(val)) return false;
        const [mes, ano] = val.split('/').map(Number);
        if (mes < 1 || mes > 12) return false;
        // Ano mínimo: ano atual (só 2 dígitos)
        const anoAtual = new Date().getFullYear() % 100;
        return ano >= anoAtual;
    }

    function validarCVV(cvv, bandeira) {
        if (!/^\d+$/.test(cvv)) return false;
        if (bandeira === 'amex') return cvv.length === 4;
        if (bandeira === 'visa' || bandeira === 'mastercard' || bandeira === 'hipercard' || bandeira === 'elo') return cvv.length === 3;
        return false;
    }

    // Feedback visual para validade e cvv
    function feedbackCampo(input, valido) {
        input.style.borderColor = valido ? '#28a745' : '#dc3545';
    }

    validade.addEventListener('input', function () {
        feedbackCampo(validade, validarValidade(validade.value));
    });
    cvv.addEventListener('input', function () {
        const bandeira = detectarBandeira(numeroCartao.value);
        feedbackCampo(cvv, validarCVV(cvv.value, bandeira));
    });

    function atualizarParcelamento() {
        const bandeira = detectarBandeira(numeroCartao.value);
        const todosPreenchidos = numeroCartao.value.trim().length === 19 &&
            validarValidade(validade.value) &&
            validarCVV(cvv.value, bandeira);
        selectParcelamento.disabled = !todosPreenchidos;
        selectParcelamento.style.opacity = todosPreenchidos ? '1' : '0.5';
        selectParcelamento.style.cursor = todosPreenchidos ? 'pointer' : 'not-allowed';
    }

    // Detecta a bandeira do cartão e troca a imagem ao digitar
    function detectarBandeira(numero) {
        numero = numero.replace(/\D/g, '');
        if (/^4/.test(numero)) return 'visa';
        if (/^(5[1-5]|2[2-7])/.test(numero)) return 'mastercard';
        if (/^3[47]/.test(numero)) return 'amex';
        if (/^(606282|3841)/.test(numero)) return 'hipercard';
        if (/^(4011|4312|4389|4514|4576|5041|5066|5067|5090|6277|6362|6363|6504|6505|6509|6516|6550)/.test(numero)) return 'elo';
        return 'default';
    }

    function atualizarBandeira() {
        const bandeira = detectarBandeira(numeroCartao.value);
        let src = '';
        if (bandeira === 'visa') src = '../img/formas-de-pagamento/visa.png';
        if (bandeira === 'mastercard') src = '../img/formas-de-pagamento/mastercard.png';
        if (bandeira === 'amex') src = '../img/formas-de-pagamento/amex.png';
        if (bandeira === 'hipercard') src = '../img/formas-de-pagamento/hipercard.png';
        if (bandeira === 'elo') src = '../img/formas-de-pagamento/elo.png';
        if (src) {
            bandeiraCartao.src = src;
            bandeiraCartao.alt = 'Bandeira do cartão';
            bandeiraCartao.style.display = 'block';
        } else {
            bandeiraCartao.removeAttribute('src');
            bandeiraCartao.alt = '';
            bandeiraCartao.style.display = 'none';
        }
    }

    [radioCartao, radioPix, radioBoleto].forEach(radio => {
        radio.addEventListener('change', atualizarCamposCartao);
    });

    [numeroCartao, validade, cvv].forEach(input => {
        input.addEventListener('input', atualizarParcelamento);
        input.addEventListener('blur', atualizarParcelamento);
    });

    // Sempre que mudar a forma de pagamento, revalida
    radioCartao.addEventListener('change', atualizarParcelamento);
    document.getElementById('pix').addEventListener('change', atualizarParcelamento);
    document.getElementById('boleto').addEventListener('change', atualizarParcelamento);

    // Esconde a imagem da bandeira ao focar ou limpar o campo
    numeroCartao.addEventListener('focus', function () {
        bandeiraCartao.style.visibility = 'hidden';
        numeroCartao.style.paddingLeft = '12px'; // volta o texto para a esquerda
    });
    numeroCartao.addEventListener('blur', function () {
        if (numeroCartao.value.replace(/\D/g, '').length > 0) {
            bandeiraCartao.style.visibility = 'visible';
        } else {
            bandeiraCartao.style.visibility = 'hidden';
            numeroCartao.style.paddingLeft = '12px';
        }
    });
    // Mostra a imagem só quando digitar e só se for uma bandeira reconhecida
    numeroCartao.addEventListener('input', function () {
        const bandeira = detectarBandeira(numeroCartao.value);
        if (numeroCartao.value.replace(/\D/g, '').length > 0 && bandeira !== 'default') {
            bandeiraCartao.style.visibility = 'visible';
            numeroCartao.style.paddingLeft = '60px';
        } else {
            bandeiraCartao.style.visibility = 'hidden';
            numeroCartao.style.paddingLeft = '12px';
        }
        atualizarBandeira();
    });
    // Inicializa invisível e sem src
    bandeiraCartao.style.visibility = 'hidden';
    bandeiraCartao.style.display = 'none';
    bandeiraCartao.removeAttribute('src');
    numeroCartao.style.paddingLeft = '12px';

    // Atualiza a função de atualizarBandeira para esconder completamente a imagem se não houver bandeira
    function atualizarBandeira() {
        const bandeira = detectarBandeira(numeroCartao.value);
        let src = '';
        if (bandeira === 'visa') src = '../img/formas-de-pagamento/visa.png';
        if (bandeira === 'mastercard') src = '../img/formas-de-pagamento/mastercard.png';
        if (bandeira === 'amex') src = '../img/formas-de-pagamento/amex.png';
        if (bandeira === 'hipercard') src = '../img/formas-de-pagamento/hipercard.png';
        if (bandeira === 'elo') src = '../img/formas-de-pagamento/elo.png';
        if (src) {
            bandeiraCartao.src = src;
            bandeiraCartao.alt = 'Bandeira do cartão';
            bandeiraCartao.style.display = 'block';
        } else {
            bandeiraCartao.removeAttribute('src');
            bandeiraCartao.alt = '';
            bandeiraCartao.style.display = 'none';
        }
    }

    // Inicializa estado ao carregar
    atualizarCamposCartao();
    atualizarParcelamento();

    // Impede digitar números no campo nome completo
    const nomeInput = document.getElementById('nome');
    nomeInput.addEventListener('input', function () {
        this.value = this.value.replace(/\d+/g, '');
    });
});
