<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lotes y Calidad - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ProQuaris</div>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="/ProQuaris/views/dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" class="nav-item">
                <span class="nav-icon">📋</span>
                <span>Órdenes de Producción</span>
            </a>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="nav-item active">
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
            <a href="/ProQuaris/views/logout.php" class="nav-item" style="color:#FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Lotes y Calidad</h1>
                <p>Gestión y control de lotes de producción generados por planta</p>
            </div>
            <button id="btnNuevoLote" class="btn-primary">+ Nuevo Lote</button>
        </div>
        
        <div class="table-container">
            <table id="tablaLotesCalidad" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>PRODUCTO</th>
                        <th>CANTIDAD</th>
                        <th>FECHA</th>
                        <th>ESTADO</th>
                        <th style="text-align: right;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lotes)): ?>
                        <?php foreach ($lotes as $lote): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($lote['idLote'] ?? ''); ?></strong></td>
                                <td style="font-weight: 500; color: var(--color-texto);"><?php echo htmlspecialchars($lote['producto'] ?? 'Sin asignar'); ?></td>
                                <td><?php echo htmlspecialchars($lote['cantidad'] ?? ''); ?> uds</td>
                                <td><?php echo htmlspecialchars($lote['fechaCreacion'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                        $estado = $lote['estado'] ?? 'Activa';
                                        $badgeClass = ($estado === 'Aprobado' || $estado === 'Activa') ? 'badge-success' : (($estado === 'Rechazado') ? 'badge-danger' : 'badge-warning');
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($estado); ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn-action btn-edit btn-editar" data-id="<?php echo $lote['idLote']; ?>" title="Editar">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <button class="btn-action btn-delete btn-eliminar" data-id="<?php echo $lote['idLote']; ?>" title="Eliminar">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
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
    $('#tablaLotesCalidad').DataTable({
        dom: 'frtip',
        pageLength: 10
    });
});
</script>
</body>
</html>