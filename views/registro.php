<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - ProQuaris</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/registro.css">
</head>
<body>
<div class="contenedor-login">
    <div class="tarjeta-login">
        <div class="logo-formulario">ProQuaris</div>
        <h2>Registrar Nuevo Personal</h2>
        <p class="subtitulo">Asigne credenciales y el rol correspondiente</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php if ($_GET['error'] == 1) echo "❌ Todos los campos son requeridos"; ?>
                <?php if ($_GET['error'] == 2) echo "❌ Error al registrar el usuario"; ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/UsuarioController.php" method="POST">
            <input type="hidden" name="accion" value="registrar">

            <div class="grupo-input">
                <input type="text" name="nombre" placeholder="Nombres" required>
            </div>
            <div class="grupo-input">
                <input type="text" name="apellido" placeholder="Apellidos" required>
            </div>
            <div class="grupo-input">
                <input type="email" name="correo" placeholder="Correo electrónico" required>
            </div>
            <div class="grupo-input">
                <input type="password" name="contrasena" placeholder="Contraseña" required>
            </div>

            <div class="grupo-input">
                <select name="rol" required>
                    <option value="">Seleccione el Rol del Usuario...</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Empleado">Empleado</option>
                </select>
            </div>

            <button type="submit" class="btn-login">Registrar</button>
        </form>

        <div class="acciones-secundarias">
            <a href="login.php">← Volver a Iniciar Sesión</a>
        </div>
    </div>
</div>
</body>
</html>