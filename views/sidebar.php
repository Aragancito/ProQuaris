<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';

// Texto de ayuda general del sidebar: cambia según el rol, porque un
// Empleado no ve (ni debe ver) el mismo menú que un Administrador.
if ($rolUsuario === 'Administrador') {
    $ayudaSidebar = "Este menú es tu punto de partida para TODO el sistema.\n"
        . "<b>Inicio</b>: resumen con tus KPIs y gráficas de la planta.\n"
        . "<b>Órdenes de Producción</b>: crear y controlar órdenes de fabricación.\n"
        . "<b>Lotes y Calidad</b>: seguimiento de lotes e inspecciones de calidad.\n"
        . "<b>Inventario y Productos</b>: catálogo de productos, recetas y stock de insumos.\n"
        . "<b>Usuarios y Roles</b>: aprobar o desvincular empleados de tu planta.";
} else {
    $ayudaSidebar = "Este menú es tu punto de partida en ProQuaris.\n"
        . "<b>Inicio</b>: resumen con las órdenes, lotes y alertas de tu planta.\n"
        . "<b>Órdenes de Producción</b>: crear y ver las órdenes de fabricación.\n"
        . "<b>Lotes y Calidad</b>: registrar inspecciones de calidad sobre los lotes.\n"
        . "<b>Usuarios y Roles</b>: aquí te vinculas a la planta de tu Administrador.\n"
        . "\"Inventario y Productos\" no aparece para tu rol: solo el Administrador gestiona el catálogo y el stock.";
}
?>
<aside class="sidebar">
    <div class="sidebar-header" style="display:flex; align-items:center; justify-content:space-between;">
        <div class="logo">ProQuaris</div>
        <button type="button" class="btn-ayuda"
                data-ayuda="<?php echo htmlspecialchars($ayudaSidebar); ?>"
                data-ayuda-titulo="¿Qué hace cada módulo?"
                title="Ayuda del menú">?</button>
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
        <?php if ($rolUsuario === 'Administrador'): ?>
        <a href="/ProQuaris/controllers/ProductoController.php?accion=listar" class="nav-item">
            <span class="nav-icon">📦</span>
            <span>Inventario y Productos</span>
        </a>
        <?php endif; ?>
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
<script src="/ProQuaris/views/js/animaciones.js?v=<?php echo time(); ?>" defer></script>