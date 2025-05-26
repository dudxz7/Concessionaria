// Busca dinâmica de modelos para index.php

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container .input');
    const searchBtn = document.querySelector('.search-container .btn-search');
    const cardsContainer = document.querySelector('.carrossel-container .cards-container');
    const cardsPromocoesContainer = document.querySelectorAll('.carrossel-container .cards-container')[1];

    function buscarModelos() {
        const termo = searchInput.value.trim();
        cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando...</p>';
        fetch(`php/card-veiculos.php?search=${encodeURIComponent(termo)}`)
            .then(res => res.text())
            .then(html => {
                cardsContainer.innerHTML = html;
            })
            .catch(() => {
                cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar modelos.</p>';
            });
    }

    function buscarPromocoes(formData) {
        if (!cardsPromocoesContainer) return;
        cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando promoções...</p>';
        fetch('php/card-promocoes.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.text())
            .then(html => {
                cardsPromocoesContainer.innerHTML = html;
            })
            .catch(() => {
                cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar promoções.</p>';
            });
    }

    searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        buscarModelos();
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarModelos();
        }
    });

    // Busca dinâmica de modelos para index.php

    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) {
        formFiltros.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(formFiltros);
            // Garante que ano_min e ano_max sempre vão, mesmo se vazios
            const anoMin = document.getElementById('filtro-ano-min')?.value || '';
            const anoMax = document.getElementById('filtro-ano-max')?.value || '';
            if (!formData.has('ano_min')) formData.append('ano_min', anoMin);
            else formData.set('ano_min', anoMin);
            if (!formData.has('ano_max')) formData.append('ano_max', anoMax);
            else formData.set('ano_max', anoMax);
            // Monta params para GET
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                params.append(key, value);
            }
            cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando...</p>';
            fetch(`php/card-veiculos.php?${params.toString()}`)
                .then(res => res.text())
                .then(html => {
                    cardsContainer.innerHTML = html;
                })
                .catch(() => {
                    cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar modelos.</p>';
                });
            if (cardsPromocoesContainer) {
                cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando promoções...</p>';
                fetch(`php/card-promocoes.php?${params.toString()}`)
                    .then(res => res.text())
                    .then(html => {
                        cardsPromocoesContainer.innerHTML = html;
                    })
                    .catch(() => {
                        cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar promoções.</p>';
                    });
            }
        });

        // Garante que qualquer alteração em qualquer filtro aplica todos os filtros juntos (interseção)
        const campos = formFiltros.querySelectorAll('input, select');
        campos.forEach(function(campo) {
            campo.addEventListener('change', function() {
                formFiltros.dispatchEvent(new Event('submit', { cancelable: true }));
            });
        });
    }

    const limparFiltrosBtn = document.getElementById('limpar-filtros');
    if (limparFiltrosBtn) {
        limparFiltrosBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Limpa todos os campos do formulário de filtros
            if (formFiltros) formFiltros.reset();
            // Busca todos os veículos SEM filtro e SEM cache
            cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando...</p>';
            fetch('php/card-veiculos.php?_rnd=' + Date.now())
                .then(res => res.text())
                .then(html => {
                    cardsContainer.innerHTML = html;
                })
                .catch(() => {
                    cardsContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar modelos.</p>';
                });
            // Busca promoções SEM filtro e SEM cache
            if (cardsPromocoesContainer) {
                cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Buscando promoções...</p>';
                fetch('php/card-promocoes.php?_rnd=' + Date.now(), { method: 'POST' })
                    .then(res => res.text())
                    .then(html => {
                        cardsPromocoesContainer.innerHTML = html;
                    })
                    .catch(() => {
                        cardsPromocoesContainer.innerHTML = '<p style="width:100%;text-align:center;font-size:1.2rem;padding:2em 0;">Erro ao buscar promoções.</p>';
                    });
            }
        });
    }
});
