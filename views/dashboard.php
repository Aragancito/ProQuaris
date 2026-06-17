<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rolUsuario = $_SESSION['usuario_rol'] ?? 'Empleado';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">ProQuaris</div>
        </div>
        <div class="user-info">
            <div class="user-name"><?php echo htmlspecialchars($nombreUsuario); ?></div>
            <div class="user-role"><?php echo htmlspecialchars($rolUsuario); ?></div>
        </div>
        <nav class="nav-menu">
            <a href="#" class="nav-item active">
                <span class="nav-icon">📊</span>
                <span>Inicio (Resumen)</span>
            </a>
            <a href="#" class="nav-item">
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
        <div style="padding: 20px;">
            <a href="../controllers/UsuarioController.php?logout=true" class="nav-item" style="color: #FF5252;">
                <span class="nav-icon">🚪</span>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Panel de Control Principal</h1>
                <p><?php echo date('F, Y'); ?></p>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Órdenes Activas</div>
                <div class="kpi-value">12</div>
                <div class="kpi-trend trend-up">↑ +3 vs mes anterior</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Lotes Producidos hoy</div>
                <div class="kpi-value">45</div>
                <div class="kpi-trend trend-up">↑ +8% vs ayer</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Alertas de Calidad</div>
                <div class="kpi-value">3</div>
                <div class="kpi-trend trend-down">↓ -2 vs semana pasada</div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>Últimos Lotes Verificados</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Código Lote</th>
                        <th>Fecha</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>LOT-2026-001</td>
                        <td>21/05/2026</td>
                        <td>500 uds</td>
                        <td><span class="badge badge-success">Aprobado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-002</td>
                        <td>21/05/2026</td>
                        <td>350 uds</td>
                        <td><span class="badge badge-danger">Rechazado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-003</td>
                        <td>20/05/2026</td>
                        <td>280 uds</td>
                        <td><span class="badge badge-success">Aprobado</span></td>
                    </tr>
                    <tr>
                        <td>LOT-2026-004</td>
                        <td>20/05/2026</td>
                        <td>420 uds</td>
                        <td><span class="badge badge-warning">En revisión</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/06/17/03/20260617035538-JZYJE355.js" defer></script>
    
</html>