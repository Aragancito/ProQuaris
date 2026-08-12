<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Global de Lotes y Calidad - ProQuaris</title>
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
                <h1>Reporte Global de Lotes y Calidad</h1>
                <p>Trazabilidad de lotes heredados de las órdenes de producción</p>
            </div>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px; background: #0F172A; border-radius: 12px; border: 1px solid #1E293B;">
            <table id="tablaLotesCalidad" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID LOTE</th>
                        <th>REF. ORDEN</th>
                        <th>PRODUCTO</th>
                        <th>CANTIDAD</th>
                        <th>FECHA</th>
                        <th>ESTADO</th>
                        <th>CALIDAD</th>
                        <th style="text-align: right;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lotes)): ?>
                        <?php foreach ($lotes as $lote): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($lote['idLote'] ?? ''); ?></strong></td>
                                <td><span style="background: #2196F3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Orden #<?php echo htmlspecialchars($lote['FK_ordenId'] ?? ''); ?></span></td>
                                <td style="font-weight: 500; color: #F8FAFC;"><?php echo htmlspecialchars($lote['producto'] ?? 'Sin asignar'); ?></td>
                                <td><?php echo htmlspecialchars($lote['cantidad'] ?? ''); ?> uds</td>
                                <td><?php echo htmlspecialchars($lote['fechaCreacion'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                        $estado = $lote['estado'] ?? 'Activa';
                                        $badgeClass = ($estado === 'Activa' || $estado === 'En Proceso') ? 'badge-success' : 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($estado); ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $calidad = $lote['resultadoCalidad'] ?? 'Sin inspección';
                                        $calidadClass = ($calidad === 'Aprobado') ? 'badge-success' : (($calidad === 'Rechazado') ? 'badge-danger' : 'badge-warning');
                                    ?>
                                    <span class="badge <?php echo $calidadClass; ?>"><?php echo htmlspecialchars($calidad); ?></span>
                                </td>
                                <td style="text-align: right; display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo $lote['idLote']; ?>" class="btn-action" title="Ver Historial de Calidad" style="color: #2196F3;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                    </a>
                                    <a href="/ProQuaris/controllers/CalidadController.php?accion=registrar&idLote=<?php echo $lote['idLote']; ?>" class="btn-action" title="Registrar Inspección" style="color: #4CAF50;">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                    <a href="/ProQuaris/controllers/ProduccionController.php?accion=eliminar&id=<?php echo $lote['idLote']; ?>" class="btn-action btn-delete" title="Eliminar Lote" onclick="return confirm('¿Seguro que deseas eliminar este lote?')">
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
<script>
$(document).ready(function() {
    $('#tablaLotesCalidad').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        "pageLength": 10
    });
});
</script>
</body>
</html>