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
$idLote = $_GET['idLote'] ?? 'N/A';
$inspecciones = $inspecciones ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Inspecciones - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Historial de Calidad</h1>
                <p>Auditoría inmutable de inspecciones realizadas al Lote #<?php echo htmlspecialchars($idLote); ?></p>
            </div>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="btn-primary" style="padding: 10px 20px; background: #475569; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">← Volver al listado</a>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="color: #F8FAFC; font-size: 16px;">Registros de Auditoría del Lote</h3>
                <a href="/ProQuaris/controllers/CalidadController.php?accion=registrar&idLote=<?php echo $idLote; ?>" style="padding: 8px 16px; background: #6366F1; color: white; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: bold;">+ Nueva Inspección</a>
            </div>

            <table class="display" style="width: 100%; border-collapse: collapse; text-align: left; color: #CBD5E1; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #334155; color: #94A3B8; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 12px;">Fecha y Hora / Orden</th>
                        <th style="padding: 12px;">Producto / Resultado</th>
                        <th style="padding: 12px; text-align: center;">Unidades (Correctas / Defectuosas)</th>
                        <th style="padding: 12px;">Impacto Financiero (Dinámico vs Base Activa)</th>
                        <th style="padding: 12px;">Inspector y Observaciones</th>
                        <th style="padding: 12px; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inspecciones)): ?>
                        <?php foreach ($inspecciones as $row): 
                            $unidadesBaseInspeccion = intval($row['unidades_base_inspeccion'] ?? 0);
                            $defectuosas = intval($row['unidades_defectuosas'] ?? 0);
                            $correctas = max(0, $unidadesBaseInspeccion - $defectuosas);
                            $impactoNeto = floatval($row['impacto_financiero'] ?? 0);
                            
                            $signo = ($impactoNeto > 0) ? '+' : (($impactoNeto < 0) ? '-' : '');
                            $colorImpacto = ($impactoNeto >= 0) ? '#34D399' : '#F87171';
                            
                            $precioVentaRef = floatval($row['precioUnitarioProducto'] ?? 0);
                            $valorBaseReferencia = $unidadesBaseInspeccion * $precioVentaRef;
                            $idRI = $row['idRI'] ?? $row['id'] ?? 0;
                        ?>
                            <tr style="border-bottom: 1px solid #1E293B;">
                                <td style="padding: 14px;">
                                    <div style="font-weight: 600; color: #F8FAFC;"><?php echo htmlspecialchars($row['fecha'] ?? ''); ?></div>
                                    <div style="font-size: 12px; color: #38BDF8; margin-top: 2px;">Orden #<?php echo htmlspecialchars($row['numeroOrden'] ?? 'N/A'); ?> (Lote #<?php echo htmlspecialchars($idLote); ?>)</div>
                                </td>
                                <td style="padding: 14px;">
                                    <div style="font-weight: bold; color: #F1F5F9;"><?php echo htmlspecialchars($row['producto_nombre'] ?? 'Producto'); ?></div>
                                    <?php 
                                        $res = $row['resultado'] ?? 'N/A';
                                        $badgeColor = ($res === 'Aprobado') ? '#34D399' : (($res === 'Rechazado') ? '#F87171' : '#FBBF24');
                                    ?>
                                    <span style="display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: rgba(52, 211, 153, 0.15); color: <?php echo $badgeColor; ?>;">
                                        <?php echo htmlspecialchars($res); ?>
                                    </span>
                                </td>
                                <td style="padding: 14px; text-align: center;">
                                    <span style="color: #34D399; font-weight: bold;"><?php echo $correctas; ?> correctas</span><br>
                                    <span style="color: #F87171; font-size: 12px;"><?php echo $defectuosas; ?> defectuosas</span>
                                </td>
                                <td style="padding: 14px;">
                                    <div style="font-weight: bold; font-size: 15px; color: <?php echo $colorImpacto; ?>;">
                                        <?php echo $signo . ' $' . number_format(abs($impactoNeto), 0, ',', '.'); ?>
                                    </div>
                                    <div style="font-size: 11px; color: #94A3B8; margin-top: 3px;">
                                        Base activa: $<?php echo number_format($valorBaseReferencia, 0, ',', '.'); ?> (<?php echo $unidadesBaseInspeccion; ?> uds disponibles)
                                    </div>
                                </td>
                                <td style="padding: 14px; color: #94A3B8; font-size: 13px; max-width: 300px;">
                                    <strong style="color: #CBD5E1;"><?php echo htmlspecialchars($row['inspectorNombre'] ?? 'Admin'); ?></strong><br>
                                    <?php echo htmlspecialchars($row['observaciones'] ?? ''); ?>
                                </td>
                                <td style="padding: 14px; text-align: center; display: flex; gap: 8px; justify-content: center; align-items: center;">
                                    <a href="/ProQuaris/controllers/CalidadController.php?accion=editar&id=<?php echo $idRI; ?>" style="color: #38BDF8; text-decoration: none; font-weight: bold; font-size: 13px;">Editar</a>
                                    <span style="color: #334155;">|</span>
                                    <a href="/ProQuaris/controllers/CalidadController.php?accion=eliminar&id=<?php echo $idRI; ?>&idLote=<?php echo $idLote; ?>" onclick="return confirm('¿Estás seguro de eliminar este registro y restaurar el stock?');" style="color: #F87171; text-decoration: none; font-weight: bold; font-size: 13px;">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: #64748B;">No hay registros de inspección para este lote.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>