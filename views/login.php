<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProQuaris - Iniciar Sesión</title>
    <link rel="stylesheet" href="/PROQUARIS/views/css/estilos-globales.css">
    <link rel="stylesheet" href="/PROQUARIS/views/css/login.css">
    <style>
        /* Ajuste de diseño para el contenedor de enlaces secundarios */
        .acciones-secundarias {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        /* Clase para el estilo visual del enlace de registro */
        .btn-registro-link {
            color: #7c4dff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        /* Método de estilo al pasar el cursor por encima (hover) */
        .btn-registro-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="contenedor-login">
        <div class="tarjeta-login">
            <div class="logo-formulario">ProQuaris</div>
            <h2>Iniciar sesión</h2>
            <p class="subtitulo">Usa tu cuenta institucional de la empresa</p>
            
            <form action="../controllers/UsuarioController.php" method="POST">
                <div class="grupo-input">
                    <input type="email" name="correo" placeholder="Correo electrónico" required>
                </div>
                
                <div class="grupo-input">
                    <input type="password" name="contrasena" placeholder="Contraseña" required>
                </div>

                <button type="submit" class="btn-login">Siguiente</button>
            </form>

            <div class="acciones-secundarias">
                <a href="recuperar.php" class="link-recuperar">¿Olvidaste tu contraseña?</a>
                <a href="registro.php" class="btn-registro-link">Registrar Nuevo Personal →</a>
            </div>
        </div>
    </div>

</body>
</html>