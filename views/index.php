<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - ProQuaris</title>
    <link rel="stylesheet" href="../css/estilos-globales.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <div class="contenedor-login">
        <div class="tarjeta-login">
            
            <div class="logo-formulario">ProQuaris</div>
            
            <h2>Iniciar sesión</h2>
            <p class="subtitulo">Usa tu cuenta de la empresa</p>

            <form id="formulario-login" action="../controllers/UsuarioController.php" method="POST">
                <div class="grupo-input">
                    <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required>
                </div>

                <div class="grupo-input">
                    <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña" required>
                </div>

                <button type="submit" class="btn-login">Siguiente</button>
            </form>

            <div class="acciones-secundarias">
                <a href="recuperar.php" class="link-recuperar">¿Olvidaste tu contraseña?</a>
            </div>

        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>