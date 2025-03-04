document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("location-modal");
    const overlay = document.getElementById("overlay");
    const closeModal = document.getElementById("close-modal");
    const openLocationModal = document.getElementById("open-location-modal");
    const cepInput = document.getElementById("cep-input");
    const userLocation = document.getElementById("user-location");

    if (modal && overlay && closeModal && openLocationModal && cepInput && userLocation) {
        // Abre o modal ao clicar em "XXXX e Região"
        openLocationModal.addEventListener("click", function () {
            modal.style.display = "block";
            overlay.style.display = "block";
        });

        // Fecha o modal ao clicar no botão X
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
            overlay.style.display = "none";
        });

        // Fecha o modal ao clicar fora dele
        overlay.addEventListener("click", function () {
            modal.style.display = "none";
            overlay.style.display = "none";
        });

        // Atualiza a localização ao digitar o CEP e pressionar Enter
        cepInput.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                const cepValue = cepInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

                if (cepValue.length === 8) { // Verifica se o CEP tem 8 dígitos
                    buscarCapital(cepValue).then(capital => {
                        if (capital) {
                            userLocation.textContent = `${capital.nome} - ${capital.uf} e Região`; // Inclui "e Região"
                        } else {
                            userLocation.textContent = "Localização não encontrada";
                        }
                    });
                    modal.style.display = "none";
                    overlay.style.display = "none";
                }
            }
        });

        // Reseta o campo ao focar e remove o autocomplete
        cepInput.addEventListener("focus", function () {
            this.value = ""; // Reseta o campo ao focar
            this.setAttribute("autocomplete", "off"); // Tenta reforçar
        });

        // Atribui um nome dinâmico ao campo CEP
        const randomName = "cep-" + Math.random().toString(36).substring(2, 10);
        cepInput.setAttribute("name", randomName);
    }

    // Função para buscar a capital correspondente ao CEP
    async function buscarCapital(cep) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            if (data.uf) {
                const capitais = {
                    "AC": { nome: "Rio Branco", uf: "AC" },
                    "AL": { nome: "Maceió", uf: "AL" },
                    "AP": { nome: "Macapá", uf: "AP" },
                    "AM": { nome: "Manaus", uf: "AM" },
                    "BA": { nome: "Salvador", uf: "BA" },
                    "CE": { nome: "Fortaleza", uf: "CE" },
                    "DF": { nome: "Brasília", uf: "DF" },
                    "ES": { nome: "Vitória", uf: "ES" },
                    "GO": { nome: "Goiânia", uf: "GO" },
                    "MA": { nome: "São Luís", uf: "MA" },
                    "MT": { nome: "Cuiabá", uf: "MT" },
                    "MS": { nome: "Campo Grande", uf: "MS" },
                    "MG": { nome: "Belo Horizonte", uf: "MG" },
                    "PA": { nome: "Belém", uf: "PA" },
                    "PB": { nome: "João Pessoa", uf: "PB" },
                    "PR": { nome: "Curitiba", uf: "PR" },
                    "PE": { nome: "Recife", uf: "PE" },
                    "PI": { nome: "Teresina", uf: "PI" },
                    "RJ": { nome: "Rio de Janeiro", uf: "RJ" },
                    "RN": { nome: "Natal", uf: "RN" },
                    "RS": { nome: "Porto Alegre", uf: "RS" },
                    "RO": { nome: "Porto Velho", uf: "RO" },
                    "RR": { nome: "Boa Vista", uf: "RR" },
                    "SC": { nome: "Florianópolis", uf: "SC" },
                    "SP": { nome: "São Paulo", uf: "SP" },
                    "SE": { nome: "Aracaju", uf: "SE" },
                    "TO": { nome: "Palmas", uf: "TO" }
                };
                return capitais[data.uf] || null;
            }
            return null;
        } catch (error) {
            console.error("Erro ao buscar CEP:", error);
            return null;
        }
    }
});
