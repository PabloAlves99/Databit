<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análise de Resultados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="shortcut icon" href="media/icon.png" type="image/x-icon">
</head>

<body>

    <div class="container-fluid mt-4">
        <!-- Filtros -->
        <div class="card mb-4 shadow-sm">
            <img src="media/logo.png" alt="Logo" width="250px" style="padding: 15px;">
            <div class="card-body py-3">
                <?php require_once("models/filtros.php") ?>
            </div>

            <!-- Barra de Indicadores (KPIs) -->
            <div class="card mb-4 shadow-sm border-0">
                <?php require_once("models/kpis.php") ?>
            </div>
        </div>

        <!-- Grid Dinâmico (Tabela e 3 Gráficos) -->
        <div class="row" id="dashboard-grid">

            <!-- Tabela de Clientes (ID Corrigido) -->
            <div class="col-lg-6 mb-4 modulo-dashboard" id="card-tabela-clientes">
                <?php require_once("models/tabelaCli.php") ?>
            </div>
            
            <!-- Tabela de Técnicos -->
            <div class="col-lg-6 mb-4 modulo-dashboard" id="card-tabela">
                <?php require_once("models/tabelaTec.php") ?>
            </div>

            <!-- Gráfico 1 -->
            <div class="col-lg-6 mb-4 modulo-dashboard" id="card-tipo-os">
                <div class="card shadow-sm h-80 border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0 fw-bold text-secondary">Tipo de OS (Normal vs Aferição)</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-tipo-os"></div>
                    </div>
                </div>
            </div>

            <!-- Gráfico 2 -->
            <div class="col-lg-6 mb-4 modulo-dashboard" id="card-sla-mes">
                <div class="card shadow-sm h-80 border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0 fw-bold text-secondary">Ordens de serviço por mês e percentual de SLA excedido</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-sla-mes"></div>
                    </div>
                </div>
            </div>

            <!-- Gráfico 3 -->
            <div class="col-lg-6 mb-4 modulo-dashboard" id="card-os-dia">
                <div class="card shadow-sm h-80 border-0">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0 fw-bold text-secondary">Ordens de serviço por dia</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-os-dia"></div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bloco de Scripts Organizados -->
    <script src="js/globals.js"></script>
    <script src="js/kpis.js"></script>
    <script src="js/tabela.js"></script>
    <script src="js/graficos.js"></script>
    <script src="js/filtros.js"></script>
    <script src="js/app.js"></script>
    <script src="js/layout.js"></script>
</body>

</html>