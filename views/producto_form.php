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
    <title>Registrar Producto y Receta - ProQuaris</title>
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body style="background: #0F172A; color: white; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background: #1E293B; padding: 30px; border-radius: 12px; border: 1px solid #334155;">
        <h2>Registrar Nuevo Producto y su Receta</h2>
        <form action="/ProQuaris/controllers/ProductoController.php?accion=crear" method="POST" style="margin-top: 20px;">
            <label>Nombre del Producto:</label>
            <input type="text" name="nombre" required style="width:100%; padding: 10px; margin: 5px 0 15px 0; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">

            <label>Descripción:</label>
            <textarea name="descripcion" style="width:100%; height: 80px; padding: 10px; margin: 5px 0 15px 0; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;"></textarea>

            <label>Precio de Venta:</label>
            <input type="number" step="0.01" name="precioVenta" required style="width:100%; padding: 10px; margin: 5px 0 20px 0; background: #0F172A; border: 1px solid #334155; color: white; border-radius: 6px;">

            <h3 style="border-top: 1px solid #334155; padding-top: 15px; margin-bottom: 10px;">Insumos necesarios (Receta)</h3>
            <p style="font-size: 13px; color: #94A3B8; margin-bottom: 15px;">Indica la cantidad requerida de cada materia prima para fabricar una unidad de este producto:</p>

            <?php if (!empty($insumos)): ?>
                <?php foreach ($insumos as $ins): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; background: #0F172A; padding: 10px; border-radius: 6px;">
                        <span><?php echo htmlspecialchars($ins['insumo']); ?> (Stock actual: <?php echo $ins['stockActual']; ?>)</span>
                        <input type="number" step="0.01" name="insumos[<?php echo $ins['idinventario']; ?>]" placeholder="Cantidad" style="width: 120px; padding: 6px; background: #1E293B; border: 1px solid #334155; color: white; border-radius: 4px;">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #F87171;">No hay insumos registrados en el inventario todavía. Agrega algunos en la tabla inventario primero.</p>
            <?php endif; ?>

            <button type="submit" style="width: 100%; padding: 12px; margin-top: 20px; background: #6366F1; border: none; color: white; font-weight: bold; border-radius: 8px; cursor: pointer;">Guardar Producto y Receta</button>
        </form>
        <br>
        <a href="/ProQuaris/controllers/ProductoController.php?accion=listar" style="color: #818CF8; text-decoration: none;">← Volver al listado</a>
    </div>
</body>
</html>