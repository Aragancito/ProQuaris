<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../config/conexion.php';

// ==========================================
// MODELO DE USUARIO
// ==========================================
class UsuarioModel {
    private $db;

    public function __construct() {
        // Obtiene la conexión activa a la base de datos
        $this->db = Conexion::conectar();
        // Asegura que la tabla exista antes de cualquier operación
        $this->crearTabla();
    }

    // ==========================================
    // CREACIÓN AUTOMÁTICA DE LA TABLA
    // ==========================================
    // Si la tabla no existe, la crea con la estructura definida.
    // Esto permite que el sistema funcione sin ejecutar scripts SQL manualmente.
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

    // ==========================================
    // REGISTRO DE NUEVO USUARIO
    // ==========================================
    public function registrarUsuario($datos) {
        // Genera un ID único de 32 caracteres hexadecimales usando CSPRNG
        $id = bin2hex(random_bytes(16));
        
        // Consulta preparada con marcadores de posición (?) para prevenir inyección SQL
        // El estado se fija como 'Activo' por defecto al insertar
        $sql = "INSERT INTO usuario (id, nombre, apellido, correo, contraseña, rol, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id,
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            $datos['contrasena'], // Debe venir ya hasheada desde el controlador
            $datos['rol']
        ]);
    }

    // ==========================================
    // BÚSQUEDA DE USUARIO POR CORREO
    // ==========================================
    // Retorna todos los datos del usuario o false si no existe
    public function buscarPorCorreo($correo) {
        $sql = "SELECT * FROM usuario WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>