document.addEventListener("DOMContentLoaded", function () {
    const inputImagem = document.getElementById("file-input");
    const previewImg = document.getElementById("preview");
    const uploadIcon = document.getElementById("upload-icon");
    const dropZone = document.getElementById("drop-zone");
    const fileNameDisplay = document.getElementById("file-name");

    // Função para exibir a imagem, esconder o ícone e mostrar o nome do arquivo
    function handleFile(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewImg.style.display = "block";
            if (uploadIcon) uploadIcon.style.display = "none";

            if (fileNameDisplay) {
                fileNameDisplay.textContent = file.name;
                fileNameDisplay.style.display = "block";
            }
        };
        reader.readAsDataURL(file);
    }

    // Evento de mudança (seleção manual)
    inputImagem.addEventListener("change", function () {
        if (this.files && this.files[0]) {
            handleFile(this.files[0]);
        }
    });

    // Eventos de drag & drop
    dropZone.addEventListener("dragover", function (e) {
        e.preventDefault();
        dropZone.classList.add("dragover");
    });

    dropZone.addEventListener("dragleave", function () {
        dropZone.classList.remove("dragover");
    });

    dropZone.addEventListener("drop", function (e) {
        e.preventDefault();
        dropZone.classList.remove("dragover");

        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith("image/")) {
            inputImagem.files = e.dataTransfer.files; // Atualiza o input
            handleFile(file);
        }
    });
});
