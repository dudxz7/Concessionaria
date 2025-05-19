        document.querySelectorAll('input[type="text"], input[type="email"], input[type="date"]').forEach(input => {
            input.addEventListener('blur', function () {
                if (this.value.trim() === '') {
                    this.classList.add('input-vazio');
                } else {
                    this.classList.remove('input-vazio');
                }
            });

            // Remover borda azul ao focar de novo
            input.addEventListener('focus', function () {
                this.classList.remove('input-vazio');
            });
        });