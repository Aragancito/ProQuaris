<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once "../config/conexion.php";

// ==========================================
// MODELO DE PRODUCCIÓN
// ==========================================
class ProduccionModel {
    private $db;

    public function __construct() {
        // Obtiene la conexión activa a la base de datos
        $this->db = Conexion::conectar();
    }

    // ==========================================
    // REGISTRO DE LOTE DE PRODUCCIÓN
    // ==========================================
    public function registrarLote($datos) {
        try {
            // Consulta preparada con marcadores nombrados (:param) para prevenir inyección SQL
            // Los nombres de los marcadores coinciden con las claves del array $datos
            $query = "INSERT INTO Produccion (id_lote, producto, cantidad, estado) 
                      VALUES (:id, :producto, :cantidad, :estado)";
            $stmt = $this->db->prepare($query);
            
            // Se pasa el array directamente porque los nombres de las claves coinciden 
            // con los marcadores de la consulta (ej. :id → $datos['id'])
            $stmt->execute($datos);
            return true;
        } catch (PDOException $e) {
            // Detiene la ejecución mostrando el error para depuración
            die("Error en ProduccionModel: " . $e->getMessage());
        }
    }
}
?>