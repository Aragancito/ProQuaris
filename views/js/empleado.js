// ==========================================
// CARGAR PANEL PRINCIPAL
// ==========================================
function cargarPanel() {
    fetch('empleado_api.php?action=panel')
        .then(r => r.json())
        .then(d => {
            document.getElementById('main-content').innerHTML = `
                <div class="top-bar">
                    <div class="page-title">
                        <h1>Mi Panel de Control</h1>
                        <p>Resumen general de tus actividades y lotes asignados</p>
                    </div>
                </div>
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
                                    <td><span class="badge ${l.estado === 'completado' ? 'badge-success' : 'badge-danger'}">${l.estado}</span></td>
                                    <td><button class="btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="verDetalleLote(${l.id})">Ver detalle</button></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 20px;">
                    <button class="btn-primary" onclick="abrirModalDefecto()">+ Registrar defecto</button>
                </div>
            `;
            if (typeof $.fn.DataTable !== 'undefined') { 
                $('#tablaLotesEmpleado').DataTable({ responsive: true }); 
            }
        });
}

// ==========================================
// CARGAR MIS LOTES
// ==========================================
function cargarLotes() {
    fetch('empleado_api.php?action=lotes')
        .then(r => r.json())
        .then(data => {
            document.getElementById('main-content').innerHTML = `
                <div class="top-bar">
                    <div class="page-title">
                        <h1>Mis Lotes Asignados</h1>
                        <p>Listado general de lotes registrados en el sistema</p>
                    </div>
                </div>
                <div class="table-container">
                    <table id="tablaMisLotes" class="display">
                        <thead>
                            <tr>
                                <th>Código Lote</th>
                                <th>Fecha</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.map(l => `
                                <tr>
                                    <td>${l.codigo}</td>
                                    <td>${l.fecha}</td>
                                    <td>${l.cantidad}</td>
                                    <td><span class="badge ${l.estado === 'completado' || l.estado === 'Aprobado' ? 'badge-success' : 'badge-danger'}">${l.estado}</span></td>
                                    <td>
                                        <button class="btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="verDetalleLote(${l.id})">Ver detalle</button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            if (typeof $.fn.DataTable !== 'undefined') { 
                $('#tablaMisLotes').DataTable({ responsive: true }); 
            }
        });
}

// ==========================================
// FUNCIONES AUXILIARES Y MODALES
// ==========================================
function verDetalleLote(id) {
    fetch('empleado_api.php?action=detalle_lote&id=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('detalleLote').innerHTML = `<p>ID: ${d.id}</p><p>Código: ${d.codigo}</p><p>Estado: ${d.estado}</p>`;
            document.getElementById('modalDetalle').style.display = 'flex';
        });
}

function abrirModalDefecto() { 
    document.getElementById('modalDefecto').style.display = 'flex'; 
}

function cerrarModal() { 
    document.getElementById('modalDefecto').style.display = 'none'; 
    document.getElementById('formDefecto').reset(); 
}

function cerrarModalDetalle() { 
    document.getElementById('modalDetalle').style.display = 'none'; 
}

// ==========================================
// EVENTOS DE NAVEGACIÓN Y CARGA INICIAL
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-item[data-page]').forEach(el => {
        el.addEventListener('click', () => {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            el.classList.add('active');
            
            if (el.dataset.page === 'panel') {
                cargarPanel();
            } else if (el.dataset.page === 'lotes') {
                cargarLotes();
            }
        });
    });

    // Cargar el panel por defecto al abrir la vista
    cargarPanel();
});