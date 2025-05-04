<?php
require_once('conexao.php'); // Conexão com o banco de dados

// Função para gerar o ano de fabricação e o ano seguinte no formato "2024/2025"
function gerarAno($ano) {
    $anoAnterior = $ano - 1;
    return $anoAnterior . '/' . $ano;
}

// Função para gerar o rating (quantidade de estrelas)
function gerarRating() {
    $estrelasCheias = rand(3, 5);
    $estrelas = [];

    // Adiciona estrelas cheias
    for ($i = 0; $i < $estrelasCheias; $i++) {
        $estrelas[] = 'estrela.png';
    }

    // Se tiver espaço, chance de adicionar uma estrela metade
    if (count($estrelas) < 5 && rand(0, 1)) {
        $estrelas[] = 'estrela-metade.png';
    }

    // Completa com estrelas neutras
    while (count($estrelas) < 5) {
        $estrelas[] = 'estrela-neutra.png';
    }

    return $estrelas;
}

// Função para gerar a nota (número de avaliações)
function gerarNota() {
    return rand(1, 1500);
}

// Consulta para pegar os modelos de carros **sem promoções ativas**
$sql = "
  SELECT m.id, m.modelo, m.fabricante, m.cor, m.ano, m.preco, d.descricao, d.imagem
  FROM modelos m
  LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
  WHERE m.id NOT IN (
    SELECT modelo_id FROM promocoes
    WHERE status = 'Ativa' AND data_limite > NOW()
  )";

$result = $conn->query($sql);

// Gerar os cards
if ($result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        // Ajustando o caminho da imagem
        $imagemPath = 'img/modelos/' . htmlspecialchars($carro['imagem']); // Caminho completo da imagem

        // Gerar ano formatado
        $anoFormatado = gerarAno($carro['ano']);

        // Gerar rating aleatório
        $rating = gerarRating();

        // Gerar nota aleatória
        $nota = gerarNota();

        echo '<div class="card">
                <div class="favorite-icon">
                    <img src="img/coracoes/coracao-nao-salvo.png" alt="Favoritar" class="heart-icon" draggable="false">
                </div>
                <img src="' . $imagemPath . '" alt="' . htmlspecialchars($carro['modelo']) . '">
                <h2>' . htmlspecialchars($carro['modelo']) . '</h2>
                <p>' . htmlspecialchars($carro['descricao']) . '</p>
                <p><img src="img/cards/calendario.png" alt="Ano"> ' . $anoFormatado . ' <img src="img/cards/painel-de-controle.png" alt="Km"> 0 Km</p>
                <div class="rating">';

        // Exibir estrelas
        foreach ($rating as $estrela) {
            echo '<img src="img/cards/' . $estrela . '" alt="estrela">';
        }

        // Exibir nota aleatória
        echo '<span class="nota">(' . number_format($nota, 0, ',', '.') . ')</span>
                </div>
                <h2>R$ ' . number_format($carro['preco'], 2, ',', '.') . '</h2>
                <button class="btn-send">Estou interessado</button>
            </div>';
    }
} else {
    echo "<p>Nenhum modelo encontrado.</p>";
}
?>
