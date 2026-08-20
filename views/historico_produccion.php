<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}
$historicos = $historicos ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Producción - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>
<div class="dashboard-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Histórico de Órdenes Completadas</h1>
                <p style="color: #64748B; font-size: 14px; margin-top: 4px;">Trazabilidad final para analítica y descarga de informes</p>
            </div>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver a Órdenes</a>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px; background: #0F172A; border-radius: 12px; border: 1px solid #1E293B;">
            <table id="tablaHistorico" class="display" style="width: 100%; color: #CBD5E1;">
                <thead>
                    <tr style="color: #94A3B8; text-transform: uppercase; font-size: 12px;">
                        <th>ID Historial</th>
                        <th>Ref. Orden</th>
                        <th>Producto</th>
                        <th>Planificadas</th>
                        <th>Correctas</th>
                        <th>Defectuosas</th>
                        <th>Impacto Neto</th>
                        <th>Fecha Cierre</th>
                        <th style="text-align: center;">Acciones PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historicos)): ?>
                        <?php foreach ($historicos as $h): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($h['idHistorico']); ?></td>
                            <td>
                                <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo htmlspecialchars($h['idLote'] ?? 0); ?>" style="color: #38BDF8; text-decoration: none; font-weight: bold;" title="Ver historial de inspecciones de este lote">
                                    Orden #<?php echo htmlspecialchars($h['idOrden']); ?> 🔍
                                </a>
                            </td>
                            <td style="font-weight: bold; color: #FFF;"><?php echo htmlspecialchars($h['productoNombre']); ?></td>
                            <td><?php echo htmlspecialchars($h['cantidadPlanificada']); ?> uds</td>
                            <td style="color: #34D399; font-weight: bold;"><?php echo htmlspecialchars($h['unidadesCorrectas']); ?> uds</td>
                            <td style="color: #F87171;"><?php echo htmlspecialchars($h['unidadesDefectuosas']); ?> uds</td>
                            <td style="font-weight: bold; color: #38BDF8;">$<?php echo number_format($h['impactoFinancieroNeto'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($h['fechaCierre']); ?></td>
                            <td style="text-align: center;">
                                <button onclick="window.print();" style="padding: 6px 12px; background: #6366F1; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;">📥 Descargar PDF</button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
$(document).ready(function() {
    $('#tablaHistorico').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'pdf', text: '📄 Exportar a PDF', className: 'btn btn-primary' },
            { extend: 'excel', text: '📊 Exportar a Excel', className: 'btn btn-success' }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 10
    });
});
</script>
</body>
</html>