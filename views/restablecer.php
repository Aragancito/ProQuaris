<?php
$token = $_GET['token'] ?? '';
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/restablecer.css">
</head>
<body>
<div class="contenedor-login">
    <div class="tarjeta-login">
        <div class="logo-formulario">ProQuaris</div>
        <h2>Nueva Contraseña</h2>
        <p class="subtitulo">Ingresa tu nueva contraseña</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php if ($_GET['error'] == 1) echo "❌ El enlace ha expirado"; ?>
                <?php if ($_GET['error'] == 2) echo "❌ Las contraseñas no coinciden"; ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/restablecer_controller.php" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="grupo-input">
                <input type="password" name="contrasena" placeholder="Nueva contraseña" required>
            </div>
            <div class="grupo-input">
                <input type="password" name="contrasena_confirm" placeholder="Confirmar contraseña" required>
            </div>
            <button type="submit" class="btn-login">Guardar contraseña</button>
        </form>

        <div class="acciones-secundarias">
            <a href="login.php">← Volver a Iniciar Sesión</a>
        </div>
    </div>
</div>
</body>
</html>