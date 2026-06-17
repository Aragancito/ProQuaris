<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php';
require_once '../config/config.php';

function enviarCorreo($correo, $nombreReceptor, $token) {
    $mail = new PHPMailer(true);

    try {
        // --- CONFIGURACIÓN DE CONEXIÓN FORZADA ---
        $mail->SMTPDebug = 2;                      
        $mail->isSMTP();                           
        
        // Usamos la IP directa de Gmail para romper bloqueos de DNS
        $mail->Host       = '74.125.142.108'; 
        $mail->SMTPAuth   = true;                  
        $mail->Username   = USERNAME;   
        $mail->Password   = PASSWORD;   
        
        // CONFIGUACIÓN ASOCIADA AL PUERTO (Si falla, cambia a 'ssl' y '465')
        $mail->SMTPSecure = 'tls';                 
        $mail->Port       = 587;                   

        // ESTO ES OBLIGATORIO: Forzar a XAMPP a ignorar restricciones de red locales
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // --- DESTINATARIOS ---
        $mail->setFrom(USERNAME, 'ProQuaris System');
        $mail->addAddress($correo, $nombreReceptor);

        // --- CONTENIDO DEL CORREO ---
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
        echo "<h2>[SENA ADSO] Reporte de Error Técnico de PHPMailer:</h2>";
        echo "<pre>" . $mail->ErrorInfo . "</pre>";
        echo "<br><b>Mensaje de la excepción:</b> " . $e->getMessage();
        exit();
    }
}