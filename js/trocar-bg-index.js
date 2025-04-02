let heroBg = document.querySelector('.hero-section');

setInterval(() => {
    heroBg.style.backgroundImage = "url(img/bg-com-luz.jpg)"
    
    setTimeout(() => {
        heroBg.style.backgroundImage = "url(img/bg.jpg)"
    }, 1000);
}, 2200);