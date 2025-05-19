document.getElementById("btn-upload").addEventListener("click", () => {
  document.getElementById("file-input").click();
});

const fileInput = document.getElementById("file-input");
const container = document.getElementById("preview-container");
const ordemCampos = document.getElementById("ordem-campos");
const fileNamesSpan = document.getElementById("file-names");
const form = document.querySelector("form");

let allFiles = [];

fileInput.addEventListener("change", () => {
  // Adiciona novos arquivos sem remover os anteriores
  const newFiles = Array.from(fileInput.files);
  // Evita arquivos duplicados pelo nome e tamanho
  newFiles.forEach(f => {
    if (!allFiles.some(existing => existing.name === f.name && existing.size === f.size)) {
      allFiles.push(f);
    }
  });
  updatePreviews();
  // Limpa o input para permitir selecionar o mesmo arquivo novamente
  fileInput.value = "";
});

function updatePreviews() {
  container.innerHTML = "";
  ordemCampos.innerHTML = "";

  if (allFiles.length === 0) {
    fileNamesSpan.textContent = "Nenhum arquivo selecionado";
  } else if (allFiles.length === 1) {
    fileNamesSpan.textContent = allFiles[0].name;
  } else {
    fileNamesSpan.textContent = allFiles.length + " arquivos selecionados";
  }

  allFiles.forEach((file, index) => {
    const reader = new FileReader();
    const box = document.createElement("div");
    box.classList.add("preview-box");
    box.setAttribute("draggable", true);
    box.dataset.index = index;

    reader.onload = function (e) {
      box.innerHTML = `<img src="${e.target.result}" alt=""><p>${file.name}</p>`;
      container.appendChild(box);
      updateOrderFields();
    };
    reader.readAsDataURL(file);

    box.addEventListener("dragstart", () => {
      box.classList.add("dragging");
    });

    box.addEventListener("dragend", () => {
      box.classList.remove("dragging");
      updateOrderFields();
    });
  });
}

container.addEventListener("dragover", function (e) {
  e.preventDefault();
  const afterElement = getDragAfterElement(container, e.clientX);
  const dragging = document.querySelector(".dragging");
  if (!dragging) return;
  if (afterElement == null) {
    container.appendChild(dragging);
  } else {
    container.insertBefore(dragging, afterElement);
  }
});

function getDragAfterElement(container, x) {
  const elements = [...container.querySelectorAll(".preview-box:not(.dragging)")];
  return elements.reduce(
    (closest, child) => {
      const box = child.getBoundingClientRect();
      const offset = x - box.left - box.width / 2;
      if (offset < 0 && offset > closest.offset) {
        return { offset, element: child };
      } else {
        return closest;
      }
    },
    { offset: Number.NEGATIVE_INFINITY }
  ).element;
}

function updateOrderFields() {
  ordemCampos.innerHTML = "";
  const boxes = container.querySelectorAll(".preview-box");
  boxes.forEach((box) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "ordem_imagens[]";
    input.value = box.dataset.index;
    ordemCampos.appendChild(input);
  });
}

// Remove arquivo ao clicar com botão direito
container.addEventListener("contextmenu", function(e) {
  e.preventDefault();
  const box = e.target.closest(".preview-box");
  if (box) {
    const idx = Number(box.dataset.index);
    allFiles.splice(idx, 1);
    updatePreviews();
  }
});

// *** AQUI A MÁGICA PARA REORDENAR OS ARQUIVOS DO INPUT FILE ANTES DE ENVIAR ***
form.addEventListener("submit", (e) => {
  e.preventDefault();

  const orderIndexes = Array.from(ordemCampos.querySelectorAll("input"))
    .map(input => Number(input.value));

  const dt = new DataTransfer();
  orderIndexes.forEach(idx => {
    if (allFiles[idx]) {
      dt.items.add(allFiles[idx]);
    }
  });

  // Substitui os arquivos no input file pela nova ordem
  fileInput.files = dt.files;

  // Agora envia o form normalmente
  form.submit();
});
