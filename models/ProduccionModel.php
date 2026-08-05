<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once "../config/conexion.php";

// ==========================================
// MODELO DE PRODUCCIÓN (CAPA DE DATOS)
// ==========================================
class ProduccionModel {
    
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // ==========================================
    // REGISTRO DE LOTE VINCULADO A UNA ORDEN
    // ==========================================
    public function registrarLoteDesdeOrden($fk_ordenId, $cantidad, $estado) {
        try {
            // Usamos los nombres exactos de columnas de tu base de datos (tabla 'lote')
            $query = "INSERT INTO lote (FK_ordenId, cantidad, fechaCreacion, estado) 
                      VALUES (:fk_ordenId, :cantidad, CURDATE(), :estado)";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->execute([
                ':fk_ordenId' => $fk_ordenId,
                ':cantidad' => $cantidad,
                ':estado' => $estado
            ]);
            
            return true;
        } catch (PDOException $e) {
            die("Error en ProduccionModel: " . $e->getMessage());
        }
    }

    // ==========================================
    // LISTAR LOTES (Para la nueva pestaña de Lotes)
    // ==========================================
    public function obtenerLotes() {
        try {
            $query = "SELECT l.idLote, o.producto, l.cantidad, l.fechaCreacion, l.estado 
                      FROM lote l 
                      JOIN ordenproduccion o ON l.FK_ordenId = o.idOrden";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error al obtener lotes: " . $e->getMessage());
        }
    }
}
?>