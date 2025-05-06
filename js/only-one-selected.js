    document.querySelectorAll('.color-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (this.checked) {
                // Desmarcar todos os outros
                document.querySelectorAll('.color-checkbox').forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });
            }
        });
    });
