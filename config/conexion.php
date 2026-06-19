<?php
// ==========================================
// CONEXIÓN A LA BASE DE DATOS
// ==========================================
// ABSTRACCIÓN: Esta clase abstrae toda la lógica de conexión a MySQL.
// El resto del sistema solo llama a Conexion::conectar() sin conocer
// los detalles de configuración, credenciales o manejo de excepciones.
class Conexion {
    
    // ==========================================
    // MÉTODO ESTÁTICO DE CONEXIÓN
    // ==========================================
    // ENCAPSULAMIENTO: El método es estático, no se necesita instanciar la clase.
    // POLIMORFISMO: El método siempre retorna un objeto PDO, pero internamente
    // maneja diferentes casos (crear BD, seleccionar BD, errores).
    public static function conectar() {
        // Credenciales del entorno de desarrollo local
        $host = "localhost";
        $user = "root";
        $pass = "";

        try {
            // ABSTRACCIÓN: PDO oculta los detalles del driver de base de datos.
            // El método setAttribute() configura el comportamiento de errores.
            $db = new PDO("mysql:host=$host", $user, $pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // POLIMORFISMO: exec() se adapta a diferentes comandos SQL
            // (CREATE DATABASE y USE), mostrando polimorfismo en acción.
            $db->exec("CREATE DATABASE IF NOT EXISTS proquaris_bd");
            $db->exec("USE proquaris_bd");

            return $db;
        } catch (PDOException $e) {
            // ABSTRACCIÓN: Oculta la excepción completa mostrando solo
            // un mensaje simple al usuario final.
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>