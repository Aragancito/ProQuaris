<?php
// ==========================================
// VERIFICACIÓN DE SESIÓN ACTIVA
// ==========================================
// ABSTRACCIÓN: session_start() maneja la persistencia de datos del usuario
// sin exponer cómo se almacenan las sesiones (archivos, cookies, etc.)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión, redirige al login para evitar acceso no autorizado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

// Recupera los datos del usuario desde la sesión con valores por defecto
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Empleado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ========================================== -->
    <!-- META TAGS PARA EVITAR CACHÉ (LOGOUT)      -->
    <!-- ========================================== -->
    <!-- Estos meta tags evitan que el navegador guarde la página en caché,
         forzando una carga fresca desde el servidor. Esto es crítico para
         que después del logout el usuario no pueda retroceder y ver datos
         de sesiones anteriores. -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">

    <!-- ========================================== -->
    <!-- DATATABLES CSS                             -->
    <!-- ========================================== -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
<div class="dashboard-container">
    <!-- ========================================== -->
    <!-- SIDEBAR - Menú lateral del administrador   -->
    <!-- ========================================== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ProQuaris</div>
        </div>
        <div class="user-info">
            <!-- htmlspecialchars previene inyección XSS -->
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <!-- Cada item del menú corresponde a un módulo del sistema -->
            <a href="#" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="#" class="nav-item">
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
        <!-- Cierre de sesión siempre visible al final del sidebar -->
        <div style="padding: 20px;">
            <a href="../controllers/UsuarioController.php?logout=true" class="nav-item" style="color: #FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- CONTENIDO PRINCIPAL                        -->
    <!-- ========================================== -->
    <main class="main-content">
        <!-- Encabezado con título y fecha actual -->
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Control Principal</h1>
                <!-- date('F, Y') muestra el mes y año en formato texto -->
                <p><?php echo date('F, Y'); ?></p>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TARJETAS KPI (Indicadores Clave)           -->
        <!-- ========================================== -->
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

        <!-- ========================================== -->
        <!-- TABLA DE LOTES RECIENTES CON DATATABLES   -->
        <!-- ========================================== -->
        <div class="table-container">
            <div class="table-header">
                <h3>Últimos Lotes Verificados</h3>
            </div>
            <table id="tablaLotes" class="display">
                <thead>
                    <tr>
                        <th>Código Lote</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>LOT-2026-001</td>
                        <td>21/05/2026</td>
                        <td>500 uds</td>
                        <td><span class="badge badge-success">Aprobado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-002</td>
                        <td>21/05/2026</td>
                        <td>350 uds</td>
                        <td><span class="badge badge-danger">Rechazado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-003</td>
                        <td>20/05/2026</td>
                        <td>280 uds</td>
                        <td><span class="badge badge-success">Aprobado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-004</td>
                        <td>20/05/2026</td>
                        <td>420 uds</td>
                        <td><span class="badge badge-warning">En revisión</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- ========================================== -->
<!-- JQUERY Y DATATABLES                        -->
<!-- ========================================== -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    $(document).ready(function() {
        // ==========================================
        // INICIALIZACIÓN DE DATATABLES
        // ==========================================
        // ABSTRACCIÓN: DataTables oculta la lógica de paginación,
        // búsqueda, ordenamiento y exportación de datos.
        // POLIMORFISMO: Los botones permiten diferentes formatos de
        // exportación (PDF, Excel, Imprimir) desde la misma tabla.
        $('#tablaLotes').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'pdf', text: '📄 Exportar PDF', className: 'btn btn-primary' },
                { extend: 'excel', text: '📊 Exportar Excel', className: 'btn btn-success' },
                { extend: 'print', text: '🖨️ Imprimir', className: 'btn btn-secondary' }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            responsive: true
        });
    });
</script>

<!-- ========================================== -->
<!-- BOTPRESS CHATBOT                           -->
<!-- ========================================== -->
<script type="module">
    import Chatbot from "https://cdn.jsdelivr.net/npm/flowise-embed/dist/web.js"
    Chatbot.init({
        chatflowid: "50de36ef-a39c-4cfa-a795-e95952c78ebe",
        apiHost: "https://cloud.flowiseai.com",
    })
</script>

</body>
</html>