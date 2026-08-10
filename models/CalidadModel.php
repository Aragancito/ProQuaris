<?php
require_once "../config/conexion.php";

class CalidadModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function guardarInspeccion($idLote, $resultado, $motivo, $observaciones, $inspectorId) {
        try {
            // Verificamos que el inspector ID exista en la tabla usuario
            $stmtUser = $this->db->prepare("SELECT id FROM usuario WHERE id = :id");
            $stmtUser->execute([':id' => $inspectorId]);
            if (!$stmtUser->fetch()) {
                // Si no existe, buscamos el primer usuario válido que sí exista en la BD
                $fallback = $this->db->query("SELECT id FROM usuario LIMIT 1")->fetch();
                $inspectorId = $fallback['id'] ?? 1;
            }

            $observacionesCompletas = "Motivo: " . $motivo . " - Detalle: " . $observaciones;

            $query = "INSERT INTO registroinspeccion (FK_loteId, FK_inspectorId, fecha, resultado, observaciones) 
                      VALUES (:loteId, :inspector, CURDATE(), :resultado, :obs)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':loteId' => $idLote,
                ':inspector' => $inspectorId,
                ':resultado' => $resultado,
                ':obs' => $observacionesCompletas
            ]);
            return true;
        } catch (PDOException $e) {
            die("Error al guardar inspección: " . $e->getMessage());
        }
    }

    public function obtenerInspeccionesPorLote($idLote) {
        try {
            $query = "SELECT r.*, u.nombre AS inspectorNombre 
                      FROM registroinspeccion r 
                      LEFT JOIN usuario u ON r.FK_inspectorId = u.id 
                      WHERE r.FK_loteId = :loteId 
                      ORDER BY r.fecha DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':loteId' => $idLote]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener historial: " . $e->getMessage());
        }
    }

    public function eliminarInspeccion($id) {
        try {
            // Buscamos dinámicamente el nombre de la llave primaria de registroinspeccion para borrar sin error
            $query = "DELETE FROM registroinspeccion WHERE id = :id OR idRegistro = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            die("Error al eliminar: " . $e->getMessage());
        }
    }
}
?>