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
    <title>Productos y Recetas - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Catálogo de Productos y Recetas</h1>
                <p>Gestión de artículos, plusvalía e insumos directos</p>
            </div>
            <a href="/ProQuaris/controllers/ProductoController.php?accion=crear" class="btn-primary" style="padding:10px 20px; background:#6366F1; color:white; border-radius:8px; text-decoration:none; font-weight:600;">+ Nuevo Producto</a>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px; background: #0F172A; border-radius: 12px; border: 1px solid #1E293B;">
            <table style="width: 100%; border-collapse: collapse; color: white;">
                <thead>
                    <tr style="border-bottom: 2px solid #334155; text-align: left;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">NOMBRE</th>
                        <th style="padding: 10px;">DESCRIPCIÓN</th>
                        <th style="padding: 10px;">PLUSVALÍA</th>
                        <th style="padding: 10px;">PRECIO VENTA</th>
                        <th style="padding: 10px; text-align: center;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <tr style="border-bottom: 1px solid #1E293B;">
                                <td style="padding: 10px;">#<?php echo $p['idProducto']; ?></td>
                                <td style="padding: 10px; font-weight: 500;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td style="padding: 10px; color: #94A3B8;"><?php echo htmlspecialchars($p['descripcion']); ?></td>
                                <td style="padding: 10px; color: #34D399;">$<?php echo number_format($p['plusvalia'] ?? 0, 2); ?></td>
                                <td style="padding: 10px; font-weight: bold;">$<?php echo number_format($p['precioVenta'], 2); ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <a href="/ProQuaris/controllers/ProductoController.php?accion=editar&id=<?php echo $p['idProducto']; ?>" style="background: rgba(99, 102, 241, 0.15); color: #818CF8; padding: 6px 12px; border-radius: 6px; text-decoration: none; margin-right: 6px; font-size: 13px;">Editar</a>
                                    <a href="/ProQuaris/controllers/ProductoController.php?accion=eliminar&id=<?php echo $p['idProducto']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este producto?')" style="background: rgba(239, 68, 68, 0.15); color: #F87171; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 13px;">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #94A3B8;">No hay productos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>