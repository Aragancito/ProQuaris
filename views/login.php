<?php
session_start();
if (isset($_SESSION['usuario_nombre'])) {
    if ($_SESSION['usuario_rol'] === 'Administrador') {
        header("Location: dashboard.php");
    } else {
        header("Location: dashboard_empleado.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>ProQuaris</h1>
                <p>Gestión de Producción y Calidad</p>
            </div>

            <h2 class="titulo">Bienvenido</h2>
            <p class="subtitulo">Ingresa tus credenciales</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php 
                        switch($_GET['error']) {
                            case 1: echo "❌ Usuario o contraseña incorrectos"; break;
                            case 2: echo "❌ Usuario no encontrado"; break;
                            default: echo "❌ Error al iniciar sesión";
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                <div class="success-message">
                    ✅ ¡Registro exitoso! Ahora puedes iniciar sesión
                </div>
            <?php endif; ?>

            <form action="../controllers/UsuarioController.php" method="POST">
                <div class="input-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" placeholder="tu@empresa.com" required>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <input type="password" name="contraseña" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>

            <div class="links">
                <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
                <a href="registro.php">Registrar nuevo personal →</a>
            </div>
        </div>
    </div>
</body>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/06/17/03/20260617035538-JZYJE355.js" defer></script>
    
</html>