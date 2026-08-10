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
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
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
            <a href="/ProQuaris/views/dashboard.php" class="nav-item"><span class="nav-icon">📊</span>Inicio</a>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" class="nav-item"><span class="nav-icon">📋</span>Órdenes</a>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="nav-item"><span class="nav-icon">🏷️</span>Lotes y Calidad</a>
            <a href="/ProQuaris/controllers/ProductoController.php?accion=listar" class="nav-item active"><span class="nav-icon">📦</span>Productos y Recetas</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <h1>Catálogo de Productos y Recetas</h1>
            <a href="/ProQuaris/controllers/ProductoController.php?accion=crear" class="btn-primary" style="padding:10px 20px; background:#6366F1; color:white; border-radius:8px; text-decoration:none; font-weight:600;">+ Nuevo Producto</a>
        </div>
        
        <div class="table-container" style="margin-top: 20px; padding: 20px; background: #0F172A; border-radius: 12px; border: 1px solid #1E293B;">
            <table style="width: 100%; border-collapse: collapse; color: white;">
                <thead>
                    <tr style="border-bottom: 2px solid #334155; text-align: left;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">NOMBRE</th>
                        <th style="padding: 10px;">DESCRIPCIÓN</th>
                        <th style="padding: 10px;">PRECIO VENTA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <tr style="border-bottom: 1px solid #1E293B;">
                                <td style="padding: 10px;">#<?php echo $p['idProducto']; ?></td>
                                <td style="padding: 10px; font-weight: 500;"><?php echo htmlspecialchars($p['nombre']); ?></td>
                                <td style="padding: 10px; color: #94A3B8;"><?php echo htmlspecialchars($p['descripcion']); ?></td>
                                <td style="padding: 10px;">$<?php echo number_format($p['precioVenta'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; padding: 20px; color: #94A3B8;">No hay productos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>