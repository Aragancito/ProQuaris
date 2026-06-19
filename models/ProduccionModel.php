<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once "../config/conexion.php";

// ==========================================
// MODELO DE PRODUCCIÓN (CAPA DE DATOS)
// ==========================================
// ABSTRACCIÓN: Esta clase abstrae todas las operaciones de base de datos
// relacionadas con lotes de producción. El controlador solo llama
// a registrarLote() sin conocer los detalles SQL.
class ProduccionModel {
    
    // ==========================================
    // ENCAPSULAMIENTO
    // ==========================================
    // El atributo $db es privado, protegiendo la conexión a la base de datos.
    private $db;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    // ABSTRACCIÓN: La conexión se obtiene mediante un método estático
    // que oculta la configuración PDO.
    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // ==========================================
    // REGISTRO DE LOTE DE PRODUCCIÓN
    // ==========================================
    // ABSTRACCIÓN: Este método oculta toda la lógica de inserción SQL.
    // El controlador solo pasa los datos y recibe un booleano.
    public function registrarLote($datos) {
        try {
            // POLIMORFISMO: Los marcadores nombrados (:param) permiten que
            // la consulta se adapte a diferentes estructuras de datos.
            $query = "INSERT INTO Produccion (id_lote, producto, cantidad, estado) 
                      VALUES (:id, :producto, :cantidad, :estado)";
            $stmt = $this->db->prepare($query);
            
            // POLIMORFISMO: execute() recibe un array asociativo y se adapta
            // a los marcadores de la consulta sin necesidad de bindParam().
            // Los nombres de las claves deben coincidir con los marcadores.
            $stmt->execute($datos);
            return true;
        } catch (PDOException $e) {
            // ABSTRACCIÓN: El error se muestra pero los detalles internos
            // de la base de datos están ocultos en el mensaje.
            die("Error en ProduccionModel: " . $e->getMessage());
        }
    }
}
?>