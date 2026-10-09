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
                <?php if (!empty($_GET['msg'])): ?>
                    ❌ <?php echo htmlspecialchars($_GET['msg']); ?>
                <?php elseif ($_GET['error'] == 1): ?>
                    ❌ Todos los campos requeridos deben ser llenados
                <?php elseif ($_GET['error'] == 2): ?>
                    ❌ Error al registrar el usuario
                <?php endif; ?>
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
                <input type="password" id="campoContrasena" name="contraseña" placeholder="Contraseña"
                       required minlength="8"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}"
                       title="Mínimo 8 caracteres, con mayúscula, minúscula y número">
                <small id="hintContrasena" style="display:block; margin-top:4px; font-size:12px; color:#94A3B8;">
                    Mínimo 8 caracteres, con mayúscula, minúscula y número.
                </small>
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

    // Indicador en vivo de fortaleza de contraseña (sin librerías: JS plano).
    // El servidor vuelve a validar esto igual al enviar; esto es solo para
    // que el usuario vea el error ANTES de darle a "Registrar", no después.
    const campoContrasena = document.getElementById('campoContrasena');
    const hintContrasena = document.getElementById('hintContrasena');

    campoContrasena.addEventListener('input', function () {
        const valor = campoContrasena.value;
        const cumple = valor.length >= 8
            && /[A-Z]/.test(valor)
            && /[a-z]/.test(valor)
            && /[0-9]/.test(valor);

        if (valor.length === 0) {
            hintContrasena.style.color = '#94A3B8';
            hintContrasena.textContent = 'Mínimo 8 caracteres, con mayúscula, minúscula y número.';
        } else if (cumple) {
            hintContrasena.style.color = '#34D399';
            hintContrasena.textContent = '✓ Contraseña segura.';
        } else {
            hintContrasena.style.color = '#F87171';
            hintContrasena.textContent = '✗ Falta: mínimo 8 caracteres, mayúscula, minúscula y número.';
        }
    });
</script>
</body>
</html>