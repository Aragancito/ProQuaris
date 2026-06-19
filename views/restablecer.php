<?php
// ==========================================
// CAPTURA Y VALIDACIÓN DEL TOKEN
// ==========================================
// ABSTRACCIÓN: El token se captura de la URL sin exponer cómo se genera o valida
$token = $_GET['token'] ?? '';

// POLIMORFISMO: Si no hay token, el flujo cambia redirigiendo al login
if (empty($token)) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - ProQuaris</title>
    <!-- Fuente Inter para tipografía moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilos específicos para el restablecimiento -->
    <link rel="stylesheet" href="css/restablecer.css">
</head>
<body>
<div class="contenedor-login">
    <div class="tarjeta-login">
        <!-- ========================================== -->
        <!-- LOGO Y TÍTULO                              -->
        <!-- ========================================== -->
        <div class="logo-formulario">ProQuaris</div>
        <h2>Nueva Contraseña</h2>
        <p class="subtitulo">Ingresa tu nueva contraseña</p>

        <!-- ========================================== -->
        <!-- MENSAJES DE ERROR                          -->
        <!-- ========================================== -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <!-- POLIMORFISMO: Muestra mensaje diferente según el tipo de error -->
                <?php if ($_GET['error'] == 1) echo "❌ El enlace ha expirado"; ?>
                <?php if ($_GET['error'] == 2) echo "❌ Las contraseñas no coinciden"; ?>
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULARIO DE NUEVA CONTRASEÑA             -->
        <!-- ========================================== -->
        <!-- ABSTRACCIÓN: El token se envía oculto, el usuario no necesita verlo
             ni saber cómo se valida en el controlador -->
        <form action="../controllers/restablecer_controller.php" method="POST">
            <!-- htmlspecialchars previene inyección XSS en el token -->
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="grupo-input">
                <input type="password" name="contrasena" placeholder="Nueva contraseña" required>
            </div>
            <!-- ENCAPSULAMIENTO: La confirmación de contraseña protege al usuario
                 de errores tipográficos sin exponer la lógica de validación -->
            <div class="grupo-input">
                <input type="password" name="contrasena_confirm" placeholder="Confirmar contraseña" required>
            </div>
            <button type="submit" class="btn-login">Guardar contraseña</button>
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