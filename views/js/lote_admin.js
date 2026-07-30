document.addEventListener('DOMContentLoaded', () => {
    // Manejo de navegación lateral en el panel de administración
    document.querySelectorAll('.sidebar .nav-item[data-page]').forEach(el => {
        el.addEventListener('click', () => {
            document.querySelectorAll('.sidebar .nav-item').forEach(nav => nav.classList.remove('active'));
            el.classList.add('active');
            
            const pagina = el.dataset.page;
            
            if (pagina === 'inicio') {
                cargarPanelAdmin();
            } else if (pagina === 'lotes') {
                cargarLotesAdmin();
            } else if (pagina === 'ordenes') {
                cargarOrdenesAdmin();
            } else if (pagina === 'inventario') {
                cargarInventarioAdmin();
            } else if (pagina === 'usuarios') {
                cargarUsuariosAdmin();
            }
        });
    });

    // Cargar la vista inicial por defecto (Inicio / Resumen)
    cargarPanelAdmin();
});

// ==========================================
// 1. VISTA DE INICIO (RESUMEN / PANEL)
// ==========================================
function cargarPanelAdmin() {
    const mainContent = document.getElementById('main-content');
    mainContent.innerHTML = `
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Control Principal</h1>
                <p>Gestión general de métricas y lotes de planta</p>
            </div>
            <button class="btn-primary" onclick="alert('Acción rápida de orden')">+ Nueva Orden</button>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde);">
                <div style="font-size: 14px; color: var(--color-texto-secundario, #aaa);">Órdenes Activas</div>
                <div style="font-size: 28px; font-weight: bold; margin: 10px 0; color: var(--color-texto);">12</div>
                <div style="font-size: 12px; color: #2ecc71;">↑ +3 vs mes anterior</div>
            </div>
            <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde);">
                <div style="font-size: 14px; color: var(--color-texto-secundario, #aaa);">Lotes Producidos hoy</div>
                <div style="font-size: 28px; font-weight: bold; margin: 10px 0; color: var(--color-texto);">45</div>
                <div style="font-size: 12px; color: #2ecc71;">↑ +8% vs ayer</div>
            </div>
            <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde);">
                <div style="font-size: 14px; color: var(--color-texto-secundario, #aaa);">Alertas de Calidad</div>
                <div style="font-size: 28px; font-weight: bold; margin: 10px 0; color: var(--color-texto);">3</div>
                <div style="font-size: 12px; color: #e74c3c;">↓ -2 vs semana pasada</div>
            </div>
        </div>
    `;
}

// ==========================================
// 2. VISTA DE LOTES Y CALIDAD
// ==========================================
function cargarLotesAdmin() {
    const mainContent = document.getElementById('main-content');
    mainContent.innerHTML = `
        <div class="top-bar">
            <div class="page-title">
                <h1>Gestión de Lotes y Calidad</h1>
                <p>Control y seguimiento de los lotes producidos en planta</p>
            </div>
            <button class="btn-primary" onclick="abrirModalNuevoLote()">+ Nuevo Lote</button>
        </div>
        <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde);">
            <table id="tablaLotesAdmin" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Código Lote</th>
                        <th>Orden ID</th>
                        <th>Fecha Creación</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargan dinámicamente vía AJAX -->
                </tbody>
            </table>
        </div>
    `;

    // Petición al backend para extraer los datos de la tabla `lote`
    fetch('admin_api.php?action=obtener_lotes')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tablaLotesAdmin tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px;">No hay lotes registrados.</td></tr>`;
                return;
            }

            data.forEach(lote => {
                const codigoLote = `LOT-2026-${String(lote.idLote).padStart(3, '0')}`;
                const esAprobado = lote.estado.toLowerCase() === 'aprobado';
                const badgeClass = esAprobado ? 'background: rgba(46, 204, 113, 0.15); color: #2ecc71; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;' : 'background: rgba(231, 76, 60, 0.15); color: #e74c3c; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="font-weight: 600;">${codigoLote}</td>
                    <td>#${lote.FK_ordenId}</td>
                    <td>${lote.fechaCreacion}</td>
                    <td>${lote.cantidad} uds</td>
                    <td><span style="${badgeClass}">${lote.estado}</span></td>
                    <td>
                        <button onclick="editarLote(${lote.idLote})" title="Editar" style="background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px;">✏️</button>
                        <button onclick="eliminarLote(${lote.idLote})" title="Eliminar" style="background: none; border: none; cursor: pointer; font-size: 16px; padding: 4px; margin-left: 6px;">🗑️</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Inicializar DataTable con idioma en español
            if ($.fn.DataTable.isDataTable('#tablaLotesAdmin')) {
                $('#tablaLotesAdmin').DataTable().destroy();
            }
            $('#tablaLotesAdmin').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50]
            });
        })
        .catch(error => {
            console.error('Error al cargar los lotes:', error);
        });
}

// ==========================================
// 3. OTRAS VISTAS (PLACEHOLDERS)
// ==========================================
function cargarOrdenesAdmin() {
    document.getElementById('main-content').innerHTML = `
        <div class="top-bar"><div class="page-title"><h1>Órdenes de Producción</h1><p>Administración y seguimiento de órdenes</p></div></div>
        <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde); color: var(--color-texto);">
            <p>Módulo de Órdenes de Producción listo para conectar con su respectiva tabla.</p>
        </div>
    `;
}

function cargarInventarioAdmin() {
    document.getElementById('main-content').innerHTML = `
        <div class="top-bar"><div class="page-title"><h1>Inventario de Materia Prima</h1><p>Control de stock y suministros</p></div></div>
        <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde); color: var(--color-texto);">
            <p>Módulo de Inventario en desarrollo.</p>
        </div>
    `;
}

function cargarUsuariosAdmin() {
    document.getElementById('main-content').innerHTML = `
        <div class="top-bar"><div class="page-title"><h1>Gestión de Usuarios y Roles</h1><p>Control de accesos del personal</p></div></div>
        <div style="background: var(--color-tarjeta); padding: 20px; border-radius: 12px; border: 1px solid var(--color-borde); color: var(--color-texto);">
            <p>Módulo de Usuarios en desarrollo.</p>
        </div>
    `;
}

// ==========================================
// 4. ACCIONES DE LOTES (MODALES / CRUD)
// ==========================================
function abrirModalNuevoLote() {
    alert("Función para abrir el modal de crear nuevo lote.");
}

function editarLote(id) {
    alert("Editar lote con ID: " + id);
}

function eliminarLote(id) {
    if (confirm("¿Estás seguro de eliminar este lote de la base de datos?")) {
        alert("Lote eliminado (simulación)");
    }
}