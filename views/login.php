<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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

    <!-- Script que limpia el candado cuando llegas al login exitosamente -->
    <script>
        localStorage.removeItem('sesion_cerrada');
    </script>
</body>

<script type="module">
    import Chatbot from "https://cdn.jsdelivr.net/npm/flowise-embed/dist/web.js"
    Chatbot.init({
        chatflowid: "50de36ef-a39c-4cfa-a795-e95952c78ebe",
        apiHost: "https://cloud.flowiseai.com",
    })
</script>
</html>