<?php
require_once '../config/conexion.php';

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
        $this->crearTabla();
    }

    private function crearTabla() {
        $sql = "CREATE TABLE IF NOT EXISTS usuario (
            id VARCHAR(36) PRIMARY KEY,
            nombre VARCHAR(30) NOT NULL,
            apellido VARCHAR(30) NOT NULL,
            correo VARCHAR(50) UNIQUE NOT NULL,
            contraseña VARCHAR(255) NOT NULL,
            rol VARCHAR(20) NOT NULL,
            estado VARCHAR(15) DEFAULT 'Activo'
        )";
        $this->db->exec($sql);
    }

    public function registrarUsuario($datos) {
        $id = bin2hex(random_bytes(16));
        
        $sql = "INSERT INTO usuario (id, nombre, apellido, correo, contraseña, rol, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id,
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            $datos['contrasena'],
            $datos['rol']
        ]);
    }

    public function buscarPorCorreo($correo) {
        $sql = "SELECT * FROM usuario WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>