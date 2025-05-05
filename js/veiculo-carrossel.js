// Obtém todos os elementos de miniaturas
const thumbnails = document.querySelectorAll('.thumb');

// Obtém a imagem principal
const mainImage = document.querySelector('.car-image');

// Função para trocar a imagem principal
thumbnails.forEach(thumb => {
    thumb.addEventListener('click', function() {
        // Remove a classe 'selected' de todas as miniaturas
        thumbnails.forEach(t => t.classList.remove('selected'));

        // Adiciona a classe 'selected' na miniatura clicada
        this.classList.add('selected');

        // Altera a imagem principal para a imagem clicada
        mainImage.src = this.src;
    });
});

// Navegação pelas setas
const prevButton = document.querySelector('.anchor-left');
const nextButton = document.querySelector('.anchor-right');

// Lógica para navegação
let currentIndex = 0;

const updateMainImage = () => {
  mainImage.src = thumbnails[currentIndex].src;
  // Remove a seleção de todas as miniaturas
  thumbnails.forEach(t => t.classList.remove('selected'));
  // Marca a miniatura correspondente à imagem principal
  thumbnails[currentIndex].classList.add('selected');
};

prevButton.addEventListener('click', () => {
  if (currentIndex > 0) {
    currentIndex--;
  } else {
    currentIndex = thumbnails.length - 1; // Volta para o último item
  }
  updateMainImage();
});

nextButton.addEventListener('click', () => {
  if (currentIndex < thumbnails.length - 1) {
    currentIndex++;
  } else {
    currentIndex = 0; // Vai para o primeiro item
  }
  updateMainImage();
});
