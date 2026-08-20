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

// --- 1. DATOS REALES PARA LAS TARJETAS KPI ---
$countActivas = 0;
$countLotes = 0;
$countAlertas = 0;
$historicoDashboard = [];
$dataCalidad = [];
$dataProduccion = [];

try {
    // Órdenes activas reales
    $stmtActivas = $db->query("SELECT COUNT(*) FROM ordenproduccion WHERE estado = 'Activa'");
    $countActivas = $stmtActivas->fetchColumn() ?: 0;

    // Lotes activos en tiempo real (vinculados únicamente a órdenes que están Activas)
    $stmtLotes = $db->query("SELECT COUNT(l.idLote) FROM lote l JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden WHERE o.estado = 'Activa'");
    $countLotes = $stmtLotes->fetchColumn() ?: 0;

    // Alertas de calidad (inspecciones rechazadas)
    $stmtAlertas = $db->query("SELECT COUNT(*) FROM registroinspeccion WHERE resultado = 'Rechazado'");
    $countAlertas = $stmtAlertas->fetchColumn() ?: 0;

    // --- 2. DATOS REALES PARA LA TABLA (HISTÓRICO DE PRODUCCIÓN) ---
    $stmtTabla = $db->query("SELECT h.*, l.idLote 
                FROM historico_produccion h 
                LEFT JOIN lote l ON h.idOrden = l.FK_ordenId 
                ORDER BY h.idHistorico DESC LIMIT 10");
    $historicoDashboard = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. DATOS REALES PARA LAS GRÁFICAS ---
    $stmtCal = $db->query("SELECT resultado, COUNT(*) as total FROM registroinspeccion GROUP BY resultado");
    $dataCalidad = $stmtCal->fetchAll(PDO::FETCH_ASSOC);

    $stmtProd = $db->query("SELECT DATE_FORMAT(fechaCierre, '%b') as mes, SUM(unidadesCorrectas) as total 
                FROM historico_produccion GROUP BY MONTH(fechaCierre) ORDER BY MAX(fechaCierre) ASC LIMIT 6");
    $dataProduccion = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Manejo silencioso en caso de tablas vacías
}
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
        /* Corrección definitiva para el fondo blanco del select y buscador de DataTables */
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
                <p>Gestión general de métricas y lotes de planta</p>
            </div>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=crear" class="btn-primary">+ Nueva Orden</a>
        </div>

        <!-- TARJETAS KPI CON DATOS REALES -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Órdenes Activas</div>
                <div class="kpi-value"><?php echo $countActivas; ?></div>
                <div class="kpi-trend trend-up">Estado operativo actual</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Lotes Activos</div>
                <div class="kpi-value"><?php echo $countLotes; ?></div>
                <div class="kpi-trend trend-up">Sincronizados en tiempo real</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Alertas de Calidad (Rechazos)</div>
                <div class="kpi-value" style="color: #F87171;"><?php echo $countAlertas; ?></div>
                <div class="kpi-trend trend-down">Inspecciones con fallas</div>
            </div>
        </div>

        <!-- SECCIÓN DE GRÁFICAS CON ESTILO PROFESIONAL -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
            <!-- Gráfica 1: Tasa de Calidad (Estilo Donut Moderno) -->
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Tasa de Aprobación de Lotes</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartCalidad"></canvas>
                </div>
            </div>
            
            <!-- Gráfica 2: Producción Mensual (Barras Modernas con bordes redondeados) -->
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Unidades Correctas por Mes</h3>
                <div style="position: relative; height: 240px;">
                    <canvas id="chartProduccion"></canvas>
                </div>
            </div>
        </div>
        
        <!-- TABLA CON EL HISTÓRICO DE PRODUCCIÓN REAL -->
        <div class="table-container" style="margin-top: 25px;">
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
                                <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo htmlspecialchars($h['idLote'] ?? 0); ?>" style="padding: 6px 10px; background: #3B82F6; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;" title="Ver auditoría de este lote">
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

    // --- GRÁFICA 1: DONUT MODERNIZADO ---
    const ctxCalidad = document.getElementById('chartCalidad').getContext('2d');
    new Chart(ctxCalidad, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($dataCalidad, 'resultado')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($dataCalidad, 'total')); ?>,
                backgroundColor: ['#34D399', '#EF4444', '#F59E0B'],
                borderWidth: 0,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94A3B8', font: { family: 'Inter', size: 12 }, boxWidth: 10, padding: 15 }
                }
            }
        }
    });

    // --- GRÁFICA 2: BARRAS ESTILIZADAS ---
    const ctxProd = document.getElementById('chartProduccion').getContext('2d');
    new Chart(ctxProd, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataProduccion, 'mes')); ?>,
            datasets: [{
                label: 'Unidades Correctas',
                data: <?php echo json_encode(array_column($dataProduccion, 'total')); ?>,
                backgroundColor: '#3B82F6',
                borderRadius: 8,
                barThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    ticks: { color: '#94A3B8', font: { family: 'Inter' } },
                    grid: { color: 'rgba(255, 255, 255, 0.04)', drawBorder: false }
                },
                x: {
                    ticks: { color: '#94A3B8', font: { family: 'Inter' } },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
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