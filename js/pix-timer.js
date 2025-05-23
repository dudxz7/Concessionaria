// pix-timer.js
(function() {
    // O backend deve definir window.PIX_EXPIRA_EM (timestamp unix)
    if (typeof window.PIX_EXPIRA_EM === 'undefined') return;
    var expiraEm = window.PIX_EXPIRA_EM;
    var agora = Math.floor(Date.now() / 1000);
    var tempoRestante = expiraEm - agora;
    var timerSpan = document.getElementById('pix-timer');
    function pad(n) { return n < 10 ? '0' + n : n; }
    function atualizarTimer() {
        if (tempoRestante <= 0) {
            timerSpan.textContent = '00:00';
            timerSpan.style.color = 'red';
            // Opcional: redirecionar ou mostrar mensagem de expiração
            return;
        }
        var min = Math.floor(tempoRestante / 60);
        var seg = tempoRestante % 60;
        timerSpan.textContent = pad(min) + ':' + pad(seg);
        tempoRestante--;
        setTimeout(atualizarTimer, 1000);
    }
    atualizarTimer();
})();
