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
    <style>
        /* ==========================================
           IMPRESIÓN / EXPORTAR A PDF (window.print)
           Técnica: se oculta TODO, y solo se hace visible de nuevo
           la tabla, sacándola del layout con position:absolute para
           que no herede el sidebar ni el flex del dashboard.
           ========================================== */
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm;
            }

            body * {
                visibility: hidden;
            }

            #tablaHistorico, #tablaHistorico * {
                visibility: visible;
            }

            #tablaHistorico {
                position: absolute;
                top: 0;
                left: 0;
                width: 100% !important;
                border-collapse: collapse;
            }

            /* Fuerza texto negro sobre blanco: los estilos inline de la
               tabla (colores claros pensados para fondo oscuro) se ven
               con !important para no depender de tocar cada <td>. */
            #tablaHistorico, #tablaHistorico * {
                color: #000 !important;
                background: #fff !important;
            }

            #tablaHistorico th, #tablaHistorico td {
                border: 1px solid #333 !important;
                padding: 6px 8px !important;
                font-size: 11px !important;
            }

            /* Columnas/elementos que no tienen sentido en el papel
               (botón de descargar, acciones) */
        }

        /* Botones REALES de exportar (los que genera DataTables Buttons
           arriba de la tabla). Antes eran poco visibles; ahora quedan
           claros y coherentes con la paleta del sistema. */
        .dt-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
        }

        .dt-button {
            padding: 10px 18px !important;
            border-radius: 8px !important;
            border: none !important;
            font-weight: bold !important;
            font-size: 13px !important;
            cursor: pointer;
            color: white !important;
            background: var(--color-principal) !important;
        }

        .dt-button:hover {
            background: var(--color-principal-hover) !important;
        }

        @media print {
            .no-imprimir, .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_paginate,
            .dataTables_wrapper .dataTables_info {
                display: none !important;
            }
        }
    </style>
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
                        <th>Creado por</th>
                        <th>Planificadas</th>
                        <th>Correctas</th>
                        <th>Defectuosas</th>
                        <th>Impacto Neto</th>
                        <th>Fecha Cierre</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historicos)): ?>
                        <?php foreach ($historicos as $h): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($h['idHistorico']); ?></td>
                            <td>
                                <a href="/ProQuaris/controllers/CalidadController.php?accion=historial&idLote=<?php echo htmlspecialchars($h['idLote'] ?? 0); ?>" style="color: #38BDF8; text-decoration: none; font-weight: bold;" title="Ver historial de inspecciones de este lote">
                                    Orden #<?php echo htmlspecialchars($h['numeroPlanta'] ?? $h['idOrden']); ?> 🔍
                                </a>
                            </td>
                            <td style="font-weight: bold; color: #FFF;"><?php echo htmlspecialchars($h['productoNombre']); ?></td>
                            <td>
                                <?php $rolCreadorHist = $h['creadorRol'] ?? ''; ?>
                                <span style="color: #CBD5E1;"><?php echo htmlspecialchars(trim($h['creadorNombre'] ?? '') ?: '—'); ?></span>
                                <?php if (!empty($rolCreadorHist)): ?>
                                    <span style="display:inline-block; margin-left:6px; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:bold; background: rgba(148,163,184,0.15); color: <?php echo ($rolCreadorHist === 'Administrador') ? '#A855F7' : '#38BDF8'; ?>;">
                                        <?php echo htmlspecialchars($rolCreadorHist); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($h['cantidadPlanificada']); ?> uds</td>
                            <td style="color: #34D399; font-weight: bold;"><?php echo htmlspecialchars($h['unidadesCorrectas']); ?> uds</td>
                            <td style="color: #F87171;"><?php echo htmlspecialchars($h['unidadesDefectuosas']); ?> uds</td>
                            <td style="font-weight: bold; color: #38BDF8;">$<?php echo number_format($h['impactoFinancieroNeto'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($h['fechaCierre']); ?></td>

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