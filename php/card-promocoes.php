<?php
require_once('conexao.php'); // Conexão com o banco de dados

// --- Funções utilitárias ---
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

// --- Consulta veículos com promoções ativas e ainda válidas ---
$sql = "
    SELECT 
        m.id,
        m.modelo,
        m.fabricante,
        m.cor,
        m.ano,
        m.preco AS preco_original,
        d.descricao,
        d.imagem,
        p.desconto,
        p.preco_com_desconto,
        p.data_limite
    FROM modelos m
    LEFT JOIN detalhes_modelos d ON m.id = d.modelo_id
    LEFT JOIN promocoes p ON m.id = p.modelo_id
    WHERE p.status = 'Ativa' AND p.data_limite > NOW()
";
$result = $conn->query($sql);

// --- Exibição dos cards ---
if ($result && $result->num_rows > 0) {
    while ($carro = $result->fetch_assoc()) {
        $imagemPath = 'img/modelos/' . htmlspecialchars($carro['imagem']);
        $anoFormatado = gerarAno((int)$carro['ano']);
        $rating = gerarRating();
        $nota = gerarNota();

        $desconto = (float)$carro['desconto'];
        $precoOriginal = (float)$carro['preco_original'];
        $precoComDesconto = (float)$carro['preco_com_desconto'];

        // --- Tempo restante (preciso) ---
        $dataAtual = new DateTime();
        $dataLimite = new DateTime($carro['data_limite']);
        $intervalo = $dataAtual->diff($dataLimite);

        if ($intervalo->days > 1) {
            $diasRest = $intervalo->days . ' dias';
        } elseif ($intervalo->days === 1) {
            $diasRest = '1 dia';
        } elseif ($intervalo->h >= 1) {
            $diasRest = $intervalo->h . ' hora' . ($intervalo->h > 1 ? 's' : '');
        } elseif ($intervalo->i >= 1) {
            $diasRest = $intervalo->i . ' minuto' . ($intervalo->i > 1 ? 's' : '');
        } else {
            $diasRest = 'menos de 1 minuto';
        }

        // --- Exibindo o card com a promoção ---
        echo '<div class="card">
                <div class="tempo-restante-wrapper">
                    <div class="tempo-restante">
                        <img src="img/cards/relogio-branco.png" class="icon-tempo" alt="Tempo">
                        <div class="tempo-texto">
                            <span>Tempo restante</span>
                            <div class="dias">'. $diasRest .'</div>
                        </div>
                    </div>
                </div>

                <div class="favorite-icon">
                    <img src="img/coracoes/coracao-nao-salvo.png" class="heart-icon" alt="Favoritar" draggable="false">
                </div>

                <img src="'. $imagemPath .'" alt="'. htmlspecialchars($carro['modelo']) .'">
                <h2>'. htmlspecialchars($carro['modelo']) .'</h2>
                <p>'. htmlspecialchars($carro['descricao']) .'</p>
                <p>
                    <img src="img/cards/calendario.png" alt="Ano"> '. $anoFormatado .'
                    <img src="img/cards/painel-de-controle.png" alt="Km"> 0 Km
                </p>

                <div class="rating">';
        foreach ($rating as $estrela) {
            echo '<img src="img/cards/'. $estrela .'" alt="estrela">';
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

                <a href="php/pagina_veiculo.php?id='. $carro['id'] .'" class="btn-link">
                    <button class="btn-send">Estou interessado</button>
                </a>
            </div>';
    }
} else {
    echo '<p>Nenhum veículo em promoção encontrado.</p>';
}
?>
