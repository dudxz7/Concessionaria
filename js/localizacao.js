async function getUserLocation() {
    try {
        const response = await fetch("https://ipapi.co/json/");
        const data = await response.json();

        if (data.city && data.region_code) {
            document.getElementById("user-location").textContent = `${data.city} - ${data.region_code} e Região`;
        } else {
            document.getElementById("user-location").textContent = "Localização não encontrada";
        }
    } catch (error) {
        console.error("Erro ao obter localização:", error);
        document.getElementById("user-location").textContent = "Erro na localização";
    }
}

// Chamar a função quando a página carregar
document.addEventListener("DOMContentLoaded", getUserLocation);
