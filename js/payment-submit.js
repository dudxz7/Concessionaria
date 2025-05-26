// Lógica do clique no botão de pagamento e submissão do formulário
// Depende de: validacao-payment.js

document.addEventListener('DOMContentLoaded', function() {
    const botaoPagamento = document.getElementById('botao-pagamento');
    if (!botaoPagamento) return;
    botaoPagamento.addEventListener('click', function() {
        const forma = document.querySelector('input[name="forma"]:checked');
        const total = document.body.getAttribute('data-total');
        const id = document.body.getAttribute('data-id');
        const cor = document.body.getAttribute('data-cor');
        // Validação dos campos obrigatórios
        const camposObrigatorios = ['nome', 'email', 'cpf', 'data_nasc', 'telefone'];
        let camposPreenchidos = true;
        camposObrigatorios.forEach(function(campo) {
            const el = document.getElementsByName(campo)[0];
            if (el && !el.value.trim()) {
                camposPreenchidos = false;
            }
        });
        aplicarClasseErroTodosInputs();
        if (!camposPreenchidos) {
            alert('Preencha todos os campos obrigatórios.');
            return;
        }
        if (!cor || cor.trim() === '') {
            alert('Selecione uma cor antes de prosseguir para o pagamento.');
            return;
        }
        if (forma && forma.id === 'pix') {
            fetch('../php/seta_pagamento_autorizado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id) + '&cor=' + encodeURIComponent(cor)
            })
            .then(response => response.text())
            .then(res => {
                if (res.trim() === 'ok') {
                    window.location.href = `../php/realizar_pagamento_pix.php?id=${encodeURIComponent(id)}&cor=${encodeURIComponent(cor)}`;
                } else {
                    alert('Erro ao iniciar pagamento Pix. Tente novamente.');
                }
            })
            .catch(() => {
                alert('Erro ao conectar com o servidor. Tente novamente.');
            });
        } else if (forma && forma.id === 'boleto') {
            alert('Pagamento por boleto ainda não implementado.');
        } else if (forma && forma.id === 'cartao') {
            if (!validarCamposCartao()) {
                alert('Preencha corretamente todos os campos do cartão.');
                return;
            }
            this.closest('form').submit();
        }
    });
});
