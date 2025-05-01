$(function() {
    let precoOriginal = {};
    $('#modelo_id option').each(function() {
        let id = $(this).val(),
            p = $(this).data('preco');
        if (id !== 'all') precoOriginal[id] = parseFloat(p);
    });

    const select = $('#modelo_id'),
        desconto = $('#desconto'),
        msg = $('#mensagem-desconto');

    function atualizar() {
        let ids = select.val() || [],
            d = parseFloat(desconto.val());
        let total = select.find('option').not('[value="all"]').length;
        if (!ids.length || isNaN(d)) {
            msg.text('');
            return;
        }
        if (ids.includes('all')) {
            select.val(Object.keys(precoOriginal)).trigger('change.select2');
            msg.html(`Aplicando ${d}% de desconto em <strong>todos os veículos</strong>.`);
        } else if (ids.length === 1) {
            let p = precoOriginal[ids[0]],
                pf = p - p * d / 100;
            msg.text('Valor final: R$ ' +
                pf.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                }));
        } else {
            msg.html(`Aplicando ${d}% de desconto em <strong>${ids.length} veículos</strong>.`);
        }
    }

    select.select2({
            placeholder: "Selecione os modelos",
            width: '100%',
            minimumResultsForSearch: Infinity // Remove barra de pesquisa
        })
        .on('change', atualizar)
        .on('select2:opening', function() {
            // Impede digitação no campo de busca interno
            setTimeout(() => {
                $('.select2-search__field').prop('readonly', true);
            }, 0);
        });

    // Impede remover opções selecionadas com o teclado
    $(document).on('keydown', '.select2-selection__choice__remove', function(e) {
        e.preventDefault();
    });

    desconto.on('input', atualizar);

    // Ao focar nos campos de data e hora, abrir o seletor automaticamente (para navegadores compatíveis)
    $('#data_limite_data, #data_limite_hora').on('focus', function() {
        if (this.showPicker) this.showPicker();
    });
});
