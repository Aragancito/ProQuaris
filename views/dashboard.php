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

// Bloqueo de seguridad: Si no es Administrador, redirigir al panel de empleado
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Empleado';
if ($rolUsuario !== 'Administrador') {
    header("Location: dashboard_empleado.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

// Planta del Administrador actual. Para un Administrador, admin_id = su propio id
// (así quedó definido en el login). Todo lo que sigue se filtra por este valor,
// para que cada Administrador vea SOLO su propia planta y no la de otros.
$adminIdPlanta = $_SESSION['admin_id'] ?? $_SESSION['usuario_id'] ?? null;

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

// --- BLOQUE 1: MÉTRICAS PRINCIPALES Y KPI (SEGURAS, FILTRADAS POR PLANTA) ---
try {
    $stmtActivas = $db->prepare("SELECT COUNT(*) FROM ordenproduccion WHERE estado = 'Activa' AND admin_id = ?");
    $stmtActivas->execute([$adminIdPlanta]);
    $countActivas = $stmtActivas->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtLotes = $db->prepare("SELECT COUNT(l.idLote) FROM lote l JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden WHERE o.estado = 'Activa' AND o.admin_id = ?");
    $stmtLotes->execute([$adminIdPlanta]);
    $countLotes = $stmtLotes->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtAlertas = $db->prepare("SELECT COUNT(*) FROM registroinspeccion WHERE resultado = 'Rechazado' AND admin_id = ?");
    $stmtAlertas->execute([$adminIdPlanta]);
    $countAlertas = $stmtAlertas->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    // historico_produccion no tiene admin_id propio: se llega a la planta a través
    // de la orden (idOrden) que sí tiene admin_id.
    $stmtGanancias = $db->prepare("SELECT SUM(h.impactoFinancieroNeto) 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ? AND MONTH(h.fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(h.fechaCierre) = YEAR(CURRENT_DATE())");
    $stmtGanancias->execute([$adminIdPlanta]);
    $gananciasMes = $stmtGanancias->fetchColumn() ?: 0;
} catch (Exception $e) {}

try {
    $stmtPerdidas = $db->prepare("SELECT SUM(h.unidadesDefectuosas) 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ? AND MONTH(h.fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(h.fechaCierre) = YEAR(CURRENT_DATE())");
    $stmtPerdidas->execute([$adminIdPlanta]);
    $totalDefectuosasMes = $stmtPerdidas->fetchColumn() ?: 0;
    $perdidasMes = $totalDefectuosasMes * 50000; 
} catch (Exception $e) {}

// --- BLOQUE 2: MÉTRICAS DE CALIDAD EN TIEMPO REAL (FILTRADAS POR PLANTA) ---
try {
    $stmtScrap = $db->prepare("SELECT (SUM(h.unidadesDefectuosas) / NULLIF(SUM(h.cantidadPlanificada), 0)) * 100 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ? AND MONTH(h.fechaCierre) = MONTH(CURRENT_DATE()) AND YEAR(h.fechaCierre) = YEAR(CURRENT_DATE())");
    $stmtScrap->execute([$adminIdPlanta]);
    $tasaDefectos = round($stmtScrap->fetchColumn() ?: 0, 1);
} catch (Exception $e) { $tasaDefectos = 0; }

try {
    $stmtInspMes = $db->prepare("SELECT COUNT(*) FROM registroinspeccion WHERE admin_id = ?");
    $stmtInspMes->execute([$adminIdPlanta]);
    $totalInspeccionesMes = $stmtInspMes->fetchColumn() ?: 0;
} catch (Exception $e) { $totalInspeccionesMes = 0; }

// --- BLOQUE 3: TABLA Y DATOS PARA GRÁFICAS (FILTRADOS POR PLANTA) ---
try {
    $stmtTabla = $db->prepare("SELECT h.*, l.idLote 
                FROM historico_produccion h 
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                LEFT JOIN lote l ON h.idOrden = l.FK_ordenId 
                WHERE o.admin_id = ?
                ORDER BY h.idHistorico DESC LIMIT 10");
    $stmtTabla->execute([$adminIdPlanta]);
    $historicoDashboard = $stmtTabla->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtCal = $db->prepare("SELECT resultado, COUNT(*) as total FROM registroinspeccion WHERE admin_id = ? GROUP BY resultado");
    $stmtCal->execute([$adminIdPlanta]);
    $dataCalidad = $stmtCal->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtProd = $db->prepare("SELECT DATE_FORMAT(h.fechaCierre, '%b %Y') as mes, SUM(h.unidadesCorrectas) as total 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ?
                GROUP BY YEAR(h.fechaCierre), MONTH(h.fechaCierre) ORDER BY MAX(h.fechaCierre) ASC LIMIT 6");
    $stmtProd->execute([$adminIdPlanta]);
    $dataProduccion = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtFin = $db->prepare("SELECT DATE_FORMAT(h.fechaCierre, '%b %Y') as mes, SUM(h.impactoFinancieroNeto) as ganancias 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ?
                GROUP BY YEAR(h.fechaCierre), MONTH(h.fechaCierre) ORDER BY MAX(h.fechaCierre) ASC LIMIT 6");
    $stmtFin->execute([$adminIdPlanta]);
    $dataFinancieraMensual = $stmtFin->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

try {
    $stmtProdR = $db->prepare("SELECT h.productoNombre, SUM(h.impactoFinancieroNeto) as totalGanancia, SUM(h.unidadesCorrectas) as correctas 
                FROM historico_produccion h
                JOIN ordenproduccion o ON h.idOrden = o.idOrden
                WHERE o.admin_id = ?
                GROUP BY h.productoNombre");
    $stmtProdR->execute([$adminIdPlanta]);
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Tasa de Aprobación de Lotes</h3>
                <div style="position: relative; height: 240px;"><canvas id="chartCalidad"></canvas></div>
            </div>
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Unidades Correctas Producidas</h3>
                <div style="position: relative; height: 240px;"><canvas id="chartProduccion"></canvas></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px;">
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Evolución de Ganancias Netas por Mes</h3>
                <div style="position: relative; height: 240px;"><canvas id="chartGanancias"></canvas></div>
            </div>
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 15px; font-weight: 600;">Rentabilidad Neta por Producto</h3>
                <div style="position: relative; height: 240px;"><canvas id="chartProductos"></canvas></div>
            </div>
        </div>
        
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
                                <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo htmlspecialchars($h['idLote'] ?? 0); ?>" style="padding: 6px 10px; background: #3B82F6; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">🔍 Ver</a>
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
        pageLength: 5,
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "Aún no hay órdenes completadas",
            paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
        }
    });

    const makeGradient = (ctx, colorStart, colorEnd) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, colorStart);
        gradient.addColorStop(1, colorEnd);
        return gradient;
    };

    const ctxCalidad = document.getElementById('chartCalidad').getContext('2d');
    // Colores fijos por nombre de resultado, NO por posición en el arreglo.
    // Así, aunque una planta solo tenga un tipo de resultado (ej. solo "Rechazado"),
    // ese resultado siempre sale con su color correcto en vez de tomar el primero de la lista.
    const coloresCalidad = {
        'Aprobado': '#34D399',
        'Observación': '#F59E0B',
        'Rechazado': '#EF4444'
    };
    const etiquetasCalidad = <?php echo json_encode(array_column($dataCalidad, 'resultado')); ?>;
    const coloresOrdenados = etiquetasCalidad.map(function(etiqueta) {
        return coloresCalidad[etiqueta] || '#94A3B8';
    });
    new Chart(ctxCalidad, {
        type: 'doughnut',
        data: {
            labels: etiquetasCalidad,
            datasets: [{
                data: <?php echo json_encode(array_column($dataCalidad, 'total')); ?>,
                backgroundColor: coloresOrdenados,
                borderWidth: 0, spacing: 6
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { position: 'bottom', labels: { color: '#94A3B8' } } } }
    });

    const ctxProd = document.getElementById('chartProduccion').getContext('2d');
    const gradProd = makeGradient(ctxProd, 'rgba(59, 130, 246, 0.95)', 'rgba(30, 58, 138, 0.4)');
    new Chart(ctxProd, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataProduccion, 'mes')); ?>,
            datasets: [{ label: 'Unidades', data: <?php echo json_encode(array_column($dataProduccion, 'total')); ?>, backgroundColor: gradProd, borderRadius: 10 }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { color: '#94A3B8' } }, x: { ticks: { color: '#94A3B8' } } }, plugins: { legend: { display: false } } }
    });

    const ctxGan = document.getElementById('chartGanancias').getContext('2d');
    const gradGan = makeGradient(ctxGan, 'rgba(52, 211, 153, 0.6)', 'rgba(52, 211, 153, 0.0)');
    new Chart(ctxGan, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($dataFinancieraMensual, 'mes')); ?>,
            datasets: [{ data: <?php echo json_encode(array_column($dataFinancieraMensual, 'ganancias')); ?>, borderColor: '#34D399', backgroundColor: gradGan, fill: true, tension: 0.3 }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { color: '#94A3B8' } }, x: { ticks: { color: '#94A3B8' } } }, plugins: { legend: { display: false } } }
    });

    const ctxProdR = document.getElementById('chartProductos').getContext('2d');
    const gradProdR = makeGradient(ctxProdR, 'rgba(168, 85, 247, 0.95)', 'rgba(88, 28, 135, 0.4)');
    new Chart(ctxProdR, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($dataProductos, 'productoNombre')); ?>,
            datasets: [{ data: <?php echo json_encode(array_column($dataProductos, 'totalGanancia')); ?>, backgroundColor: gradProdR, borderRadius: 10 }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, scales: { y: { ticks: { color: '#94A3B8' } }, x: { ticks: { color: '#94A3B8' } } }, plugins: { legend: { display: false } } }
    });
});
</script>
</body>
</html>