document.addEventListener("DOMContentLoaded", function() {
    // Função para formatar o valor em formato monetário
    function formatarMoeda(valor) {
        return valor.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
    }

    // Atualizar o valor do preço com desconto
    function atualizarPreco() {
        const precoOriginal = parseFloat(document.getElementById('preco_original').value);
        let desconto = parseFloat(document.getElementById('desconto').value);

        // Garantir que o desconto não ultrapasse 100% e limite para 2 casas decimais
        desconto = Math.min(desconto, 100); // Limitar o valor do desconto a 100%
        desconto = desconto.toFixed(0); // Limitar a 2 casas decimais

        const precoComDesconto = precoOriginal - (precoOriginal * (desconto / 100));
        document.getElementById('valor-final').textContent = "Valor final: R$ " + formatarMoeda(precoComDesconto);
        document.getElementById('desconto').value = desconto; // Atualizar o campo de desconto com o valor formatado
    }

    // Chama a função de atualização do preço quando o desconto for alterado
    document.getElementById('desconto').addEventListener('input', atualizarPreco);

    // Atualizar o valor automaticamente quando o modelo for alterado
    document.getElementById('modelo_id').addEventListener('change', function() {
        const precoSelecionado = parseFloat(this.options[this.selectedIndex].getAttribute('data-preco'));
        document.getElementById('preco_original').value = precoSelecionado;
        atualizarPreco();
    });

    // Inicializar o valor quando a página carregar
    atualizarPreco();
});