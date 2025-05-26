// Limpar filtros: reseta campos e recarrega todos os cards
const limparBtn = document.getElementById('limpar-filtros');
if (limparBtn) {
    limparBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // Tenta limpar todos os campos de filtro existentes, mas só se eles existirem
        const ids = [
            'filtro-ano', 'filtro-cor', 'filtro-preco',
            'filtro-ano-min', 'filtro-ano-max', 'filtro-preco-min', 'filtro-preco-max'
        ];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (el.type === 'checkbox') el.checked = false;
                else el.value = '';
            }
        });
        fetch('php/filtrar-veiculos.php', { method: 'POST' })
            .then(r => r.text())
            .then(html => {
                const cardsContainer = document.querySelector('.cards-container');
                if (cardsContainer) cardsContainer.innerHTML = html;
            });
    });
}
