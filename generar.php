<?php
// Define aquí la contraseña que deseas encriptar
$contrasenaPlana = "123456"; 

// Generar el hash seguro utilizando el algoritmo estándar de PHP
$hashGenerado = password_hash($contrasenaPlana, PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Hash - ProQuaris</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; padding: 40px; text-align: center; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); display: inline-block; max-width: 600px; word-break: break-all; }
        code { background: #eee; padding: 15px; display: block; margin-top: 15px; border-radius: 4px; font-size: 16px; color: #d63384; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Generador de Hash para Credenciales</h2>
        <p>Contraseña en texto plano: <strong><?php echo htmlspecialchars($contrasenaPlana); ?></strong></p>
        <p>Copia el siguiente hash generado para asignarlo en tu base de datos:</p>
        <code><?php echo htmlspecialchars($hashGenerado); ?></code>
    </div>
</body>
</html>