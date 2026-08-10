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
    <aside class="sidebar">
        <div class="sidebar-header"><div class="logo">ProQuaris</div></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="/ProQuaris/views/dashboard.php" class="nav-item"><span class="nav-icon">📊</span>Inicio (Resumen)</a>
            <a href="/ProQuaris/controllers/OrdenController.php?accion=listar" class="nav-item"><span class="nav-icon">📋</span>Órdenes de Producción</a>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="nav-item active"><span class="nav-icon">🏷️</span>Lotes y Calidad</a>
            <a href="#" class="nav-item"><span class="nav-icon">📦</span>Inventario Materia Prima</a>
            <a href="#" class="nav-item"><span class="nav-icon">👥</span>Usuarios y Roles</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Historial de Calidad</h1>
                <p>Auditoría inmutable de inspecciones realizadas al Lote #<?php echo htmlspecialchars($idLote); ?></p>
            </div>
            <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" class="btn-primary" style="background: transparent; border: 1px solid var(--color-borde-claro);">← Volver al listado</a>
        </div>
        
        <div class="table-container">
            <table class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>FECHA</th>
                        <th>RESULTADO</th>
                        <th>INSPECTOR</th>
                        <th>OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inspecciones)): ?>
                        <?php foreach ($inspecciones as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['fecha'] ?? ''); ?></td>
                                <td>
                                    <?php 
                                        $res = $row['resultado'] ?? 'N/A';
                                        $badge = ($res === 'Aprobado') ? 'badge-success' : 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($res); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['inspectorNombre'] ?? 'Admin'); ?></td>
                                <td style="max-width: 400px;"><?php echo htmlspecialchars($row['observaciones'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">No hay registros de inspección para este lote.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>