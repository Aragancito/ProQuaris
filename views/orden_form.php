<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

require_once __DIR__ . '/../models/ProductoModel.php';
$prodModel = new ProductoModel();
$listaProductos = $prodModel->obtenerTodos();

$esEdicion = isset($orden) && !empty($orden);
$titulo = $esEdicion ? "Editar Orden de Producción" : "Nueva Orden de Producción";
$action = $esEdicion
    ? "/ProQuaris/controllers/OrdenController.php?accion=editar&id=" . ($orden['idOrden'] ?? 0)
    : "/ProQuaris/controllers/OrdenController.php?accion=crear";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1><?php echo $titulo; ?></h1>
                    <p>Diligencie los campos para gestionar la orden de producción</p>
                </div>
                <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver al listado</a>
            </div>

            <div class="table-container" style="max-width: 550px; padding: 25px; margin-top: 20px;">
                <form action="<?php echo $action; ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Seleccionar Producto:</label>
                        <?php $productoActualId = $orden['idProducto'] ?? ''; ?>
                        <select name="idProducto" required style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                            <option value="">-- Seleccione un producto del catálogo --</option>
                            <?php foreach ($listaProductos as $prod): ?>
                                <option value="<?php echo $prod['idProducto']; ?>" <?php echo ($productoActualId == $prod['idProducto']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($prod['nombre']); ?> (Precio Venta: $<?php echo number_format($prod['precioVenta'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Cantidad Planificada:</label>
                        <input type="number" name="cantidadPlanificada" required min="1"
                            value="<?php echo htmlspecialchars($orden['cantidadPlanificada'] ?? ''); ?>"
                            placeholder="Ej: 500"
                            style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Fecha de Inicio:</label>
                        <input type="date" name="fechaInicio" required
                            value="<?php echo htmlspecialchars($orden['fechaInicio'] ?? date('Y-m-d')); ?>"
                            style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                    </div>

                    <!-- Si es creación mandamos 'Activa' por defecto de forma oculta; si es edición no modificamos el estado desde aquí -->
                    <?php if (!$esEdicion): ?>
                        <input type="hidden" name="estado" value="Activa">
                    <?php endif; ?>

                    <div style="margin-top: 10px;">
                        <button type="submit" style="width: 100%; padding: 12px; background: #6366F1; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s;">
                            <?php echo $esEdicion ? "Guardar Cambios" : "Crear Orden"; ?>
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</body>
</html>