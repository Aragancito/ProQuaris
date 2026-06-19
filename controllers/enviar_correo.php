<?php
// ==========================================
// IMPORTACIÓN DE CLASES DE PHPMailer
// ==========================================
// ABSTRACCIÓN: PHPMailer es una librería que abstrae todo el protocolo SMTP.
// El desarrollador solo usa métodos simples como setFrom(), addAddress(), send().
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../vendor/autoload.php';  // Composer autoload
require_once '../config/config.php';   // Constantes SMTP

// ==========================================
// FUNCIÓN DE ENVÍO DE CORREOS
// ==========================================
// ABSTRACCIÓN: Esta función oculta toda la configuración SMTP,
// autenticación, redacción del correo y manejo de errores.
// El usuario solo llama a enviarCorreo($correo, $nombre, $token).
function enviarCorreo($correo, $nombreReceptor, $token) {
    // ABSTRACCIÓN: PHPMailer oculta los detalles de conexión SMTP
    $mail = new PHPMailer(true);

    try {
        // ==========================================
        // CONFIGURACIÓN DE CONEXIÓN SMTP
        // ==========================================
        // POLIMORFISMO: PHPMailer usa diferentes métodos (isSMTP, isMail, isQmail)
        // para adaptarse a diferentes transportes de correo.
        $mail->SMTPDebug = 2;                      
        $mail->isSMTP();                           
        
        // Se usa IP directa de Gmail para evitar bloqueos por DNS en entornos locales
        $mail->Host       = '74.125.142.108'; 
        $mail->SMTPAuth   = true;                  
        $mail->Username   = USERNAME;   
        $mail->Password   = PASSWORD;   

        // ==========================================
        // CONFIGURACIÓN DE PUERTO Y CIFRADO
        // ==========================================
        // ENCAPSULAMIENTO: PHPMailer protege la configuración interna
        // y solo expone métodos para modificarla.
        $mail->SMTPSecure = 'tls';                 
        $mail->Port       = 587;                   

        // ==========================================
        // OPCIONES SSL (XAMPP/WAMP)
        // ==========================================
        // ABSTRACCIÓN: Estas opciones permiten desactivar la verificación SSL
        // sin modificar la configuración global de PHP.
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // ==========================================
        // DESTINATARIOS
        // ==========================================
        // ABSTRACCIÓN: setFrom() y addAddress() ocultan la complejidad
        // de las cabeceras MIME y validación de correos.
        $mail->setFrom(USERNAME, 'ProQuaris System');
        $mail->addAddress($correo, $nombreReceptor);

        // ==========================================
        // CONTENIDO DEL CORREO
        // ==========================================
        // POLIMORFISMO: isHTML() cambia el comportamiento del envío
        // para formatear el contenido como HTML o texto plano.
        $mail->isHTML(true);                                  
        $mail->Subject = 'Reseteo de password - ProQuaris';
        
        $mail->Body = "
            <h3>Usted ha solicitado un reseteo de contraseña</h3>
            <p>Hola " . $nombreReceptor . ", haga clic en el siguiente enlace para continuar:</p>
            <p><a href='http://localhost/ProQuaris/views/restablecer.php?token=" . $token . "'>Cambiar Contraseña</a></p>
            <br>
            <p>Este enlace expirará en unos minutos.</p>
        ";

        // ABSTRACCIÓN: send() oculta todo el proceso de autenticación,
        // establecimiento de conexión y transferencia de datos.
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // ==========================================
        // MANEJO DE ERRORES TÉCNICOS
        // ==========================================
        // ABSTRACCIÓN: PHPMailer captura excepciones y proporciona
        // ErrorInfo para depuración sin exponer detalles internos.
        echo "<h2>[SENA ADSO] Reporte de Error Técnico de PHPMailer:</h2>";
        echo "<pre>" . $mail->ErrorInfo . "</pre>";
        echo "<br><b>Mensaje de la excepción:</b> " . $e->getMessage();
        exit();
    }
}
?>