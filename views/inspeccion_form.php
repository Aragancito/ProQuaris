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

$idLote = $_GET['idLote'] ?? null;
if (!$idLote) {
    header("Location: /ProQuaris/controllers/ProduccionController.php?accion=listar");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Inspección - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header"><div class="logo">ProQuaris</div></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="/ProQuaris/views/dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span><span>Inicio (Resumen)</span>
            </a>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" class="nav-item">
                <span class="nav-icon">📋</span><span>Órdenes de Producción</span>
            </a>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="nav-item active">
                <span class="nav-icon">🏷️</span><span>Lotes y Calidad</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">📦</span><span>Inventario Materia Prima</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👥</span><span>Usuarios y Roles</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Registrar Inspección</h1>
                <p>Evaluación de calidad para el Lote #<?php echo htmlspecialchars($idLote); ?></p>
            </div>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="btn-primary" style="background: transparent; border: 1px solid var(--color-borde-claro);">← Volver al listado</a>
        </div>
        
        <div class="form-card" style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: 12px; padding: 30px; max-width: 600px;">
            <form action="/ProQuaris/controllers/CalidadController.php?accion=guardar" method="POST">
                <input type="hidden" name="idLote" value="<?php echo htmlspecialchars($idLote); ?>">

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Resultado de Calidad:</label>
                    <select name="resultado" required style="width: 100%; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white;">
                        <option value="Aprobado">Aprobado</option>
                        <option value="Rechazado">Rechazado</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Motivo / Criterio de Inspección:</label>
                    <select name="motivo" required style="width: 100%; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white;">
                        <option value="Cumple especificaciones estándar">Cumple especificaciones estándar</option>
                        <option value="Material defectuoso o dañado">Material defectuoso o dañado</option>
                        <option value="Fuera de rango de medidas/peso">Fuera de rango de medidas/peso</option>
                        <option value="Problemas de empaque o presentación">Problemas de empaque o presentación</option>
                        <option value="Otro motivo técnico">Otro motivo técnico</option>
                    </select>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Observaciones:</label>
                    <textarea name="observaciones" placeholder="Ingrese detalles adicionales..." style="width: 100%; height: 100px; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white; resize: none;"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; cursor: pointer;">Guardar Inspección</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>