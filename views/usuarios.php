<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Administrador';
$idUsuarioActual = $_SESSION['usuario_id'] ?? null;

require_once __DIR__ . '/../config/conexion.php';
$db = Conexion::conectar();

$stmtAdmins = $db->query("SELECT id, nombre, apellido, empresa FROM usuario WHERE rol = 'Administrador'");
$listaAdmins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

$personalVinculado = [];
$solicitudesPendientes = [];
$miAdminAsignado = null; 

if ($rolUsuario === 'Administrador') {
    // Buscar los Activos
    $stmtPersonal = $db->prepare("SELECT * FROM usuario WHERE admin_asignado = ? AND estado = 'Activo'");
    $stmtPersonal->execute([$idUsuarioActual]);
    $personalVinculado = $stmtPersonal->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar los Pendientes
    $stmtPendientes = $db->prepare("SELECT * FROM usuario WHERE admin_asignado = ? AND estado = 'Pendiente'");
    $stmtPendientes->execute([$idUsuarioActual]);
    $solicitudesPendientes = $stmtPendientes->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Consultamos directamente los campos del usuario actual para ver su estado y su admin asignado
    $stmtMiAdmin = $db->prepare("SELECT o.estado AS mi_estado, u.id, u.nombre, u.apellido, u.empresa FROM usuario o LEFT JOIN usuario u ON o.admin_asignado = u.id WHERE o.id = ?");
    $stmtMiAdmin->execute([$idUsuarioActual]);
    $resultado = $stmtMiAdmin->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        $miAdminAsignado = $resultado;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios y Roles - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ProQuaris/views/css/estilos-globales.css?v=<?php echo time(); ?>">
    <style>
        .panel-seccion { background: #0F172A; padding: 25px; border-radius: 12px; border: 1px solid #1E293B; margin-bottom: 25px; color: #CBD5E1; transition: all 0.35s ease; }
        .panel-seccion:hover { border-color: rgba(59, 130, 246, 0.4); box-shadow: 0 10px 30px -10px rgba(59, 130, 246, 0.3); transform: translateY(-3px); }
        .panel-seccion h3 { color: #F8FAFC; margin-bottom: 15px; font-size: 18px; }
        .tabla-pro { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .tabla-pro th, .tabla-pro td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #1E293B; font-size: 14px; }
        .tabla-pro th { color: #94A3B8; text-transform: uppercase; font-size: 12px; }
        .btn-accion { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; text-decoration: none; cursor: pointer; border: none; display: inline-block; margin-right: 5px; }
        .btn-primario { background: #3B82F6; color: white; }
        .btn-aprobar { background: #34D399; color: #0F172A; }
        .btn-eliminar { background: #EF4444; color: white; }
        .form-select-custom { background: #1E293B; color: #CBD5E1; border: 1px solid #334155; padding: 10px; border-radius: 6px; width: 100%; max-width: 500px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="dashboard-container">
    
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Gestión de Usuarios y Roles</h1>
                <p>Control de personal y asignación de supervisores de planta</p>
            </div>
        </div>

        <?php if ($rolUsuario === 'Administrador'): ?>
            <!-- SECCIÓN: SOLICITUDES PENDIENTES -->
            <div class="panel-seccion">
                <h3>📥 Solicitudes Pendientes de Aprobación</h3>
                <?php if (empty($solicitudesPendientes)): ?>
                    <p style="color: #94A3B8;">No hay solicitudes pendientes en este momento.</p>
                <?php else: ?>
                    <table class="tabla-pro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudesPendientes as $sp): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sp['nombre'] . ' ' . $sp['apellido']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sp['correo']); ?></td>
                                <td style="text-align: center;">
                                    <a href="/ProQuaris/controllers/UsuarioController.php?accion=aprobar&id=<?php echo $sp['id']; ?>" class="btn-accion btn-aprobar">✔ Aprobar</a>
                                    <a href="/ProQuaris/controllers/UsuarioController.php?accion=eliminar&id=<?php echo $sp['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Está seguro de rechazar esta solicitud?');">✖ Rechazar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- SECCIÓN: PERSONAL ACTIVO -->
            <div class="panel-seccion">
                <h3>👥 Personal de Planta Activo</h3>
                <?php if (empty($personalVinculado)): ?>
                    <p style="color: #94A3B8;">Aún no tienes operarios activos en tu planta.</p>
                <?php else: ?>
                    <table class="tabla-pro">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personalVinculado as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['correo']); ?></td>
                                <td><span style="color: #34D399; font-weight: bold;">Activo</span></td>
                                <td style="text-align: center;">
                                    <!-- ACCIÓN MODIFICADA: Desvincula en lugar de borrar la cuenta -->
                                    <a href="/ProQuaris/controllers/UsuarioController.php?accion=eliminar&id=<?php echo $p['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Está seguro de quitar a este operario de su planta? Su cuenta no se borrará, solo quedará desvinculada.');">🔌 Desvincular</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- VISTA OPERARIO -->
            <div class="panel-seccion">
                <h3>🏢 Configuración de Administrador Supervisor</h3>
                
                <?php if (is_array($miAdminAsignado) && !empty($miAdminAsignado['id'])): ?>
                    
                    <?php if (($miAdminAsignado['mi_estado'] ?? '') === 'Pendiente'): ?>
                        <div style="background-color: rgba(56, 189, 248, 0.1); border: 1px solid #38BDF8; padding: 15px; border-radius: 8px; margin-bottom: 15px; color: #BAE6FD;">
                            <h4 style="margin-bottom: 5px;">⏳ Estado: Pendiente de Aprobación</h4>
                            <p style="margin: 0; font-size: 14px;">Has solicitado unirte a la planta de: <strong><?php echo htmlspecialchars($miAdminAsignado['nombre'] . ' ' . $miAdminAsignado['apellido']); ?></strong>. En cuanto el Administrador apruebe tu solicitud en su panel, se habilitarán tus funciones operativas.</p>
                        </div>
                    <?php else: ?>
                        <p style="color: #34D399; margin-bottom: 15px; font-weight: bold;">
                            ✅ Estás aprobado y vinculado a la planta de: <?php echo htmlspecialchars($miAdminAsignado['nombre'] . ' ' . $miAdminAsignado['apellido']); ?> <?php echo !empty($miAdminAsignado['empresa']) ? ' - ' . htmlspecialchars($miAdminAsignado['empresa']) : ''; ?>
                        </p>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <p style="color: #F87171; margin-bottom: 15px;">
                        ⚠️ No tienes un administrador asignado. Selecciona tu planta de la lista.
                    </p>
                <?php endif; ?>

                <form action="/ProQuaris/controllers/UsuarioController.php" method="POST">
                    <input type="hidden" name="accion" value="asignar_admin">
                    <label style="display: block; font-size: 13px; color: #94A3B8; margin-bottom: 5px;">Seleccionar Planta / Administrador (o Cambiar):</label>
                    <select name="admin_asignado" class="form-select-custom" required>
                        <option value="">-- Selecciona una planta --</option>
                        <?php foreach ($listaAdmins as $adm): ?>
                            <?php 
                                $esSeleccionado = (is_array($miAdminAsignado) && $miAdminAsignado['id'] == $adm['id']);
                                $textoEmpresa = !empty($adm['empresa']) ? ' (' . $adm['empresa'] . ')' : '';
                            ?>
                            <option value="<?php echo $adm['id']; ?>" <?php echo $esSeleccionado ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($adm['nombre'] . ' ' . $adm['apellido'] . $textoEmpresa); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <br>
                    <button type="submit" class="btn-accion btn-primario" style="margin-top: 15px; padding: 10px 20px;">Enviar Solicitud de Asignación</button>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>