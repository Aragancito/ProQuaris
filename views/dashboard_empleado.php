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

$nombre = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Empleado - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/dashboard_empleado.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo">ProQuaris</div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombre); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rol); ?></div>
        </div>
        <div class="nav-item active" data-page="panel"><span class="nav-icon">📊</span> Mi Panel</div>
        <div class="nav-item" data-page="lotes"><span class="nav-icon">🏷️</span> Mis Lotes</div>
        <div class="nav-item" data-page="defectos"><span class="nav-icon">🔍</span> Registrar Defecto</div>
        <div class="nav-item" data-page="inspecciones"><span class="nav-icon">📋</span> Inspecciones</div>
        
        <a href="logout.php" class="nav-item logout-btn">
            <span class="nav-icon">🚪</span> Cerrar Sesión
        </a>
    </div>

    <div class="main-content" id="main-content">Cargando...</div>

    <div id="modalDefecto" class="modal">
        <div class="modal-content">
            <h3>Registrar Defecto</h3>
            <form id="formDefecto">
                <select id="lote_id" required><option value="">Seleccione un lote</option></select>
                <input type="text" id="tipo_defecto" placeholder="Tipo de defecto" required>
                <select id="severidad" required>
                    <option value="">Severidad</option>
                    <option>Menor</option>
                    <option>Media</option>
                    <option>Critica</option>
                </select>
                <textarea id="descripcion" placeholder="Descripción" rows="3"></textarea>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalDetalle" class="modal">
        <div class="modal-content" style="width: 500px;">
            <h3>Detalle del Lote</h3>
            <div id="detalleLote"></div>
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="cerrarModalDetalle()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        function cargarPanel() {
            fetch('empleado_api.php?action=panel')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('main-content').innerHTML = `
                        <h1>Mi Panel de Control</h1>
                        <div class="kpi-grid">
                            <div class="kpi-card"><div class="kpi-title">Lotes asignados</div><div class="kpi-value">${d.lotes_asignados}</div></div>
                            <div class="kpi-card"><div class="kpi-title">Defectos registrados</div><div class="kpi-value">${d.defectos_registrados}</div></div>
                            <div class="kpi-card"><div class="kpi-title">Tareas pendientes</div><div class="kpi-value">${d.tareas_pendientes}</div></div>
                        </div>
                        <div class="table-container">
                            <table id="tablaLotesEmpleado" class="display">
                                <thead>
                                    <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Estado</th><th>Acción</th></tr>
                                </thead>
                                <tbody>
                                    ${d.lotes_recientes.map(l => `
                                        <tr>
                                            <td>${l.codigo}</td>
                                            <td>${l.producto}</td>
                                            <td>${l.cantidad}</td>
                                            <td><span class="badge ${l.estado === 'completado' ? 'badge-success' : 'badge-warning'}">${l.estado}</span></td>
                                            <td><button class="btn-outline" onclick="verDetalleLote(${l.id})">Ver detalle</button></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <button class="btn-primary" onclick="abrirModalDefecto()">+ Registrar defecto</button>
                    `;
                    if (typeof $.fn.DataTable !== 'undefined') { $('#tablaLotesEmpleado').DataTable({ responsive: true }); }
                });
        }
        function verDetalleLote(id) {
            fetch('empleado_api.php?action=detalle_lote&id=' + id).then(r => r.json()).then(d => {
                document.getElementById('detalleLote').innerHTML = `<p>ID: ${d.id}</p><p>Código: ${d.codigo}</p><p>Estado: ${d.estado}</p>`;
                document.getElementById('modalDetalle').style.display = 'flex';
            });
        }
        function abrirModalDefecto() { document.getElementById('modalDefecto').style.display = 'flex'; }
        function cerrarModal() { document.getElementById('modalDefecto').style.display = 'none'; document.getElementById('formDefecto').reset(); }
        function cerrarModalDetalle() { document.getElementById('modalDetalle').style.display = 'none'; }
        
        document.querySelectorAll('.nav-item[data-page]').forEach(el => {
            el.addEventListener('click', () => {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                el.classList.add('active');
                if (el.dataset.page === 'panel') cargarPanel();
            });
        });

        cargarPanel();
    </script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
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