const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
const dropText = document.getElementById('drop-text');
const uploadIcon = document.getElementById('upload-icon');
const previewContainer = document.getElementById('preview-container');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    fileInput.files = e.dataTransfer.files;
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => {
    handleFiles(fileInput.files);
});

function handleFiles(files) {
    if (files.length > 0) {
        dropText.style.display = 'none';
        uploadIcon.style.display = 'none';
    }

    previewContainer.innerHTML = '';

    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const box = document.createElement('div');
                box.classList.add('preview-box');

                const img = document.createElement('img');
                img.src = e.target.result;

                const name = document.createElement('p');
                name.textContent = file.name;

                box.appendChild(img);
                box.appendChild(name);
                previewContainer.appendChild(box);
            };
            reader.readAsDataURL(file);
        }
    });
}
