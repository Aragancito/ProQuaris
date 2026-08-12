<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Materia Prima - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Gestión de Materia Prima e Insumos</h1>
                <p>Control de stock, costos unitarios y unidades de medida</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-top: 20px;">
            <!-- Formulario de Registro -->
            <div style="background: #1E293B; padding: 20px; border-radius: 12px; border: 1px solid #334155; height: fit-content;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 16px;">Registrar Insumo</h3>
                <form action="/ProQuaris/controllers/InventarioController.php?accion=crear" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Código / ID Insumo:</label>
                        <input type="text" name="idinventario" required placeholder="Ej: INS-001" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Nombre del Insumo:</label>
                        <input type="text" name="insumo" required placeholder="Ej: Pegamento / Tornillo" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Stock Actual:</label>
                        <input type="number" step="0.01" name="stockActual" required placeholder="0.00" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Costo Unitario ($):</label>
                        <input type="number" step="0.01" name="costoUnitario" required placeholder="0.00" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Unidad de Medida:</label>
                        <input type="text" name="unidadMedida" required placeholder="Ej: ml, gramos, unidades" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="font-size: 13px; color: #CBD5E1;">Ubicación:</label>
                        <input type="text" name="ubicacion" placeholder="Almacén Principal" style="width: 100%; padding: 8px; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">
                    </div>
                    <button type="submit" style="width: 100%; padding: 10px; background: #6366F1; border: none; color: white; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 5px;">Guardar Insumo</button>
                </form>
            </div>

            <!-- Tabla de Listado -->
            <div style="background: #0F172A; padding: 20px; border-radius: 12px; border: 1px solid #1E293B;">
                <h3 style="color: #F8FAFC; margin-bottom: 15px; font-size: 16px;">Listado de Materia Prima Registrada</h3>
                <table style="width: 100%; border-collapse: collapse; color: white; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid #334155; text-align: left;">
                            <th style="padding: 8px;">ID</th>
                            <th style="padding: 8px;">Insumo</th>
                            <th style="padding: 8px;">Stock</th>
                            <th style="padding: 8px;">Costo U.</th>
                            <th style="padding: 8px;">Medida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($insumos)): ?>
                            <?php foreach ($insumos as $ins): ?>
                                <tr style="border-bottom: 1px solid #1E293B;">
                                    <td style="padding: 8px;">#<?php echo htmlspecialchars($ins['idinventario']); ?></td>
                                    <td style="padding: 8px; font-weight: 500; color: #F8FAFC;"><?php echo htmlspecialchars($ins['insumo']); ?></td>
                                    <td style="padding: 8px;"><?php echo $ins['stockActual']; ?></td>
                                    <td style="padding: 8px;">$<?php echo number_format($ins['costoUnitario'], 2); ?></td>
                                    <td style="padding: 8px; color: #94A3B8;"><?php echo htmlspecialchars($ins['unidadMedida'] ?? 'unidad'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #94A3B8;">No hay materias primas registradas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>