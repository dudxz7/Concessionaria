<?php
require_once('conexao.php'); // Conexão com o banco de dados

// --- Guardas para não redeclarar funções ---
if (!function_exists('gerarAno')) {
    function gerarAno(int $ano): string {
        return ($ano - 1) . '/' . $ano;
    }
}

if (!function_exists('gerarRating')) {
    function gerarRating(): array {
        $estrelasCheias = rand(3, 5);
        $estrelas = [];
        for ($i = 0; $i < $estrelasCheias; $i++) {
            $estrelas[] = 'estrela.png';
        }
        if (count($estrelas) < 5 && rand(0, 1)) {
            $estrelas[] = 'estrela-metade.png';
        }
        while (count($estrelas) < 5) {
            $estrelas[] = 'estrela-neutra.png';
        }
        return $estrelas;
    }
}

if (!function_exists('gerarNota')) {
    function gerarNota(): int {
        return rand(1, 1500);
    }
}

// --- Consulta só veículos em promoção ativa e não expirados (data_limite) ---
$sql = "
  SELECT 
    m.id,
    m.modelo,
    m.fabricante,
    m.cor,
    m.ano,
    m.preco           AS preco_original,
    d.descricao,
    d.imagem,
    p.desconto,
    p.preco_com_desconto,
    p.data_limite
  FROM modelos m
  LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
  LEFT JOIN promocoes      p ON m.id = p.modelo_id
  WHERE p.ativo = 1
    AND p.data_limite > CURDATE()
";
$result = $conn->query($sql);

// --- Loop de exibição ---
if ($result && $result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        // Caminho da imagem
        $imagemPath = 'img/modelos/' . htmlspecialchars($carro['imagem']);
        // Ano formatado
        $anoFormatado = gerarAno((int)$carro['ano']);
        // Rating e nota
        $rating = gerarRating();
        $nota   = gerarNota();
        // Desconto e preços
        $desconto         = (float)$carro['desconto'];
        $precoOriginal    = (float)$carro['preco_original'];
        $precoComDesconto = (float)$carro['preco_com_desconto'];
        // Tempo restante (dias entre hoje e data_limite)
        $dataLimite = new DateTime($carro['data_limite']);
        $intervalo  = (new DateTime())->diff($dataLimite);
        $diasRest   = $intervalo->days . ' dias';

        echo '<div class="card">
                <div class="tempo-restante-wrapper">
                  <div class="tempo-restante">
                    <img src="img/relogio-branco.png" class="icon-tempo" alt="Tempo">
                    <div class="tempo-texto">
                      <span>Tempo restante</span>
                      <div class="dias">'. $diasRest .'</div>
                    </div>
                  </div>
                </div>

                <div class="favorite-icon">
                  <img src="img/coracao-nao-salvo.png" class="heart-icon" alt="Favoritar" draggable="false">
                </div>

                <img src="'. $imagemPath .'" alt="'. htmlspecialchars($carro['modelo']) .'">
                <h2>'. htmlspecialchars($carro['modelo']) .'</h2>
                <p>'. htmlspecialchars($carro['descricao']) .'</p>
                <p>
                  <img src="img/calendario.png" alt="Ano"> '. $anoFormatado .'
                  <img src="img/painel-de-controle.png" alt="Km"> 0 Km
                </p>

                <div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="img/'. $estrela .'" alt="estrela">';
        }
        echo '<span class="nota">('. $nota .')</span>
                </div>

                <div class="preco-promocao">
                  <h2 class="preco-antigo">R$ '. number_format($precoOriginal, 2, ',', '.') .'</h2>
                  <div class="preco-novo">
                    <h2>R$ '. number_format($precoComDesconto, 2, ',', '.') .'</h2>
                    <span class="desconto">-'. $desconto .'%</span>
                  </div>
                </div>

                <button class="btn-send">Estou interessado</button>
            </div>';
    }
} else {
    echo '<p>Nenhum veículo em promoção encontrado.</p>';
}
?>
