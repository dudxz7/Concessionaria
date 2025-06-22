// Atualiza o data-id do botão 'Compre agora' ao trocar a cor
// Requer que o botão tenha id 'btn-comprar' e cada checkbox de cor tenha classe 'color-checkbox'

document.querySelectorAll('.color-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            const cor = this.value;
            const modeloId = document.getElementById('btn-comprar').getAttribute('data-modelo-id');
            fetch('php/busca_veiculo_disponivel.php?modelo_id=' + encodeURIComponent(modeloId) + '&cor=' + encodeURIComponent(cor))
                .then(resp => resp.json())
                .then(data => {
                    if (data && data.veiculo_id) {
                        document.getElementById('btn-comprar').setAttribute('data-id', data.veiculo_id);
                    } else {
                        document.getElementById('btn-comprar').setAttribute('data-id', '');
                    }
                });
        }
    });
});
