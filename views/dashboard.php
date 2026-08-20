<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';

// Conexión a la base de datos
require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::conectar();

// --- INICIALIZAR VARIABLES ---
$countActivas = 0;
$countLotes = 0;
$countAlertas = 0;
$gananciasMes = 0;
$perdidasMes = 0;
$tasaDefectos = 0;
$totalInspeccionesMes = 0;

$historicoDashboard = [];
$dataCalidad = [];
$dataProduccion = [];
$dataFinancieraMensual = [];
$dataProductos = [];

// --- BLOQUE 1: MÉTRICAS PRINCIPALES Y KPI (SEGURAS) ---
try {
    $stmtActivas = $db->query("SELECT COUNT(*) FROM ordenproduccion WHERE estado = 'Activa'");
    $countActivas = $stmtActivas->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtLotes = $db->query("SELECT COUNT(l.idLote) FROM lote l JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden WHERE o.estado = 'Activa'");
    $countLotes = $stmtLotes->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtAlertas = $db->query("SELECT COUNT(*) FROM registroinspeccion WHERE resultado = 'Rechazado'");
    $countAlertas = $stmtAlertas->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtGanancias = $db->query("SELECT SUM(impactoFinancieroNeto) FROM historico_produccion WHERE MONTH(fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(fechaCierre) = YEAR(CURRENT_DATE())");
    $gananciasMes = $stmtGanancias->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtPerdidas = $db->query("SELECT SUM(unidadesDefectuosas) FROM historico_produccion WHERE MONTH(fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(fechaCierre) = YEAR(CURRENT_DATE())");
    $totalDefectuosasMes = $stmtPerdidas->fetchColumn() ?: 0;
    $perdidasMes = $totalDefectuosasMes * 50000; 
} catch (Exception $e) {}

// --- BLOQUE 2: MÉTRICAS DE CALIDAD EN TIEMPO REAL ---
try {
    $stmtScrap = $db->query("SELECT (SUM(unidadesDefectuosas) / NULLIF(SUM(cantidadPlanificada), 0)) * 100 FROM historico_produccion WHERE MONTH(fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(fechaCierre) = YEAR(CURRENT_DATE())");
    $tasaDefectos = round($stmtScrap->fetchColumn() ?: 0, 1);
} catch (Exception $e) { $tasaDefectos = 0; }

try {
    $stmtInspMes = $db->query("SELECT COUNT(*) FROM registroinspeccion");
    $totalInspeccionesMes = $stmtInspMes->fetchColumn() ?: 0;
} catch (Exception $e) { $totalInspeccionesMes = 0; }

