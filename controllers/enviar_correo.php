<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';
require_once '../config/config.php';

function enviarCorreo($correo, $nombreReceptor, $token) {
    $mail = new PHPMailer(true);

    try {
        // ==========================================
        // CONFIGURACIÓN DE CONEXIÓN SMTP
        // ==========================================
        $mail->SMTPDebug = 2;                      
        $mail->isSMTP();                           
        
        // Se usa IP directa de Gmail para evitar bloqueos por resolución de DNS en entornos locales
        $mail->Host       = '74.125.142.108'; 
        $mail->SMTPAuth   = true;                  
        $mail->Username   = USERNAME;   
        $mail->Password   = PASSWORD;   

        // ==========================================
        // CONFIGURACIÓN DE PUERTO Y CIFRADO
        // ==========================================
        // TLS en puerto 587 es estándar para Gmail.
        // Si falla por restricciones de red, probar con SSL en puerto 465
        $mail->SMTPSecure = 'tls';                 
        $mail->Port       = 587;                   

        // ==========================================
        // OPCIÓN OBLIGATORIA EN XAMPP/WAMP
        // ==========================================
        // Desactiva la verificación SSL para evitar errores
        // de certificados en entornos de desarrollo locales.
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
        $mail->setFrom(USERNAME, 'ProQuaris System');
        $mail->addAddress($correo, $nombreReceptor);

        // ==========================================
        // CONTENIDO DEL CORREO
        // ==========================================
        $mail->isHTML(true);                                  
        $mail->Subject = 'Reseteo de password - ProQuaris';
        
        $mail->Body = "
            <h3>Usted ha solicitado un reseteo de contraseña</h3>
            <p>Hola " . $nombreReceptor . ", haga clic en el siguiente enlace para continuar:</p>
            <p><a href='http://localhost/ProQuaris/views/restablecer.php?token=" . $token . "'>Cambiar Contraseña</a></p>
            <br>
            <p>Este enlace expirará en unos minutos.</p>
        ";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // ==========================================
        // MANEJO DE ERRORES TÉCNICOS
        // ==========================================
        // Muestra información detallada del error SMTP
        // para depuración en entornos de desarrollo.
        echo "<h2>[SENA ADSO] Reporte de Error Técnico de PHPMailer:</h2>";
        echo "<pre>" . $mail->ErrorInfo . "</pre>";
        echo "<br><b>Mensaje de la excepción:</b> " . $e->getMessage();
        exit();
    }
}
?>