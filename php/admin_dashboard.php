<?php
session_start();

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['usuarioLogado']) || $_SESSION['usuarioAdmin'] !== 1) {
    // Redireciona para o index ou login se não for admin
    header("Location: ../index.php");
    exit();
}

// Recupera dados da sessão
$nome_completo = $_SESSION['nome_completo'] ?? 'Administrador';
$email = $_SESSION['email'] ?? 'admin@gmail.com';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link rel="icon" href="../img/admin_colored.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap">
    <style>
        .content {
            flex: 1;
            padding: 40px 32px 32px 32px;
            min-width: 0;
            overflow-y: auto;
            max-height: 100vh;
            box-sizing: border-box;
        }
        .dashboard-header {
            background: linear-gradient(90deg, #003366 60%, #0072ce 100%);
            color: #fff;
            padding: 32px 0 24px 0;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-radius: 12px;
        }
        .dashboard-header h1 {
            margin: 0 0 8px 0;
            font-size: 2.1rem;
            letter-spacing: 1px;
        }
        .dashboard-header p {
            margin: 0;
            font-size: 1.05rem;
            opacity: 0.9;
        }
        .dashboard-summary {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin: 32px auto 0 auto;
            max-width: 900px;
        }
        .summary-box {
            background: #003366;
            color: #fff;
            border-radius: 12px;
            padding: 18px 32px;
            min-width: 140px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .summary-box h4 {
            margin: 0 0 6px 0;
            font-size: 1.05rem;
            font-weight: 500;
        }
        .summary-box span {
            font-size: 1.3rem;
            font-weight: 700;
        }
        .dashboard-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            margin: 40px auto 0 auto;
            max-width: 1100px;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 32px 28px 24px 28px;
            min-width: 220px;
            max-width: 260px;
            flex: 1 1 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.18s, box-shadow 0.18s;
            cursor: pointer;
            text-decoration: none;
            color: #003366;
        }
        .dashboard-card:hover {
            transform: translateY(-6px) scale(1.04);
            box-shadow: 0 6px 24px rgba(0,114,206,0.13);
            background: #f0f6ff;
        }
        .dashboard-card img {
            width: 54px;
            height: 54px;
            margin-bottom: 18px;
        }
        .dashboard-card h3 {
            margin: 0 0 8px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .dashboard-card p {
            margin: 0;
            font-size: 0.98rem;
            color: #444;
            opacity: 0.85;
        }
        .dashboard-indicators {
            display: flex;
            gap: 32px;
            justify-content: center;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .dashboard-graphs {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            margin: 40px 0 40px 0;
        }
        .dashboard-graphs > div {
            min-width: 280px;
            max-width: 360px;
            flex: 1 1 280px;
        }
        @media (max-width: 900px) {
            .dashboard-cards { flex-direction: column; align-items: center; }
            .dashboard-summary { flex-direction: column; align-items: center; gap: 18px; }
            .dashboard-graphs { flex-direction: column; align-items: center; }
            .dashboard-graphs > div { max-width: 98vw; min-width: 0; }
        }
    </style>
</head>
<body>
    <div class="container" style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <div>
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="content">
            <a href="../index.php" class="back-button" title="Voltar para a loja">
                <img src="../img/seta-esquerdabranca.png" alt="Voltar" style="width: 28px; height: 28px;">
            </a>
            <div class="dashboard-header" style="display:none;"></div>
            <!-- Indicadores rápidos -->
            <div class="dashboard-indicators" style="display: flex; gap: 32px; justify-content: center; margin-bottom: 32px; flex-wrap: wrap;">
                <div style="background:#fff; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:22px 32px; min-width:180px; display:flex; align-items:center; gap:16px;">
                    <img src="../img/novo-usuario.png" alt="Usuários" style="width:38px;">
                    <div><div style="font-size:1.1rem; color:#003366;">Usuários</div><div style="font-size:1.5rem; font-weight:700;">120</div></div>
                </div>
                <div style="background:#fff; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:22px 32px; min-width:180px; display:flex; align-items:center; gap:16px;">
                    <img src="../img/credit-card-2-29.png" alt="Faturamento" style="width:38px;">
                    <div><div style="font-size:1.1rem; color:#003366;">Faturamento</div><div style="font-size:1.5rem; font-weight:700;">R$ 1.200.000</div></div>
                </div>
                <div style="background:#fff; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:22px 32px; min-width:180px; display:flex; align-items:center; gap:16px;">
                    <img src="../img/carro_de_frente.png" alt="Modelos" style="width:38px;">
                    <div><div style="font-size:1.1rem; color:#003366;">Modelos</div><div style="font-size:1.5rem; font-weight:700;">18</div></div>
                </div>
                <div style="background:#fff; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:22px 32px; min-width:180px; display:flex; align-items:center; gap:16px;">
                    <img src="../img/homem-de-negocios.png" alt="Clientes" style="width:38px;">
                    <div><div style="font-size:1.1rem; color:#003366;">Clientes</div><div style="font-size:1.5rem; font-weight:700;">350</div></div>
                </div>
            </div>
            <!-- Gráficos -->
            <div class="dashboard-graphs" style="display: flex; flex-wrap: wrap; gap: 32px; justify-content: center; margin: 40px 0 40px 0;">
                <div style="background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px 32px; min-width: 340px; max-width: 480px; flex: 1 1 340px;">
                    <h3 style="text-align:center; color:#003366; margin-bottom: 18px;">Vendas por Mês</h3>
                    <canvas id="vendasBarChart" width="400" height="220"></canvas>
                </div>
                <div style="background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px 32px; min-width: 340px; max-width: 480px; flex: 1 1 340px;">
                    <h3 style="text-align:center; color:#003366; margin-bottom: 18px;">Proporção de Modelos Vendidos</h3>
                    <canvas id="modelosPieChart" width="400" height="220"></canvas>
                </div>
                <div style="background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px 32px; min-width: 340px; max-width: 480px; flex: 1 1 340px;">
                    <h3 style="text-align:center; color:#003366; margin-bottom: 18px;">Evolução de Vendas</h3>
                    <canvas id="vendasLineChart" width="400" height="220"></canvas>
                </div>
                <div style="background: #fff; border-radius: 18px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 24px 32px; min-width: 340px; max-width: 480px; flex: 1 1 340px;">
                    <h3 style="text-align:center; color:#003366; margin-bottom: 18px;">Status do Estoque</h3>
                    <canvas id="estoqueDoughnutChart" width="400" height="220"></canvas>
                </div>
            </div>
            <!-- Tabela de últimas vendas -->
            <div style="background:#fff; border-radius:14px; box-shadow:0 2px 8px rgba(0,0,0,0.07); margin:40px auto 0 auto; max-width:900px; padding:24px 32px;">
                <h3 style="color:#003366; margin-bottom:18px;">Últimas Vendas</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f0f6ff; color:#003366;">
                            <th style="padding:10px 6px; text-align:left;">Data</th>
                            <th style="padding:10px 6px; text-align:left;">Cliente</th>
                            <th style="padding:10px 6px; text-align:left;">Modelo</th>
                            <th style="padding:10px 6px; text-align:left;">Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>28/06/2025</td><td>João Silva</td><td>BMW X1</td><td>R$ 210.000</td></tr>
                        <tr><td>27/06/2025</td><td>Maria Souza</td><td>BMW Série 3</td><td>R$ 250.000</td></tr>
                        <tr><td>26/06/2025</td><td>Carlos Lima</td><td>BMW X3</td><td>R$ 230.000</td></tr>
                        <tr><td>25/06/2025</td><td>Fernanda Dias</td><td>BMW X5</td><td>R$ 320.000</td></tr>
                    </tbody>
                </table>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
            // Gráfico de Barras - Vendas por Mês (exemplo)
            const ctxBar = document.getElementById('vendasBarChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Vendas',
                        data: [120, 150, 180, 90, 200, 170, 210],
                        backgroundColor: '#0072ce',
                        borderRadius: 8,
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: '#e0e0e0' } }
                    }
                }
            });
            // Gráfico de Pizza - Proporção de Modelos Vendidos (exemplo)
            const ctxPie = document.getElementById('modelosPieChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['BMW X1', 'BMW X3', 'BMW Série 3', 'BMW X5'],
                    datasets: [{
                        data: [30, 25, 20, 25],
                        backgroundColor: ['#003366', '#0072ce', '#b0bec5', '#e0e0e0'],
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: false }
                    }
                }
            });
            // Gráfico de Linha - Evolução de Vendas
            const ctxLine = document.getElementById('vendasLineChart').getContext('2d');
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Vendas',
                        data: [100, 120, 140, 160, 180, 200, 220],
                        borderColor: '#003366',
                        backgroundColor: 'rgba(0,51,102,0.08)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                        pointBackgroundColor: '#0072ce',
                    }]
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        title: { display: false }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: '#e0e0e0' } }
                    }
                }
            });
            // Gráfico Doughnut - Status do Estoque
            const ctxDoughnut = document.getElementById('estoqueDoughnutChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Disponível', 'Reservado', 'Vendido'],
                    datasets: [{
                        data: [60, 25, 15],
                        backgroundColor: ['#0072ce', '#b0bec5', '#003366'],
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' },
                        title: { display: false }
                    }
                }
            });
            </script>
        </div>
    </div>
</body>
</html>
