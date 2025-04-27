<?php
require_once('conexao.php'); // Conexão com o banco de dados

// Consulta para pegar os modelos de carros
$sql = "SELECT m.id, m.modelo, m.fabricante, m.cor, m.ano, m.preco, d.descricao, d.imagem
        FROM modelos m
        LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id";
$result = $conn->query($sql);

// Gerar os cards
if ($result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        // Ajustando o caminho da imagem
        $imagemPath = 'img/modelos/' . htmlspecialchars($carro['imagem']); // Caminho completo da imagem

        echo '<div class="card">
                <div class="favorite-icon">
                    <img src="img/coracao-nao-salvo.png" alt="Favoritar" class="heart-icon">
                </div>
                <img src="' . $imagemPath . '" alt="' . htmlspecialchars($carro['modelo']) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p><img src="img/calendario.png" alt="Ano"> ' . htmlspecialchars($carro['ano']) . ' <img src="img/painel-de-controle.png" alt="Km"> 0 Km</p>
                <div class="rating">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela.png" alt="estrela">
                    <img src="img/estrela-neutra.png" alt="estrela">
                    <span class="nota">(1.234)</span>
                </div>
                <h2>R$ ' . number_format($carro['preco'], 2, ',', '.') . '</h2>
                <button class="btn-send">Estou interessado</button>
            </div>';
    }
} else {
    echo "<p>Nenhum modelo encontrado.</p>";
}
?>
