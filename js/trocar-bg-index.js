let heroBg = document.querySelector('.hero-section');

setInterval(() => {
    heroBg.style.backgroundImage = "url(img/background-main/bg-com-luz.jpg)"
    
    setTimeout(() => {
        heroBg.style.backgroundImage = "url(img/background-main/bg.jpg)"
    }, 1000);
}, 2200);