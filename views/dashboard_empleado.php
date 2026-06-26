<?php
// ==========================================
// VERIFICACIÓN DE SESIÓN ACTIVA
// ==========================================
session_start();
// Si no hay sesión, redirige al login para evitar acceso no autorizado
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

// Recupera los datos del usuario desde la sesión
$nombre = $_SESSION['usuario_nombre'];
$rol = $_SESSION['usuario_rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
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
    <title>Panel Empleado - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/dashboard_empleado.css">

    <!-- ========================================== -->
    <!-- DATATABLES CSS                             -->
    <!-- ========================================== -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
    <!-- ========================================== -->
    <!-- SIDEBAR - Menú lateral del empleado        -->
    <!-- ========================================== -->
    <div class="sidebar">
        <div class="logo">ProQuaris</div>
        <div class="user-info">
            <!-- htmlspecialchars evita inyección XSS -->
            <div class="user-name"><?php echo htmlspecialchars($nombre); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rol); ?></div>
        </div>
        <!-- data-page identifica la sección a cargar vía fetch -->
        <div class="nav-item active" data-page="panel"><span class="nav-icon">📊</span> Mi Panel</div>
        <div class="nav-item" data-page="lotes"><span class="nav-icon">🏷️</span> Mis Lotes</div>
        <div class="nav-item" data-page="defectos"><span class="nav-icon">🔍</span> Registrar Defecto</div>
        <div class="nav-item" data-page="inspecciones"><span class="nav-icon">📋</span> Inspecciones</div>
        <a href="../controllers/UsuarioController.php?logout=true" class="nav-item logout-btn"><span class="nav-icon">🚪</span> Cerrar Sesión</a>
    </div>

    <!-- ========================================== -->
    <!-- CONTENIDO PRINCIPAL                        -->
    <!-- ========================================== -->
    <!-- Se carga dinámicamente vía JavaScript según la opción seleccionada -->
    <div class="main-content" id="main-content">Cargando...</div>

    <!-- ========================================== -->
    <!-- MODAL: Registrar Defecto                   -->
    <!-- ========================================== -->
    <div id="modalDefecto" class="modal">
        <div class="modal-content">
            <h3>Registrar Defecto</h3>
            <form id="formDefecto">
                <!-- Los lotes se cargan dinámicamente desde la API -->
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

    <!-- ========================================== -->
    <!-- MODAL: Detalle de Lote                     -->
    <!-- ========================================== -->
    <div id="modalDetalle" class="modal">
        <div class="modal-content" style="width: 500px;">
            <h3>Detalle del Lote</h3>
            <div id="detalleLote"></div> <!-- Se llena dinámicamente vía fetch -->
            <div class="modal-buttons">
                <button type="button" class="btn-cancel" onclick="cerrarModalDetalle()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // CARGAR PANEL PRINCIPAL
        // ==========================================
        // ABSTRACCIÓN: La función oculta la lógica de fetch y renderizado.
        // El usuario solo llama a cargarPanel() y obtiene el contenido completo.
        function cargarPanel() {
            // POLIMORFISMO: fetch se adapta a diferentes endpoints (panel, lotes, inspecciones)
            fetch('empleado_api.php?action=panel')
                .then(r => r.json())
                .then(d => {
                    // ABSTRACCIÓN: Template literals ocultan la construcción del HTML
                    document.getElementById('main-content').innerHTML = `
                        <h1>Mi Panel de Control</h1>
                        <p>${new Date().toLocaleDateString()}</p>
                        <div class="kpi-grid">
                            <div class="kpi-card"><div class="kpi-title">Lotes asignados</div><div class="kpi-value">${d.lotes_asignados}</div></div>
                            <div class="kpi-card"><div class="kpi-title">Defectos registrados</div><div class="kpi-value">${d.defectos_registrados}</div></div>
                            <div class="kpi-card"><div class="kpi-title">Tareas pendientes</div><div class="kpi-value">${d.tareas_pendientes}</div></div>
                        </div>
                        <div class="table-container">
                            <div class="table-header"><h3>Mis Lotes Recientes</h3></div>
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
                                            <!-- POLIMORFISMO: El badge cambia según el estado -->
                                            <td><span class="badge ${l.estado === 'completado' ? 'badge-success' : 'badge-warning'}">${l.estado}</span></td>
                                            <td><button class="btn-outline" onclick="verDetalleLote(${l.id})">Ver detalle</button></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <button class="btn-primary" onclick="abrirModalDefecto()">+ Registrar defecto</button>
                    `;

                    // ==========================================
                    // INICIALIZAR DATATABLES DESPUÉS DE CARGAR
                    // ==========================================
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#tablaLotesEmpleado').DataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                { extend: 'pdf', text: '📄 Exportar PDF', className: 'btn btn-primary' },
                                { extend: 'excel', text: '📊 Exportar Excel', className: 'btn btn-success' }
                            ],
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            responsive: true
                        });
                    }
                }).catch(e => { document.getElementById('main-content').innerHTML = `<p>Error: ${e.message}</p>`; });
        }

        // ==========================================
        // CARGAR LISTA DE LOTES
        // ==========================================
        // ABSTRACCIÓN: Oculta la lógica de fetch y renderizado de lotes.
        function cargarLotes() {
            fetch('empleado_api.php?action=lotes')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('main-content').innerHTML = `
                        <h1>Mis Lotes</h1>
                        <div class="table-container">
                            <table id="tablaLotesCompleta" class="display">
                                <thead>
                                    <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr>
                                </thead>
                                <tbody>
                                    ${d.map(l => `
                                        <tr>
                                            <td>${l.codigo}</td>
                                            <td>${l.producto}</td>
                                            <td>${l.cantidad}</td>
                                            <td>${l.fecha}</td>
                                            <td><span class="badge ${l.estado === 'completado' ? 'badge-success' : 'badge-warning'}">${l.estado}</span></td>
                                            <td><button class="btn-outline" onclick="verDetalleLote(${l.id})">Ver</button></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;

                    // Inicializar DataTables con exportación
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#tablaLotesCompleta').DataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                { extend: 'pdf', text: '📄 Exportar PDF', className: 'btn btn-primary' },
                                { extend: 'excel', text: '📊 Exportar Excel', className: 'btn btn-success' }
                            ],
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            responsive: true
                        });
                    }
                });
        }

        // ==========================================
        // CARGAR INSPECCIONES
        // ==========================================
        // ABSTRACCIÓN: Oculta la lógica de fetch y renderizado de inspecciones.
        function cargarInspecciones() {
            fetch('empleado_api.php?action=inspecciones')
                .then(r => r.json())
                .then(d => {
                    if(d.length === 0) {
                        document.getElementById('main-content').innerHTML = `<h1>Inspecciones</h1><p>No hay inspecciones registradas</p>`;
                        return;
                    }
                    document.getElementById('main-content').innerHTML = `
                        <h1>Inspecciones de Calidad</h1>
                        <div class="table-container">
                            <table id="tablaInspecciones" class="display">
                                <thead>
                                    <tr><th>Lote</th><th>Fecha</th><th>Resultado</th><th>Observaciones</th></tr>
                                </thead>
                                <tbody>
                                    ${d.map(i => `
                                        <tr>
                                            <td>${i.lote_codigo || i.FK_loteld}</td>
                                            <td>${i.fecha}</td>
                                            <td><span class="badge ${i.resultado === 'aprobado' ? 'badge-success' : 'badge-danger'}">${i.resultado}</span></td>
                                            <td>${i.observaciones || '-'}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;

                    // Inicializar DataTables con exportación
                    if (typeof $.fn.DataTable !== 'undefined') {
                        $('#tablaInspecciones').DataTable({
                            dom: 'Bfrtip',
                            buttons: [
                                { extend: 'pdf', text: '📄 Exportar PDF', className: 'btn btn-primary' },
                                { extend: 'excel', text: '📊 Exportar Excel', className: 'btn btn-success' }
                            ],
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            responsive: true
                        });
                    }
                });
        }

        // ==========================================
        // VER DETALLE DE LOTE (MODAL)
        // ==========================================
        // ABSTRACCIÓN: Oculta la lógica de fetch y renderizado del detalle.
        // ENCAPSULAMIENTO: El modal se abre y cierra mediante funciones específicas.
        function verDetalleLote(id) {
            fetch('empleado_api.php?action=detalle_lote&id=' + id)
                .then(r => r.json())
                .then(d => {
                    document.getElementById('detalleLote').innerHTML = `
                        <div class="detail-row"><div class="detail-label">ID Lote:</div><div class="detail-value">${d.id}</div></div>
                        <div class="detail-row"><div class="detail-label">Código:</div><div class="detail-value">${d.codigo}</div></div>
                        <div class="detail-row"><div class="detail-label">Producto:</div><div class="detail-value">${d.producto}</div></div>
                        <div class="detail-row"><div class="detail-label">Cantidad:</div><div class="detail-value">${d.cantidad}</div></div>
                        <div class="detail-row"><div class="detail-label">Fecha creación:</div><div class="detail-value">${d.fecha}</div></div>
                        <div class="detail-row"><div class="detail-label">Estado:</div><div class="detail-value">${d.estado}</div></div>
                    `;
                    document.getElementById('modalDetalle').style.display = 'flex';
                });
        }

        // ==========================================
        // ABRIR MODAL DE REGISTRO DE DEFECTO
        // ==========================================
        // Carga los lotes disponibles desde la API antes de mostrar el modal
        function abrirModalDefecto() {
            fetch('empleado_api.php?action=mislotes')
                .then(r => r.json())
                .then(d => {
                    let opts = '<option value="">Seleccione un lote</option>';
                    d.forEach(l => opts += `<option value="${l.id}">${l.codigo} - ${l.producto}</option>`);
                    document.getElementById('lote_id').innerHTML = opts;
                    document.getElementById('modalDefecto').style.display = 'flex';
                });
        }

        // ==========================================
        // ENVÍO DEL FORMULARIO DE DEFECTO
        // ==========================================
        // ABSTRACCIÓN: Usa FormData para ocultar la construcción del payload.
        // POLIMORFISMO: fetch se adapta a POST con diferentes datos.
        document.getElementById('formDefecto').addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData();
            fd.append('action', 'registrar_defecto');
            fd.append('lote_id', document.getElementById('lote_id').value);
            fd.append('tipo', document.getElementById('tipo_defecto').value);
            fd.append('severidad', document.getElementById('severidad').value);
            fd.append('descripcion', document.getElementById('descripcion').value);
            fetch('empleado_api.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => { 
                    if(d.success){ 
                        alert('Defecto registrado correctamente'); 
                        cerrarModal(); 
                        cargarPanel(); 
                    } else { 
                        alert('Error al registrar'); 
                    } 
                });
        });

        // ==========================================
        // FUNCIONES AUXILIARES DE MODALES
        // ==========================================
        // ENCAPSULAMIENTO: Funciones específicas para controlar los modales.
        function cerrarModal() { document.getElementById('modalDefecto').style.display = 'none'; document.getElementById('formDefecto').reset(); }
        function cerrarModalDetalle() { document.getElementById('modalDetalle').style.display = 'none'; }

        // ==========================================
        // NAVEGACIÓN POR MENÚ LATERAL
        // ==========================================
        // POLIMORFISMO: El mismo manejador de eventos decide qué función ejecutar
        // según la página seleccionada.
        document.querySelectorAll('.nav-item[data-page]').forEach(el => {
            el.addEventListener('click', () => {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                el.classList.add('active');
                const page = el.dataset.page;
                if (page === 'panel') cargarPanel();
                else if (page === 'lotes') cargarLotes();
                else if (page === 'defectos') abrirModalDefecto();
                else if (page === 'inspecciones') cargarInspecciones();
            });
        });

        // ==========================================
        // INICIALIZACIÓN
        // ==========================================
        // Carga el panel principal al cargar la página
        cargarPanel();
    </script>

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
</body>

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

</html>