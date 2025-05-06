$(document).ready(function() {
    // Quando o ícone do coração for clicado
    $('.heart-icon').click(function(e) {
        e.preventDefault(); // Impede o comportamento padrão do link (que recarregaria a página)
        
        var modeloId = $(this).data('id');
        var usuarioId = $(this).data('usuario');
        
        // Envia a requisição AJAX para favoritar/desfavoritar
        var $heartIcon = $(this).find('img'); // Pega a imagem dentro do ícone do coração

        $.ajax({
            url: 'php/favoritar.php',
            type: 'GET',
            data: { modelo_id: modeloId },
            success: function(response) {
                if (response === 'adicionar') {
                    // Mudar o ícone para favoritado
                    $heartIcon.attr('src', 'img/coracoes/coracao-salvo.png');
                } else if (response === 'remover') {
                    // Mudar o ícone para não favoritado
                    $heartIcon.attr('src', 'img/coracoes/coracao-nao-salvo.png');
                }
            },
            error: function() {
                alert('Erro ao favoritar/desfavoritar.');
            }
        });
    });
});
