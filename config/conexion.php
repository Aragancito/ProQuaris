<?php
class Conexion {
    public static function conectar() {
        $host = "localhost";
        $user = "root";
        $pass = "";
        try {
            $db = new PDO("mysql:host=$host", $user, $pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crea la base de datos si no existe y la selecciona
            $db->exec("CREATE DATABASE IF NOT EXISTS proquaris");
            $db->exec("USE proquaris");
            
            return $db;
        } catch (PDOException $e) {
            die("Error de conexión a MySQL: " . $e->getMessage());
        }
    }
}
?>