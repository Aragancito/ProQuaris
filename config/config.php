<?php

// Credenciales para el servidor SMTP de Gmail
define("HOST", "smtp.gmail.com");
define("USERNAME", "juandatamayo14@gmail.com");
define("PASSWORD", "lmhrmixvhjsuwjps"); // Contraseña de aplicación de 16 dígitos sin espacios

// Protocolo de cifrado para la conexión SMTP
define("SMTP_SECURE", "TLS");

// Tiempo de vida del token de recuperación en segundos (por defecto 1 minuto)
// Se usa para controlar la expiración de los enlaces de restablecimiento de contraseña
// Actualmente configurado a 60 segundos (1 minuto) para pruebas rápidas
define("TIEMPO_VIDA", time() + 180);