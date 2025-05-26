// Busca dinâmica de modelos para index.php

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.search-container .input');
    const searchBtn = document.querySelector('.search-container .btn-search');
    const cardsContainer = document.querySelector('.carrossel-container .cards-container');

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
});
