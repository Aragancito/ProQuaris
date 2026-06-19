<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - ProQuaris</title>
    <!-- Fuente Inter para tipografía moderna -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Estilos específicos para el registro -->
    <link rel="stylesheet" href="css/registro.css">
</head>
<body>
<div class="contenedor-login">
    <div class="tarjeta-login">
        <!-- ========================================== -->
        <!-- LOGO Y TÍTULO                              -->
        <!-- ========================================== -->
        <div class="logo-formulario">ProQuaris</div>
        <h2>Registrar Nuevo Personal</h2>
        <p class="subtitulo">Asigne credenciales y el rol correspondiente</p>

        <!-- ========================================== -->
        <!-- MENSAJES DE ERROR                          -->
        <!-- ========================================== -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <!-- POLIMORFISMO: Muestra mensaje diferente según el tipo de error -->
                <?php if ($_GET['error'] == 1) echo "❌ Todos los campos son requeridos"; ?>
                <?php if ($_GET['error'] == 2) echo "❌ Error al registrar el usuario"; ?>
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULARIO DE REGISTRO                     -->
        <!-- ========================================== -->
        <!-- POLIMORFISMO: El campo oculto 'accion' permite al controlador
             diferenciar entre registro y login desde el mismo endpoint -->
        <form action="../controllers/UsuarioController.php" method="POST">
            <input type="hidden" name="accion" value="registrar">

            <!-- ABSTRACCIÓN: Los campos del formulario ocultan cómo se procesarán
                 los datos en el controlador y modelo -->
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

            <!-- POLIMORFISMO: El select permite diferentes roles que cambiarán
                 el comportamiento del sistema -->
            <div class="grupo-input">
                <select name="rol" required>
                    <option value="">Seleccione el Rol del Usuario...</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Empleado">Empleado</option>
                </select>
            </div>

            <button type="submit" class="btn-login">Registrar</button>
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