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
                <?php if ($_GET['error'] == 1) echo "❌ Todos los campos requeridos deben ser llenados"; ?>
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
                <input type="password" name="contraseña" placeholder="Contraseña" required>
            </div>
            
            <div class="grupo-input">
                <input type="text" name="empresa" id="input-empresa" placeholder="Nombre de Empresa / Planta (Opcional)">
            </div>

            <div class="grupo-input">
                <select name="rol" id="select-rol" required>
                    <option value="">Seleccione el Rol del Usuario...</option>
                    <option value="Administrador">Administrador</option>
                    <option value="Empleado">Operario</option>
                </select>
            </div>

            <button type="submit" class="btn-login">Registrar</button>
        </form>

        <div class="acciones-secundarias">
            <a href="login.php">← Volver a Iniciar Sesión</a>
        </div>
    </div>
</div>

<script>
    // Lógica para hacer la empresa obligatoria solo si es Administrador
    document.getElementById('select-rol').addEventListener('change', function() {
        const inputEmpresa = document.getElementById('input-empresa');
        if (this.value === 'Administrador') {
            inputEmpresa.required = true;
            inputEmpresa.placeholder = "Nombre de Empresa / Planta (Obligatorio)";
            inputEmpresa.style.border = "1px solid #3B82F6"; // Resalta ligeramente el borde
        } else {
            inputEmpresa.required = false;
            inputEmpresa.placeholder = "Nombre de Empresa / Planta (Opcional)";
            inputEmpresa.style.border = "none";
        }
    });
</script>
</body>
</html>