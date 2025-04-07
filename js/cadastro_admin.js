const cargoSelect = document.getElementById("cargo");
const camposExtras = document.getElementById("camposExtras");

// Mostra ou oculta os campos extras com base no cargo
cargoSelect.addEventListener("change", function() {
    if (this.value === "Funcionario" || this.value === "Gerente") {
        camposExtras.style.display = "block";
    } else {
        camposExtras.style.display = "none";
    }
});

// Validação PIS
const pisInput = document.getElementById("pis");
const erroPis = document.getElementById("erroPis");

pisInput.addEventListener("input", function () {
    this.value = this.value.replace(/\D/g, ''); // Remove tudo que não for número
    if (this.value.length === 11) {
        erroPis.style.display = "none";
    }
});

pisInput.addEventListener("blur", function () {
    if (this.value.length !== 11) {
        erroPis.style.display = "block";
    } else {
        erroPis.style.display = "none";
    }
});