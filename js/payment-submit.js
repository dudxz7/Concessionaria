// Lógica do clique no botão de pagamento e submissão do formulário
// Depende de: validacao-payment.js

document.addEventListener('DOMContentLoaded', function() {
    const botaoPagamento = document.getElementById('botao-pagamento');
    if (!botaoPagamento) return;
    botaoPagamento.addEventListener('click', function() {
        const forma = document.querySelector('input[name="forma"]:checked');
        const total = document.body.getAttribute('data-total');
        const id = document.body.getAttribute('data-id');
        // Busca a cor selecionada pelo usuário, se houver input/select de cor visível e selecionado
        let cor = null;
        const corInput = document.querySelector('input[name="cor"]:checked, select[name="cor"]');
        if (corInput && corInput.value && corInput.value.trim() !== '') {
            cor = corInput.value.trim();
        } else {
            // Se não houver input/select, pega do data-cor do body
            cor = document.body.getAttribute('data-cor');
        }
        // Validação dos campos obrigatórios
        const camposObrigatorios = (forma && forma.id === 'cartao')
            ? ['numero_cartao', 'validade', 'cvv', 'nome_cartao']
            : ['nome', 'email', 'cpf', 'data_nasc', 'telefone'];
        let camposPreenchidos = true;
        camposObrigatorios.forEach(function(campo) {
            const el = document.getElementsByName(campo)[0];
            if (el && !el.disabled && el.offsetParent !== null && !el.value.trim()) {
                camposPreenchidos = false;
            }
        });
        aplicarClasseErroTodosInputs && aplicarClasseErroTodosInputs();
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
                if (res.trim().startsWith('ok')) {
                    window.location.href = `../php/realizar_pagamento_pix.php?id=${encodeURIComponent(id)}&cor=${encodeURIComponent(cor)}`;
                } else {
                    alert(res.trim());
                }
            })
            .catch(() => {
                alert('Erro ao conectar com o servidor. Tente novamente.');
            });
        } else if (forma && forma.id === 'boleto') {
            fetch('../php/seta_pagamento_autorizado_boleto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(id) + '&cor=' + encodeURIComponent(cor)
            })
            .then(response => response.text())
            .then(res => {
                if (res.trim() === 'ok') {
                    window.location.href = `../php/realizar_pagamento_boleto.php?id=${encodeURIComponent(id)}&cor=${encodeURIComponent(cor)}`;
                } else {
                    alert('Erro ao iniciar pagamento Boleto. Tente novamente.');
                }
            })
            .catch(() => {
                alert('Erro ao conectar com o servidor. Tente novamente.');
            });
        } else if (forma && forma.id === 'cartao') {
            // Validação local extra: impede envio se número do cartão for inválido ou bandeira não reconhecida
            const numeroCartao = document.getElementById('numero_cartao').value.trim();
            const nomeImpresso = document.getElementById('nome_cartao').value.trim();
            const bandeira = typeof detectarBandeira === 'function' ? detectarBandeira(numeroCartao) : null;
            if (!validarCamposCartao() || !bandeira || /^1+$/.test(numeroCartao) || numeroCartao.length < 12 || nomeImpresso.length < 3) {
                mostrarNotificacao && mostrarNotificacao('recusado');
                return;
            }
            // Cria o formData antes de usar
            const formData = new FormData();
            formData.append('cliente_id', document.body.getAttribute('data-cliente'));
            formData.append('veiculo_id', document.body.getAttribute('data-id'));
            formData.append('cor', cor);
            formData.append('nome_impresso', nomeImpresso);
            formData.append('numero_cartao_final', numeroCartao.slice(-4));
            formData.append('bandeira', bandeira);
            // Parcelamento
            const selectParcelamento = document.getElementById('parcelamento');
            const parcelas = parseInt(selectParcelamento.value, 10);
            const textoOpcao = selectParcelamento.options[selectParcelamento.selectedIndex].text;
            const matchValor = textoOpcao.match(/= R\$\s*([\d.,]+)/i);
            let valorFinal = total;
            if (matchValor && matchValor[1]) {
                valorFinal = matchValor[1].replace('.', '').replace(',', '.');
            }
            formData.append('parcelas', parcelas);
            formData.append('valor', valorFinal);
            formData.append('status', 'aprovado'); // ou 'recusado' se for o caso
            // Mostra notificação pendente imediatamente
            mostrarNotificacao && mostrarNotificacao('pendente');
            setTimeout(() => {
                fetch('../php/processar_pagamento_cartao.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        mostrarNotificacao && mostrarNotificacao('aprovado');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1800);
                    } else {
                        mostrarNotificacao && mostrarNotificacao('erro', null, res.erro || res.message || 'Erro ao processar pagamento.');
                    }
                })
                .catch(() => {
                    mostrarNotificacao && mostrarNotificacao('erro', null, 'Erro ao conectar com o servidor. Tente novamente.');
                });
            }, 600); // Delay para garantir que o pendente apareça
            return;
        }
    });
});
