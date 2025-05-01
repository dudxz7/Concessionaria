document.addEventListener("DOMContentLoaded", function () {
    const selectModelo = document.getElementById("modelo_id");
    const inputDesconto = document.getElementById("desconto");
    const campoValorFinal = document.getElementById("valor-final");
    const inputPrecoOriginal = document.getElementById("preco_original");

    function atualizarPrecoFinal() {
        // Primeiro tenta o preco_original escondido (definido pelo PHP)
        let precoBase = parseFloat(inputPrecoOriginal.value || 0);

        // Se modelo mudou, pega o novo preco do <option>
        if (selectModelo.selectedOptions.length > 0) {
            const precoFromOption = parseFloat(selectModelo.selectedOptions[0].dataset.preco || 0);
            if (!isNaN(precoFromOption)) precoBase = precoFromOption;
        }

        const desconto = parseFloat(inputDesconto.value || 0);

        if (!isNaN(precoBase) && !isNaN(desconto)) {
            const precoComDesconto = precoBase * (1 - desconto / 100);
            campoValorFinal.textContent = "Valor final: R$ " + precoComDesconto.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        } else {
            campoValorFinal.textContent = "Valor final: R$ -";
        }
    }

    atualizarPrecoFinal();

    selectModelo.addEventListener("change", atualizarPrecoFinal);
    inputDesconto.addEventListener("input", atualizarPrecoFinal);
});
