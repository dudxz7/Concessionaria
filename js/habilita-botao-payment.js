// Habilita o botão de pagamento apenas se todos os campos obrigatórios estiverem preenchidos corretamente
// Agora também exige os campos de cartão se a opção "Cartão" estiver selecionada

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const botao = document.querySelector('.botao');
    const radioCartao = document.getElementById('cartao');
    const requiredFields = [
        document.getElementById('nome'),
        document.getElementById('email'),
        document.getElementById('cpf'),
        document.getElementById('data_nasc'),
        document.getElementById('telefone')
    ];
    const camposCartao = document.getElementById('campos-cartao');
    const cartaoFields = [
        document.getElementById('numero_cartao'),
        document.getElementById('validade'),
        document.getElementById('cvv')
    ];

    function validarCampos() {
        let todosPreenchidos = requiredFields.every(input => input.value.trim() !== '');
        if (radioCartao.checked) {
            // Exige que os campos de cartão estejam preenchidos E válidos
            const numero = document.getElementById('numero_cartao').value.trim();
            const validade = document.getElementById('validade').value.trim();
            const cvv = document.getElementById('cvv').value.trim();
            // Validação extra: número 19 caracteres, validade MM/AA válida, CVV válido
            const bandeira = (typeof detectarBandeira === 'function') ? detectarBandeira(numero) : null;
            const numeroValido = numero.length === 19;
            const validadeValida = typeof validarValidade === 'function' ? validarValidade(validade) : /^\d{2}\/\d{2}$/.test(validade);
            const cvvValido = typeof validarCVV === 'function' ? validarCVV(cvv, bandeira) : /^\d{3,4}$/.test(cvv);
            todosPreenchidos = todosPreenchidos && numeroValido && validadeValida && cvvValido;
        }
        botao.disabled = !todosPreenchidos;
        botao.style.opacity = todosPreenchidos ? '1' : '0.5';
        botao.style.cursor = todosPreenchidos ? 'pointer' : 'not-allowed';
    }

    [...requiredFields, ...cartaoFields, radioCartao].forEach(input => {
        input.addEventListener('input', validarCampos);
        input.addEventListener('blur', validarCampos);
    });
    document.getElementById('pix').addEventListener('change', validarCampos);
    document.getElementById('boleto').addEventListener('change', validarCampos);

    // Inicializa estado do botão ao carregar
    validarCampos();
});