// --- BLOQUE 3: TABLA Y DATOS PARA GRÁFICAS ---
try {
    $stmtTabla = $db->query("SELECT h.*, l.idLote 
                FROM historico_produccion h 
                LEFT JOIN lote l ON h.idOrden = l.FK_ordenId 
                ORDER BY h.idHistorico DESC LIMIT 10");
    $historicoDashboard = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtCal = $db->query("SELECT resultado, COUNT(*) as total FROM registroinspeccion GROUP BY resultado");
    $dataCalidad = $stmtCal->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtProd = $db->query("SELECT DATE_FORMAT(fechaCierre, '%b %Y') as mes, SUM(unidadesCorrectas) as total 
                FROM historico_produccion GROUP BY YEAR(fechaCierre), MONTH(fechaCierre) ORDER BY MAX(fechaCierre) ASC LIMIT 6");
    $dataProduccion = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtFin = $db->query("SELECT DATE_FORMAT(fechaCierre, '%b %Y') as mes, SUM(impactoFinancieroNeto) as ganancias 
                FROM historico_produccion GROUP BY YEAR(fechaCierre), MONTH(fechaCierre) ORDER BY MAX(fechaCierre) ASC LIMIT 6");
    $dataFinancieraMensual = $stmtFin->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtProdR = $db->query("SELECT productoNombre, SUM(impactoFinancieroNeto) as totalGanancia, SUM(unidadesCorrectas) as correctas 
                FROM historico_produccion GROUP BY productoNombre");
    $dataProductos = $stmtProdR->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            background: #1E293B !important;
            color: #CBD5E1 !important;
            border: 1px solid #334155 !important;
            border-radius: 6px !important;
            padding: 5px 10px !important;
        }
        .dataTables_wrapper .dataTables_length select option {
            background: #0F172A !important;
            color: #CBD5E1 !important;
        }
        .dataTables_wrapper .dataTables_info, 
        .dataTables_wrapper .dataTables_paginate {
            color: #94A3B8 !important;
            margin-top: 15px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #CBD5E1 !important;
            border: 1px solid #334155 !important;
            background: #1E293B !important;
            border-radius: 6px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3B82F6 !important;
            color: white !important;
            border: 1px solid #3B82F6 !important;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Control Principal</h1>
                <p>Inteligencia de negocios y analítica de planta en tiempo real</p>
            </div>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=crear" class="btn-primary">+ Nueva Orden</a>
        </div>

        <!-- FILA 1 DE TARJETAS KPI -->
        <div class="kpi-grid" style="grid-template-columns: repeat(5, 1fr);">
            <div class="kpi-card">
                <div class="kpi-title">Órdenes Activas</div>
                <div class="kpi-value"><?php echo $countActivas; ?></div>
                <div class="kpi-trend trend-up">En proceso</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Lotes Activos</div>
                <div class="kpi-value"><?php echo $countLotes; ?></div>
                <div class="kpi-trend trend-up">Tiempo real</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Alertas Calidad</div>
                <div class="kpi-value" style="color: #F87171;"><?php echo $countAlertas; ?></div>
                <div class="kpi-trend trend-down">Rechazos</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Ganancias (Mes)</div>
                <div class="kpi-value" style="color: #34D399; font-size: 20px;">$<?php echo number_format($gananciasMes, 0, ',', '.'); ?></div>
                <div class="kpi-trend trend-up">Neto positivo</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Pérdidas (Mes)</div>
                <div class="kpi-value" style="color: #F87171; font-size: 20px;">$<?php echo number_format($perdidasMes, 0, ',', '.'); ?></div>
                <div class="kpi-trend trend-down">Por defectos</div>
            </div>
        </div>

        <!-- FILA 2 DE TARJETAS KPI (Ajustada a 2 elementos principales) -->
        <div class="kpi-grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 15px;">
            <div class="kpi-card">
                <div class="kpi-title">Tasa de Defectos</div>
                <div class="kpi-value" style="color: #F59E0B;"><?php echo $tasaDefectos; ?>%</div>
                <div class="kpi-trend trend-down">Scrap Rate del mes</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Auditorías / Inspecciones</div>
                <div class="kpi-value" style="color: #A855F7;"><?php echo $totalInspeccionesMes; ?></div>
                <div class="kpi-trend trend-up">Registros de calidad</div>
            </div>
        </div>

        <!-- SECCIÓN DE GRÁFICAS FILA 1 -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Tasa de Aprobación de Lotes</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartCalidad"></canvas>
                </div>
            </div>
            
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Unidades Correctas Producidas</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartProduccion"></canvas>
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE GRÁFICAS FILA 2 -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Evolución de Ganancias Netas por Mes</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartGanancias"></canvas>
                </div>
            </div>
            
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Rentabilidad Neta por Producto</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartProductos"></canvas>
                </div>
            </div>
        </div>
        
        <!-- TABLA CON EL HISTÓRICO DE PRODUCCIÓN REAL -->
        <div class="table-container" style="margin-top: 25px; margin-bottom: 40px;">
            <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 16px;">Últimas Órdenes Completadas (Trazabilidad)</h3>
            <table id="tablaHistoricoDash" class="display" style="width: 100%; color: #CBD5E1;">
                <thead>
                    <tr style="color: #94A3B8; text-transform: uppercase; font-size: 12px;">
                        <th>REF. ORDEN</th>
                        <th>PRODUCTO</th>
                        <th>PLANIFICADAS</th>
                        <th>CORRECTAS</th>
                        <th>DEFECTUOSAS</th>
                        <th>IMPACTO NETO</th>
                        <th>FECHA CIERRE</th>
                        <th style="text-align: center;">INSPECCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historicoDashboard)): ?>
                        <?php foreach ($historicoDashboard as $h): ?>
                        <tr>
                            <td><strong style="color: #38BDF8;">Orden #<?php echo htmlspecialchars($h['idOrden']); ?></strong></td>
                            <td style="font-weight: bold; color: #FFF;"><?php echo htmlspecialchars($h['productoNombre']); ?></td>
                            <td><?php echo htmlspecialchars($h['cantidadPlanificada']); ?> uds</td>
                            <td style="color: #34D399; font-weight: bold;"><?php echo htmlspecialchars($h['unidadesCorrectas']); ?> uds</td>
                            <td style="color: #F87171;"><?php echo htmlspecialchars($h['unidadesDefectuosas']); ?> uds</td>
                            <td style="font-weight: bold; color: #38BDF8;">$<?php echo number_format($h['impactoFinancieroNeto'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($h['fechaCierre']); ?></td>
                            <td style="text-align: center;">
                                <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo htmlspecialchars($h['idLote'] ?? 0); ?>" style="padding: 6px 10px; background: #3B82F6; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;" title="Ver auditoría">
                                    🔍 Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaHistoricoDash').DataTable({
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados en el histórico",
            emptyTable: "Aún no hay órdenes completadas en el histórico",
            paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
        },
        pageLength: 5
    });

    const makeGradient = (ctx, colorStart, colorEnd) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        return gradient;
    };

    Chart.defaults.plugins.tooltip.backgroundColor = '#1E293B';
    Chart.defaults.plugins.tooltip.titleColor = '#F8FAFC';
    Chart.defaults.plugins.tooltip.bodyColor = '#CBD5E1';
    Chart.defaults.plugins.tooltip.borderColor = '#334155';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;

    // Gráfica 1: Donut
    const ctxCalidad = document.getElementById('chartCalidad').getContext('2d');
    new Chart(ctxCalidad, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($dataCalidad, 'resultado')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($dataCalidad, 'total')); ?>,
                backgroundColor: ['#34D399', '#EF4444', '#F59E0B'],
                borderWidth: 0,
                spacing: 6,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { position: 'bottom', labels: { color: '#94A3B8', font: { family: 'Inter', size: 12 }, boxWidth: 12, padding: 15 } } }
        }
    });

    // Gráfica 2: Barras Producción
    const ctxProd = document.getElementById('chartProduccion').getContext('2d');
    const gradProd = makeGradient(ctxProd, 'rgba(59, 130, 246, 0.95)', 'rgba(30, 58, 138, 0.4)');
    new Chart(ctxProd, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataProduccion, 'mes')); ?>,
            datasets: [{
                label: 'Unidades Correctas',
                data: <?php echo json_encode(array_column($dataProduccion, 'total')); ?>,
                backgroundColor: gradProd,
                borderRadius: 10,
                maxBarThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                x: { ticks: { color: '#94A3B8' }, grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // Gráfica 3: Ganancias Netas Mensuales
    const ctxGan = document.getElementById('chartGanancias').getContext('2d');
    const gradGan = makeGradient(ctxGan, 'rgba(52, 211, 153, 0.6)', 'rgba(52, 211, 153, 0.0)');
    new Chart(ctxGan, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($dataFinancieraMensual, 'mes')); ?>,
            datasets: [{
                label: 'Ganancia Neta ($)',
                data: <?php echo json_encode(array_column($dataFinancieraMensual, 'ganancias')); ?>,
                borderColor: '#34D399',
                backgroundColor: gradGan,
                fill: true,
                tension: 0.3,
                borderWidth: 3,
                pointRadius: 6,
                pointBackgroundColor: '#34D399',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                x: { ticks: { color: '#94A3B8' }, grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // Gráfica 4: Rentabilidad por Producto
    const ctxProdR = document.getElementById('chartProductos').getContext('2d');
    const gradProdR = makeGradient(ctxProdR, 'rgba(168, 85, 247, 0.95)', 'rgba(88, 28, 135, 0.4)');
    new Chart(ctxProdR, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataProductos, 'productoNombre')); ?>,
            datasets: [{
                label: 'Impacto Financiero ($)',
                data: <?php echo json_encode(array_column($dataProductos, 'totalGanancia')); ?>,
                backgroundColor: gradProdR,
                borderRadius: 10,
                maxBarThickness: 45
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { ticks: { color: '#94A3B8', font: { weight: 'bold' } }, grid: { display: false } },
                x: { ticks: { color: '#94A3B8' }, grid: { color: 'rgba(255, 255, 255, 0.05)' } }
            },
            plugins: { legend: { display: false } }
        }
    });
});

window.addEventListener('pageshow', function (event) {
    var isBackNavigation = event.persisted || 
        (window.performance && window.performance.navigation && window.performance.navigation.type === 2) ||
        (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward");
        
    if (isBackNavigation) {
        window.location.reload(true);
    }
});
</script>
<script type="module">
    import Chatbot from "https://cdn.jsdelivr.net/npm/flowise-embed/dist/web.js"
    Chatbot.init({
        chatflowid: "50de36ef-a39c-4cfa-a795-e95952c78ebe",
        apiHost: "https://cloud.flowiseai.com",
    })
</script>
<script src="/ProQuaris/views/js/lote_admin.js"></script>
</body>
</html>