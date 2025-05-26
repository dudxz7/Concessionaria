// Modal de filtros de veículos
// Exibe/oculta modal e aplica filtros (apenas front-end, ajuste para back-end se necessário)
document.addEventListener('DOMContentLoaded', function() {
    const abrirModal = document.getElementById('abrir-modal-filtros');
    const modal = document.getElementById('modal-filtros');
    const fecharModal = document.querySelector('.fechar-modal-filtros');
    const formFiltros = document.getElementById('form-filtros');

    if (abrirModal && modal && fecharModal) {
        abrirModal.addEventListener('click', function() {
            modal.style.display = 'flex';
            setTimeout(() => { modal.style.opacity = '1'; }, 10);
        });
        fecharModal.addEventListener('click', function() {
            modal.style.opacity = '0';
            setTimeout(() => { modal.style.display = 'none'; }, 180);
        });
        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.opacity = '0';
                setTimeout(() => { modal.style.display = 'none'; }, 180);
            }
        });
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                modal.style.opacity = '0';
                setTimeout(() => { modal.style.display = 'none'; }, 180);
            }
        });
    }

    if (formFiltros) {
        formFiltros.addEventListener('submit', function(e) {
            e.preventDefault();
            const anoMin = document.getElementById('filtro-ano-min').value;
            const anoMax = document.getElementById('filtro-ano-max').value;
            const precoMin = document.getElementById('filtro-preco-min').value;
            const precoMax = document.getElementById('filtro-preco-max').value;
            const formData = new FormData(formFiltros);
            if (anoMin) formData.set('ano_min', anoMin);
            if (anoMax) formData.set('ano_max', anoMax);
            if (precoMin) formData.set('preco_min', precoMin);
            if (precoMax) formData.set('preco_max', precoMax);
            fetch('php/filtrar-veiculos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                const cardsContainer = document.querySelector('.cards-container');
                if (cardsContainer) {
                    cardsContainer.innerHTML = html;
                }
                modal.style.opacity = '0';
                setTimeout(() => { modal.style.display = 'none'; }, 180);
            })
            .catch(() => {
                alert('Erro ao filtrar veículos.');
                modal.style.opacity = '0';
                setTimeout(() => { modal.style.display = 'none'; }, 180);
            });
        });
    }
});
