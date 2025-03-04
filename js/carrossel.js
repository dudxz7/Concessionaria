let currentIndex = 0;

function moveSlide(step) {
    const slides = document.querySelectorAll('.carousel-item');
    const totalSlides = slides.length;

    currentIndex += step;

    if (currentIndex < 0) {
        currentIndex = totalSlides - 1; // Volta para o último banner
    } else if (currentIndex >= totalSlides) {
        currentIndex = 0; // Volta para o primeiro banner
    }

    const carousel = document.querySelector('.carousel');
    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;

    updateDots();
}

// Para navegar diretamente pelos pontos
function currentSlide(index) {
    currentIndex = index;
    const carousel = document.querySelector('.carousel');
    carousel.style.transform = `translateX(-${currentIndex * 100}%)`;

    updateDots();
}

// Atualiza os pontos de navegação para o slide atual
function updateDots() {
    const dots = document.querySelectorAll('.dot');
    dots.forEach((dot, index) => {
        if (index === currentIndex) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// Inicializa o carrossel com intervalo de 4,5 segundos
setInterval(() => {
    moveSlide(1); // Move uma imagem a cada 4,5 segundos
}, 4500); // Intervalo de 4500ms (4.5 segundos)

// Atribuindo os eventos de clique às setas
document.querySelector('.prev').addEventListener('click', () => moveSlide(-1));
document.querySelector('.next').addEventListener('click', () => moveSlide(1));

// Atribuindo os eventos de clique aos pontos
const dots = document.querySelectorAll('.dot');
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => currentSlide(index));
});
