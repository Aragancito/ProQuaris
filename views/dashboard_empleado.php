<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['usuario_nombre']) || !isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::conectar();

$idUsuarioActual = $_SESSION['usuario_id'];

// CONSULTA EN VIVO: Verifica el estado y la planta directamente de la BD
$stmtCheck = $db->prepare("SELECT estado, admin_asignado FROM usuario WHERE id = ?");
$stmtCheck->execute([$idUsuarioActual]);
$datosActuales = $stmtCheck->fetch(PDO::FETCH_ASSOC);

$estadoUsuario = $datosActuales['estado'] ?? 'Pendiente';
$adminIdPlanta = $datosActuales['admin_asignado'] ?? '';

// Actualizamos la sesión al vuelo
$_SESSION['estado'] = $estadoUsuario;
$_SESSION['admin_id'] = $adminIdPlanta;

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Operario';
$tienePlanta = !empty($adminIdPlanta);
$estaAprobado = ($estadoUsuario === 'Activo');
$puedeOperar = ($tienePlanta && $estaAprobado);

$countActivas = 0; $countLotes = 0; $countAlertas = 0;

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
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Operativo - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
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
            <div style="background-color: rgba(245, 158, 11, 0.1); border: 1px solid #F59E0B; padding: 20px; border-radius: 8px; margin-bottom: 25px; color: #FCD34D;">
                <h3 style="margin-bottom: 10px;">⚠️ Requiere Asignación de Planta</h3>
                <p>Aún no has seleccionado tu Administrador Supervisor. Por favor, ve a la sección de <strong>Usuarios y Roles</strong>.</p>
            </div>
        <?php elseif (!$estaAprobado): ?>
            <div style="background-color: rgba(56, 189, 248, 0.1); border: 1px solid #38BDF8; padding: 20px; border-radius: 8px; margin-bottom: 25px; color: #BAE6FD;">
                <h3 style="margin-bottom: 10px;">⏳ Esperando Aprobación</h3>
                <p>Tu solicitud ha sido enviada al Administrador. En cuanto te apruebe, podrás ver y gestionar las órdenes de la planta aquí mismo.</p>
            </div>
        <?php endif; ?>

        <!-- TARJETAS KPI OPERATIVAS: solo las 3 heredadas de la planta -->
        <div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
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
        </div>
    </main>
</div>
</body>
</html>