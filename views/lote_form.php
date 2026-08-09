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

// Verificamos si llegó un ID por la URL para saber si es edición o creación
$idLote = $_GET['id'] ?? null;
$loteActual = null;

$conexion = new mysqli("localhost", "root", "", "proquaris_bd");

if ($idLote) {
    $stmt = $conexion->prepare("SELECT * FROM lote WHERE idLote = ?");
    $stmt->bind_param("i", $idLote);
    $stmt->execute();
    $resultadoLote = $stmt->get_result();
    $loteActual = $resultadoLote->fetch_assoc();
}

// Variables dinámicas según la acción
$esEdicion = ($loteActual !== null);
$accionForm = $esEdicion ? 'actualizar' : 'guardar';
$tituloPagina = $esEdicion ? "Editar Lote #" . $loteActual['idLote'] : "Crear Nuevo Lote";
$textoBoton = $esEdicion ? "Actualizar Lote" : "Guardar Lote";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $tituloPagina; ?> - ProQuaris</title>
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
                <h1><?php echo $tituloPagina; ?></h1>
                <p>Gestiona los datos del lote de producción</p>
            </div>
        </div>
        
        <div class="table-container" style="max-width: 600px; margin: 0 auto;">
            <form action="/ProQuaris/controllers/ProduccionController.php?accion=<?php echo $accionForm; ?>" method="POST">
                
                <?php if ($esEdicion): ?>
                    <input type="hidden" name="idLote" value="<?php echo $loteActual['idLote']; ?>">
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Seleccionar Orden de Producción</label>
                    <select name="orden_id" required style="width: 100%; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white;">
                        <option value="">Seleccione una orden...</option>
                        <?php
                        $resultadoOrdenes = $conexion->query("SELECT idOrden, producto FROM ordenproduccion");
                        while ($orden = $resultadoOrdenes->fetch_assoc()) {
                            $selected = ($esEdicion && $orden['idOrden'] == $loteActual['FK_ordenId']) ? 'selected' : '';
                            echo "<option value='{$orden['idOrden']}' {$selected}>Orden #{$orden['idOrden']} - {$orden['producto']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Cantidad (Unidades)</label>
                    <input type="number" name="cantidad" required min="1" 
                           value="<?php echo $esEdicion ? $loteActual['cantidad'] : ''; ?>"
                           style="width: 100%; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white;" 
                           placeholder="Ej. 500">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; color: var(--color-texto-mutado); font-weight: 500;">Estado</label>
                    <select name="estado" style="width: 100%; padding: 12px; border-radius: 8px; background: var(--color-fondo); border: 1px solid var(--color-borde-claro); color: white;">
                        <option value="Activa" <?php echo ($esEdicion && $loteActual['estado'] === 'Activa') ? 'selected' : ''; ?>>Activa</option>
                        <option value="En Proceso" <?php echo ($esEdicion && $loteActual['estado'] === 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="Aprobado" <?php echo ($esEdicion && $loteActual['estado'] === 'Aprobado') ? 'selected' : ''; ?>>Aprobado</option>
                        <option value="Rechazado" <?php echo ($esEdicion && $loteActual['estado'] === 'Rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                    <a href="/ProQuaris/controllers/ProduccionController.php?accion=listar" style="padding: 10px 20px; background: transparent; border: 1px solid var(--color-borde-claro); color: white; border-radius: 8px; text-decoration: none;">Cancelar</a>
                    <button type="submit" class="btn-primary"><?php echo $textoBoton; ?></button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>