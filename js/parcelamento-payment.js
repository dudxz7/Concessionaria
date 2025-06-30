// Formata o valor para o padrão brasileiro (R$ 2.000.000,00)
function formatarValorBR(valor) {
    return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Atualiza as opções de parcelamento conforme o valor do veículo
function atualizarParcelamento(valor) {
    const select = document.getElementById('parcelamento');
    if (!select) return;
    select.innerHTML = '';

    // Até 10x sem juros, 11x, 12x e 24x com juros
    const opcoes = [
        { qtd: 1, juros: 0 },
        { qtd: 2, juros: 0 },
        { qtd: 3, juros: 0 },
        { qtd: 4, juros: 0 },
        { qtd: 5, juros: 0 },
        { qtd: 6, juros: 0 },
        { qtd: 7, juros: 0 },
        { qtd: 8, juros: 0 },
        { qtd: 9, juros: 0 },
        { qtd: 10, juros: 0 },
        { qtd: 11, juros: 0.075 }, // Exemplo: 7.5% juros
        { qtd: 12, juros: 0.09 },  // Exemplo: 9% juros
        { qtd: 24, juros: 0.43 }   // Exemplo: 43% juros (ajuste conforme política)
    ];

    opcoes.forEach(op => {
        let total = valor;
        let texto = '';
        if (op.juros > 0) {
            total = valor * (1 + op.juros);
            texto = `${op.qtd} x R$ ${formatarValorBR(total / op.qtd)} = R$ ${formatarValorBR(total)} (com juros)`;
        } else {
            texto = `${op.qtd} x R$ ${formatarValorBR(valor / op.qtd)} = R$ ${formatarValorBR(valor)} (sem juros)`;
        }
        const option = document.createElement('option');
        option.value = op.qtd;
        option.textContent = texto;
        select.appendChild(option);
    });
}

// Detecta se é cartão e mostra o campo de parcelamento
function toggleParcelamento() {
    const campo = document.getElementById('campo-parcelamento');
    const cartao = document.getElementById('cartao');
    if (cartao && campo) {
        campo.style.display = cartao.checked ? 'block' : 'none';
    }
}

// Inicialização automática
window.addEventListener('DOMContentLoaded', function() {
    // Pega o valor do veículo do PHP
    const total = parseFloat(document.body.getAttribute('data-total'));
    if (!isNaN(total)) {
        atualizarParcelamento(total);
    }
    // Eventos para mostrar/esconder parcelamento
    document.querySelectorAll('input[name="forma"]').forEach(radio => {
        radio.addEventListener('change', toggleParcelamento);
    });
    toggleParcelamento();
});

// Permite atualizar parcelamento externamente
window.atualizarParcelamento = atualizarParcelamento;
