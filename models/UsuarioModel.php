<?php
require_once "../config/conexion.php";

class UsuarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
        // Crea la tabla de forma automática si no existe para evitar fallos
        $this->db->exec("CREATE TABLE IF NOT EXISTS usuario (
            id VARCHAR(50) PRIMARY KEY,
            id_especifico VARCHAR(50),
            nombre VARCHAR(100),
            apellido VARCHAR(100),
            correo VARCHAR(100),
            contrasena VARCHAR(255),
            rol VARCHAR(50),
            estado VARCHAR(20),
            nivel_acceso INT DEFAULT 1,
            puesto VARCHAR(100),
            departamento VARCHAR(100),
            fecha_ingreso DATE
        )");
    }

    public function registrarNuevoUsuario($datos) {
        try {
            $query = "INSERT INTO usuario (id, id_especifico, nombre, apellido, correo, contrasena, rol, estado, nivel_acceso, puesto, departamento, fecha_ingreso) 
                      VALUES (:id, :id_especifico, :nombre, :apellido, :correo, :contrasena, :rol, :estado, :nivel_acceso, :puesto, :departamento, :fecha_ingreso)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute($datos);
        } catch (PDOException $e) {
            die("Error al insertar en la tabla usuario: " . $e->getMessage());
        }
    }

    public function buscarPorCorreo($correo) {
        try {
            $query = "SELECT * FROM usuario WHERE correo = :correo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':correo' => $correo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al buscar el correo en la tabla: " . $e->getMessage());
        }
    }
}
?>