<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProQuaris - Registro de Personal</title>
    <link rel="stylesheet" href="/PROQUARIS/views/css/estilos-globales.css">
    <link rel="stylesheet" href="/PROQUARIS/views/css/login.css">
</head>
<body>

    <div class="contenedor-login">
        <div class="tarjeta-login" style="max-width: 500px;">
            <div class="logo-formulario">ProQuaris</div>
            <h2>Registrar Nuevo Personal</h2>
            <p class="subtitulo">Asigne credenciales y el rol correspondiente</p>
            
            <form action="../controllers/UsuarioController.php" method="POST">
                <input type="hidden" name="accion" value="registrar">

                <div class="grupo-input">
                    <input type="text" name="nombre" placeholder="Nombres" required>
                </div>
                <div class="grupo-input">
                    <input type="text" name="apellido" placeholder="Apellidos" required>
                </div>
                <div class="grupo-input">
                    <input type="email" name="correo" placeholder="Correo electrónico institucional" required>
                </div>
                <div class="grupo-input">
                    <input type="password" name="contrasena" placeholder="Contraseña temporal" required>
                </div>

                <div class="grupo-input">
                    <select name="rol" id="select-rol" required onchange="conmutarCamposRol()" style="width: 100%; padding: 12px; border-radius: 6px; background: #1e1e1e; color: #fff; border: 1px solid #333;">
                        <option value="">Seleccione el Rol del Usuario...</option>
                        <option value="Administrador">Administrador</option>
                        <option value="Empleado">Empleado (Planta/Operario)</option>
                    </select>
                </div>

                <div id="campos-admin" style="display: none; margin-top: 15px;">
                    <div class="grupo-input">
                        <input type="number" name="nivel_acceso" placeholder="Nivel de Acceso (Ej: 1, 2, 3)">
                    </div>
                </div>

                <div id="campos-empleado" style="display: none; margin-top: 15px;">
                    <div class="grupo-input">
                        <input type="text" name="puesto" placeholder="Puesto de trabajo (Ej: Analista de Calidad)">
                    </div>
                    <div class="grupo-input">
                        <input type="text" name="departamento" placeholder="Departamento (Ej: Producción)">
                    </div>
                </div>

                <button type="submit" class="btn-login" style="margin-top: 20px;">Guardar Registro</button>
            </form>

            <div class="acciones-secundarias" style="margin-top: 15px; text-align: center;">
                <a href="login.php" class="link-recuperar">← Volver al Login</a>
            </div>
        </div>
    </div>

    <script>
        function conmutarCamposRol() {
            var rol = document.getElementById('select-rol').value;
            document.getElementById('campos-admin').style.display = (rol === 'Administrador') ? 'block' : 'none';
            document.getElementById('campos-empleado').style.display = (rol === 'Empleado') ? 'block' : 'none';
        }
    </script>
</body>
</html>