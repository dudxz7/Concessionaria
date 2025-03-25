// Função para bloquear a digitação de números e transformar em maiúsculas
function bloquearNumeros(event) {
    const key = event.key;
    if (/\d/.test(key)) {
        event.preventDefault();
    }
}

// Função para transformar o texto digitado em maiúsculas
function transformarMaiusculas(event) {
    event.target.value = event.target.value.toUpperCase();
}

// Adiciona os eventos nos campos de Estado e Cidade
document.getElementById("estado").addEventListener("keydown", bloquearNumeros);
document.getElementById("cidade").addEventListener("keydown", bloquearNumeros);
document.getElementById("estado").addEventListener("input", transformarMaiusculas);

// Monitorando os campos de estado e cidade para habilitar o botão
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('#estado, #cidade, #endereco');
    const salvarBtn = document.querySelector('.salvar-btn');

    // Função para verificar se algum campo foi alterado
    function verificarCampos() {
        let algumCampoAlterado = false;

        // Verificando se algum dos campos de estado ou cidade foi alterado
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                algumCampoAlterado = true;
            }
        });

        // Se algum campo foi alterado, ativa o botão
        if (algumCampoAlterado) {
            salvarBtn.classList.add('enabled');
            salvarBtn.disabled = false;
        } else {
            salvarBtn.classList.remove('enabled');
            salvarBtn.disabled = true;
        }
    }

    // Verifica os campos sempre que o usuário digitar algo
    inputs.forEach(input => {
        input.addEventListener('input', verificarCampos);
    });
});