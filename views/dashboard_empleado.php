<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Operario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Operario';
$adminIdPlanta = $_SESSION['admin_id'] ?? ''; 
$estadoUsuario = $_SESSION['estado'] ?? 'Pendiente'; // Verificamos si ya lo aprobaron

// Variables para saber si el operario puede trabajar
$tienePlanta = !empty($adminIdPlanta);
$estaAprobado = ($estadoUsuario === 'Activo');
$puedeOperar = ($tienePlanta && $estaAprobado);

require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::conectar();

$countActivas = 0; $countLotes = 0; $countAlertas = 0; $totalInspecciones = 0; $ordenesActivasList = [];

// Solo extraemos datos si está aprobado y tiene planta
if ($puedeOperar) {
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
        $stmtInsp = $db->prepare("SELECT COUNT(*) FROM registroinspeccion WHERE admin_id = ?");
        $stmtInsp->execute([$adminIdPlanta]);
        $totalInspecciones = $stmtInsp->fetchColumn() ?: 0;
    } catch (Exception $e) {}

    try {
        // CORRECCIÓN: producto AS productoNombre para evitar error de array key
        $stmtOrdenes = $db->prepare("SELECT *, producto AS productoNombre FROM ordenproduccion WHERE estado = 'Activa' AND admin_id = ? ORDER BY idOrden DESC LIMIT 5");
        $stmtOrdenes->execute([$adminIdPlanta]);
        $ordenesActivasList = $stmtOrdenes->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Operativo - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Operaciones en Planta</h1>
                <p>Control de producción, lotes activos e inspecciones de calidad en tiempo real</p>
            </div>
            <?php if ($puedeOperar): ?>
                <a href="/ProQuaris/controllers/OrdenController.php?accion=crear" class="btn-primary">+ Nueva Orden</a>
            <?php endif; ?>
        </div>

        <?php if (!$tienePlanta): ?>
            <!-- ALERTA: SIN PLANTA ASIGNADA -->
            <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid #F59E0B; padding: 20px; border-radius: 8px; margin-bottom: 25px; color: #FCD34D;">
                <h3 style="margin-bottom: 10px;">⚠️ Requiere Asignación de Planta</h3>
                <p>Aún no has seleccionado tu Administrador Supervisor. Por favor, ve a la sección de <strong>Usuarios y Roles</strong> para enviar tu solicitud a una planta.</p>
            </div>
        <?php elseif (!$estaAprobado): ?>
            <!-- ALERTA: ESPERANDO APROBACIÓN -->
            <div style="background-color: rgba(56, 189, 248, 0.1); border: 1px solid #38BDF8; padding: 20px; border-radius: 8px; margin-bottom: 25px; color: #BAE6FD;">
                <h3 style="margin-bottom: 10px;">⏳ Esperando Aprobación</h3>
                <p>Tu solicitud ha sido enviada al Administrador. Las funciones de producción se activarán <strong>automáticamente</strong> en cuanto aprueben tu acceso.</p>
            </div>
        <?php endif; ?>

        <!-- KPI's (Si no está aprobado se muestran en 0) -->
        <div class="kpi-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="kpi-card">
                <div class="kpi-title">Órdenes Activas</div>
                <div class="kpi-value" style="color: #38BDF8;"><?php echo $countActivas; ?></div>
                <div class="kpi-trend trend-up">En proceso en planta</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Lotes Activos</div>
                <div class="kpi-value" style="color: #34D399;"><?php echo $countLotes; ?></div>
                <div class="kpi-trend trend-up">Trazabilidad activa</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Alertas de Calidad</div>
                <div class="kpi-value" style="color: #F87171;"><?php echo $countAlertas; ?></div>
                <div class="kpi-trend trend-down">Lotes rechazados</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Inspecciones Totales</div>
                <div class="kpi-value" style="color: #A855F7;"><?php echo $totalInspecciones; ?></div>
                <div class="kpi-trend trend-up">Registros de control</div>
            </div>
        </div>

        <!-- TABLA DE ÓRDENES ACTIVAS EN PLANTA -->
        <div class="table-container" style="margin-top: 25px; margin-bottom: 40px;">
            <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 16px;">Órdenes de Producción Activas (Para Ejecución)</h3>
            <table id="tablaOperativa" class="display" style="width: 100%; color: #CBD5E1;">
                <thead>
                    <tr style="color: #94A3B8; text-transform: uppercase; font-size: 12px;">
                        <th>REF. ORDEN</th>
                        <th>PRODUCTO</th>
                        <th>CANTIDAD PLANIFICADA</th>
                        <th>ESTADO</th>
                        <th style="text-align: center;">ACCIONES / CALIDAD</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenesActivasList) && $puedeOperar): ?>
                        <?php foreach ($ordenesActivasList as $ord): ?>
                        <tr>
                            <td><strong style="color: #38BDF8;">Orden #<?php echo htmlspecialchars($ord['idOrden']); ?></strong></td>
                            <td style="font-weight: bold; color: #FFF;"><?php echo htmlspecialchars($ord['productoNombre'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($ord['cantidadPlanificada']); ?> uds</td>
                            <td><span style="color: #34D399; font-weight: bold;"><?php echo htmlspecialchars($ord['estado']); ?></span></td>
                            <td style="text-align: center;">
                                <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" style="padding: 6px 12px; background: #3B82F6; color: white; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold;">
                                    🔍 Ver Lotes / Inspeccionar
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
    $('#tablaOperativa').DataTable({
        language: {
            processing: "Procesando...",
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No hay órdenes activas en este momento",
            emptyTable: "No hay órdenes activas en planta o tu acceso aún no está aprobado",
            paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
        },
        pageLength: 5
    });
});
</script>
</body>
</html>