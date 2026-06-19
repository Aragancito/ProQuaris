<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once "../config/conexion.php";

// ==========================================
// MODELO DE CALIDAD
// ==========================================
class CalidadModel {
    private $db;

    public function __construct() {
        // Obtiene la conexión activa a la base de datos mediante el singleton de Conexion
        $this->db = Conexion::conectar();
    }

    // ==========================================
    // REGISTRO DE INSPECCIÓN DE CALIDAD
    // ==========================================
    public function registrarInspeccion($datos) {
        try {
            // Se usa consulta preparada con marcadores nombrados (:param) para prevenir inyección SQL
            $query = "INSERT INTO Calidad (id_inspeccion, FK_usuario_id, resultado, fecha) 
                      VALUES (:id, :usuario_id, :resultado, :fecha)";
            $stmt = $this->db->prepare($query);
            
            // Vinculación explícita de parámetros para controlar los tipos de datos
            $stmt->bindParam(":id", $datos['id']);
            $stmt->bindParam(":usuario_id", $datos['usuario_id']);
            $stmt->bindParam(":resultado", $datos['resultado']);
            $stmt->bindParam(":fecha", $datos['fecha']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // Detiene la ejecución mostrando el error de la base de datos para depuración
            die("Error en CalidadModel: " . $e->getMessage());
        }
    }
}
?>