<?php
// Se incluye desde OrdenController.php
// Si viene la variable $orden, significa que estamos EDITANDO; de lo contrario, CREANDO.
$esEdicion = isset($orden) && !empty($orden);
$titulo = $esEdicion ? "Editar Orden de Producción" : "Nueva Orden de Producción";
$action = $esEdicion 
    ? "OrdenController.php?accion=editar&id=" . ($orden['idOrden'] ?? 0) 
    : "OrdenController.php?accion=crear";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?> - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Ruta corregida para cargar CSS desde controllers/ -->
    <link rel="stylesheet" href="../views/css/dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR COMPLETO -->
    <aside class="sidebar">
        <div class="sidebar-header"><div class="logo">ProQuaris</div></div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($_SESSION['usuario_rol'] ?? 'Administrador'); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="../views/dashboard.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="OrdenController.php?accion=listar" class="nav-item active">
                <span class="nav-icon">📋</span>
                <span>Órdenes de Producción</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">🏷️</span>
                <span>Lotes y Calidad</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">📦</span>
                <span>Inventario Materia Prima</span>
            </a>
            <a href="#" class="nav-item">
                <span class="nav-icon">👥</span>
                <span>Usuarios y Roles</span>
            </a>
        </nav>
        <div style="padding:20px;">
            <a href="UsuarioController.php?logout=true" class="nav-item" style="color:#FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1><?php echo $titulo; ?></h1>
                <p>Diligencie los campos para gestionar la orden de producción</p>
            </div>
            <a href="OrdenController.php?accion=listar" style="padding:10px 20px; background:#475569; color:white; border-radius:8px; text-decoration:none; font-weight:500;">← Volver al listado</a>
        </div>

        <div class="table-container" style="max-width: 550px; padding: 25px; margin-top: 20px;">
            <form action="<?php echo $action; ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
                
                <!-- PRODUCTO -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Producto:</label>
                    <input type="text" name="producto" required maxlength="30"
                           value="<?php echo htmlspecialchars($orden['producto'] ?? ''); ?>" 
                           placeholder="Ej: Lote Jarabe 500ml"
                           style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                </div>

                <!-- CANTIDAD PLANIFICADA -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Cantidad Planificada:</label>
                    <input type="number" name="cantidadPlanificada" required min="1"
                           value="<?php echo htmlspecialchars($orden['cantidadPlanificada'] ?? ''); ?>" 
                           placeholder="Ej: 500"
                           style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                </div>

                <!-- FECHA INICIO -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Fecha de Inicio:</label>
                    <input type="date" name="fechaInicio" required 
                           value="<?php echo htmlspecialchars($orden['fechaInicio'] ?? date('Y-m-d')); ?>"
                           style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                </div>

                <!-- ESTADO -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label style="font-weight: 600; color: #CBD5E1; font-size: 14px;">Estado:</label>
                    <select name="estado" required style="padding: 10px 14px; border-radius: 8px; border: 1px solid #334155; background: #0F172A; color: #F8FAFC; outline: none;">
                        <?php $est = $orden['estado'] ?? 'Activa'; ?>
                        <option value="Activa" <?php echo $est === 'Activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="En Proceso" <?php echo $est === 'En Proceso' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="Completada" <?php echo $est === 'Completada' ? 'selected' : ''; ?>>Completada</option>
                        <option value="Inactiva" <?php echo $est === 'Inactiva' ? 'selected' : ''; ?>>Inactiva</option>
                    </select>
                </div>

                <!-- BOTÓN DE GUARDAR -->
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