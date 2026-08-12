<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';
?>
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
        <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="nav-item">
            <span class="nav-icon">🏷️</span>
            <span>Lotes y Calidad</span>
        </a>
        <a href="/ProQuaris/controllers/ProductoController.php?accion=listar" class="nav-item">
            <span class="nav-icon">📦</span>
            <span>Inventario y Productos</span>
        </a>
        <a href="/ProQuaris/controllers/UsuarioController.php?accion=listar" class="nav-item">
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