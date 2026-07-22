<?php
// Este archivo se incluye desde OrdenController.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Órdenes de Producción - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../views/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <!-- Estilos de refinamiento visual para DataTables -->
    <style>
        /* Ajuste fino del buscador de DataTables */
        .dataTables_wrapper .dataTables_filter input {
            background-color: #0F172A !important;
            border: 1px solid #334155 !important;
            color: #F8FAFC !important;
            border-radius: 8px !important;
            padding: 6px 12px !important;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            color: #94A3B8 !important;
            font-weight: 500;
        }

        /* Estilo de la tabla */
        table.dataTable tbody tr {
            background-color: transparent !important;
            border-bottom: 1px solid #1E293B !important;
            transition: background 0.2s ease;
        }
        table.dataTable tbody tr:hover {
            background-color: #1E293B !important;
        }
        table.dataTable thead th {
            border-bottom: 2px solid #334155 !important;
            color: #94A3B8 !important;
            font-size: 13px !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Botones de acción SVG */
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            margin-right: 4px;
            text-decoration: none;
            transition: 0.2s ease;
        }
        .btn-edit { background: rgba(99, 102, 241, 0.15); color: #818CF8; }
        .btn-edit:hover { background: #6366F1; color: #FFF; }
        .btn-delete { background: rgba(239, 68, 68, 0.15); color: #F87171; }
        .btn-delete:hover { background: #EF4444; color: #FFF; }

        /* Paginación */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #94A3B8 !important;
            border-radius: 6px !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #6366F1 !important;
            color: white !important;
            border: none !important;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header"><div class="logo">ProQuaris</div></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['usuario_rol'] ?? 'Administrador'); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="../views/dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="OrdenController.php?accion=listar" class="nav-item active">
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
            <a href="UsuarioController.php?logout=true" class="nav-item" style="color:#FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Órdenes de Producción</h1>
                <p style="color: #64748B; font-size: 14px; margin-top: 4px;">Gestión general de las órdenes de planta</p>
            </div>
            <a href="OrdenController.php?accion=crear" class="btn btn-primary" style="padding:10px 20px; background:#6366F1; color:white; border-radius:8px; text-decoration:none; font-weight:600;">+ Nueva Orden</a>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px; background: #0F172A; border-radius: 12px; border: 1px solid #1E293B;">
            <table id="tablaOrdenes" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ordenes)): ?>
                        <?php foreach ($ordenes as $o): ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($o['idOrden']); ?></strong></td>
                            <td style="font-weight: 500; color: #F8FAFC;"><?php echo htmlspecialchars($o['producto']); ?></td>
                            <td><?php echo htmlspecialchars($o['cantidadPlanificada']); ?> uds</td>
                            <td><?php echo htmlspecialchars($o['fechaInicio']); ?></td>
                            <td>
                                <span class="badge <?php echo ($o['estado'] === 'Activa' || $o['estado'] === 'En Proceso') ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo htmlspecialchars($o['estado']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <!-- Botón Editar (Icono SVG) -->
                                <a href="OrdenController.php?accion=editar&id=<?php echo $o['idOrden']; ?>" class="btn-action btn-edit" title="Editar orden">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <!-- Botón Eliminar (Icono SVG) -->
                                <a href="OrdenController.php?accion=eliminar&id=<?php echo $o['idOrden']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar esta orden?')" title="Eliminar orden">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaOrdenes').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'pdf', text: '📄 PDF', className: 'btn btn-primary' },
            { extend: 'excel', text: '📊 Excel', className: 'btn btn-success' }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10
    });
});
</script>
</body>
</html>