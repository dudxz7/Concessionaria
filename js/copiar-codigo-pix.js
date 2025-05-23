// JS para copiar o código Pix ao clicar no botão

document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.copiar-btn');
    const input = document.getElementById('codigoPix');
    if (btn && input) {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            try {
                await navigator.clipboard.writeText(input.value);
                btn.innerHTML = '<img src="../img/copia.png" alt="Copiar" /> Copiado!';
                setTimeout(() => {
                    btn.innerHTML = '<img src="../img/copia.png" alt="Copiar" /> Copiar';
                }, 1500);
            } catch (err) {
                alert('Erro ao copiar o código Pix.');
            }
        });
    }
});
