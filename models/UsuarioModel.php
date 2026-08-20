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
            estado VARCHAR(15) DEFAULT 'Activo',
            admin_asignado VARCHAR(36) NULL,
            empresa VARCHAR(100) NULL
        )";
        $this->db->exec($sql);
    }

    public function registrarUsuario($datos) {
        $id = uniqid('usr_', true); 
        
        $sql = "INSERT INTO usuario (id, nombre, apellido, correo, contraseña, rol, estado, empresa) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id,
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            $datos['contraseña'],
            $datos['rol'],
            $datos['estado'],
            $datos['empresa']
        ]);
    }

    public function buscarPorCorreo($correo) {
        $sql = "SELECT * FROM usuario WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarAdminAsignado($idUsuario, $adminAsignado) {
        $sql = "UPDATE usuario SET admin_asignado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$adminAsignado, $idUsuario]);
    }

    public function cambiarEstado($idUsuario, $nuevoEstado) {
        $sql = "UPDATE usuario SET estado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nuevoEstado, $idUsuario]);
    }

    // NUEVA FUNCIÓN: Desvincula al empleado sin borrar su cuenta
    public function desvincularUsuario($id) {
        $sql = "UPDATE usuario SET admin_asignado = NULL, estado = 'Pendiente' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function eliminarUsuario($id) {
        $sql = "DELETE FROM usuario WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>