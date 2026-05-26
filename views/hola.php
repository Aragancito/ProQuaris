<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["usuario_nombre"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido</title>
</head>
<body>
    <h1>¡Hola Mundo!</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["usuario_nombre"]); ?></p>
    <a href="../controllers/UsuarioController.php?logout=true">Cerrar Sesión</a>
</body>
</html>