document.addEventListener("DOMContentLoaded", function () {
    const cepInput = document.getElementById("cep-input");

    cepInput.addEventListener("input", function (e) {
        let value = e.target.value.replace(/\D/g, ""); // Remove tudo que não for número

        if (value.length > 5) {
            value = value.slice(0, 5) + "-" + value.slice(5, 8); // Formata como 00000-000
        }

        e.target.value = value;
    });

    // Impede que o usuário digite letras
    cepInput.addEventListener("keypress", function (e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
});
