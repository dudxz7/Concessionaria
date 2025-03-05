function togglePassword() {
    const passwordField = document.getElementById('senha');
    const eyeIcon = document.getElementById('eye-icon');

    // Verifica se o tipo do campo de senha é 'password' ou 'text'
    if (passwordField.type === 'password') {
        passwordField.type = 'text';  // Torna o campo visível
        eyeIcon.src = 'img/olhoaberto.png';  // Troca o ícone para o olho aberto
    } else {
        passwordField.type = 'password';  // Torna o campo invisível
        eyeIcon.src = 'img/olhofechado.png';  // Troca o ícone para o olho fechado
    }
}
