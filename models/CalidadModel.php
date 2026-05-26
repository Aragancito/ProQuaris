<?php
require_once "../config/conexion.php";

class CalidadModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function registrarInspeccion($datos) {
        try {
            $query = "INSERT INTO Calidad (id_inspeccion, FK_usuario_id, resultado, fecha) 
                      VALUES (:id, :usuario_id, :resultado, :fecha)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":id", $datos['id']);
            $stmt->bindParam(":usuario_id", $datos['usuario_id']);
            $stmt->bindParam(":resultado", $datos['resultado']);
            $stmt->bindParam(":fecha", $datos['fecha']);
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Error en CalidadModel: " . $e->getMessage());
        }
    }
}