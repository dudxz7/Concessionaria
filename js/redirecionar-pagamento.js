document.addEventListener("DOMContentLoaded", () => {
    const botao = document.getElementById("botao-pagamento");

    botao.addEventListener("click", () => {
        const id = new URLSearchParams(window.location.search).get("id");
        const formaPix = document.getElementById("pix").checked;
        const formaBoleto = document.getElementById("boleto").checked;
        const formaCartao = document.getElementById("cartao").checked;

        if (!id) {
            alert("ID do modelo não encontrado.");
            return;
        }

        if (formaPix) {
            window.location.href = `realizar_pagamento_pix.php?id=${id}`;
        } else if (formaBoleto) {
            window.location.href = `realizar_pagamento_boleto.php?id=${id}`;
        } else if (formaCartao) {
            window.location.href = `realizar_pagamento_cartao.php?id=${id}`;
        } else {
            alert("Escolha uma forma de pagamento.");
        }
    });
});
