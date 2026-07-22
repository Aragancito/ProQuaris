<?php
// Encabezados HTTP estrictos para impedir caché en navegadores
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header"><div class="logo">ProQuaris</div></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="../controllers/OrdenController.php?accion=listar" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>Órdenes de Producción</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">🏷️</span>
                <span>Lotes y Calidad</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">📦</span>
                <span>Inventario Materia Prima</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👥</span>
                <span>Usuarios y Roles</span>
            </a>
        </nav>
        <div style="padding:20px;">
            <a href="logout.php" class="nav-item" style="color:#FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Control Principal</h1>
                <p>Gestión general de métricas y lotes de planta</p>
            </div>
            <a href="../controllers/OrdenController.php?accion=crear" class="btn btn-primary">+ Nueva Orden</a>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Órdenes Activas</div>
                <div class="kpi-value">12</div>
                <div class="kpi-trend trend-up">↑ +3 vs mes anterior</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Lotes Producidos hoy</div>
                <div class="kpi-value">45</div>
                <div class="kpi-trend trend-up">↑ +8% vs ayer</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Alertas de Calidad</div>
                <div class="kpi-value">3</div>
                <div class="kpi-trend trend-down">↓ -2 vs semana pasada</div>
            </div>
        </div>
        
        <div class="table-container">
            <table id="tablaLotes" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>CÓDIGO LOTE</th>
                        <th>FECHA</th>
                        <th>CANTIDAD</th>
                        <th>ESTADO</th>
                        <th style="text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>LOT-2026-001</strong></td>
                        <td>2026-07-22</td>
                        <td>500 uds</td>
                        <td><span class="badge badge-success">Aprobado</span></td>
                        <td style="text-align: center;">
                            <a href="#" class="btn-action btn-edit" title="Editar lote">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="#" class="btn-action btn-delete" title="Eliminar lote">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>LOT-2026-002</strong></td>
                        <td>2026-07-22</td>
                        <td>350 uds</td>
                        <td><span class="badge badge-danger">Rechazado</span></td>
                        <td style="text-align: center;">
                            <a href="#" class="btn-action btn-edit" title="Editar lote">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <a href="#" class="btn-action btn-delete" title="Eliminar lote">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaLotes').DataTable({
        language: {
            processing: "Procesando...",
            search: "Search:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron resultados",
            emptyTable: "Ningún dato disponible en esta tabla",
            paginate: {
                first: "Primero",
                previous: "Previous",
                next: "Next",
                last: "Último"
            }
        },
        pageLength: 10
    });
});

// Intercepta la carga desde el historial (Botón Atrás) y obliga a recargar contra el servidor PHP
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
</body>
</html>