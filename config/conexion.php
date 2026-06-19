<?php
class Conexion {
    public static function conectar() {
        // Credenciales del entorno de desarrollo local
        $host = "localhost";
        $user = "root";
        $pass = "";

        try {
            // Se usa PDO con excepciones para manejar errores de forma consistente
            $db = new PDO("mysql:host=$host", $user, $pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Asegura que la base de datos exista antes de seleccionarla
            $db->exec("CREATE DATABASE IF NOT EXISTS proquaris_bd");
            $db->exec("USE proquaris_bd");

            return $db;
        } catch (PDOException $e) {
            // Detiene la ejecución mostrando el error específico de conexión
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>