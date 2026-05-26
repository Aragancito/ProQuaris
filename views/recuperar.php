<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - ProQuaris</title>
    <link rel="stylesheet" href="../css/estilos-globales.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>

    <div class="contenedor-login">
        <div class="tarjeta-login">
            
            <div class="logo-formulario">ProQuaris</div>
            
            <h2>Recuperación de cuenta</h2>
            <p class="subtitulo">Introduce tu correo electrónico institucional para restablecer tu contraseña</p>

            <form id="formulario-recuperar" action="../controllers/UsuarioController.php" method="POST">
                <div class="grupo-input">
                    <input type="email" name="correo-recuperar" id="correo-recuperar" placeholder="Correo electrónico" required>
                </div>

                <button type="submit" class="btn-login">Enviar instrucciones</button>
            </form>

            <div class="acciones-secundarias">
                <a href="login.php" class="link-recuperar">Volver al inicio de sesión</a>
            </div>

        </div>
    </div>

    <script src="../js/login.js"></script>
</body>
</html>