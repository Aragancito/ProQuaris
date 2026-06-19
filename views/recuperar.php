<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - ProQuaris</title>
    <!-- Fuente Inter para tipografía moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilos específicos para la página de recuperación -->
    <link rel="stylesheet" href="css/recuperar.css">
</head>
<body>
<div class="contenedor-login">
    <div class="tarjeta-login">
        <!-- ========================================== -->
        <!-- LOGO Y TÍTULO                              -->
        <!-- ========================================== -->
        <div class="logo-formulario">ProQuaris</div>
        <h2>Recuperar Contraseña</h2>
        <p class="subtitulo">Ingresa tu correo y te enviaremos un enlace</p>

        <!-- ========================================== -->
        <!-- MENSAJES DE ERROR O ÉXITO                  -->
        <!-- ========================================== -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <!-- Muestra mensaje según el tipo de error recibido -->
                <?php if ($_GET['error'] == 1) echo "❌ El correo no está registrado"; ?>
                <?php if ($_GET['error'] == 2) echo "❌ Error al enviar el correo"; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Se enviaron las instrucciones a tu correo
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULARIO DE RECUPERACIÓN                 -->
        <!-- ========================================== -->
        <!-- Envía el correo al controlador para procesar la solicitud -->
        <form action="../controllers/recuperar_controller.php" method="POST">
            <div class="grupo-input">
                <input type="email" name="correo" placeholder="Correo electrónico" required>
            </div>
            <button type="submit" class="btn-login">Enviar enlace</button>
        </form>

        <!-- ========================================== -->
        <!-- ENLACE DE RETORNO AL LOGIN                 -->
        <!-- ========================================== -->
        <div class="acciones-secundarias">
            <a href="login.php">← Volver a Iniciar Sesión</a>
        </div>
    </div>
</div>
</body>
</html>