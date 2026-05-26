<?php
require_once "../models/Usuariomodel.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* CLASE: UsuarioController */
class UsuarioController {
    private $model;

    /* MÉTODO: __construct */
    public function __construct() {
        $this->model = new UsuarioModel();
    }

    /* MÉTODO: manejarPeticion */
    public function manejarPeticion() {
        if (isset($_GET['logout'])) {
            $this->logout();
            return;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST["accion"]) && $_POST["accion"] === "registrar") {
                $this->registrar();
            } else {
                $this->login();
            }
        }
    }

    /* MÉTODO: login */
    private function login() {
        if (!isset($_POST["correo"]) || !isset($_POST["contrasena"])) {
            die("Error: No se están recibiendo los datos del formulario. Revise los atributos 'name' en su HTML.");
        }

        $correo = trim($_POST["correo"]);
        $contrasena = $_POST["contrasena"];
        
        $usuario = $this->model->buscarPorCorreo($correo);

        // Si no encuentra el registro
        if (!$usuario) {
            die("Error de Login: El correo '" . htmlspecialchars($correo) . "' no está registrado en la tabla 'usuario'.");
        }

        // Si la contraseña no coincide
        if ($contrasena !== $usuario["contrasena"]) {
            die("Error de Login: Contraseña incorrecta para el usuario: " . htmlspecialchars($correo));
        }

        // Si todo está bien, guarda sesión y redirige
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nombre"] = $usuario["nombre"] . " " . $usuario["apellido"];
        $_SESSION["usuario_rol"] = $usuario["rol"];
        
        header("Location: ../views/hola.php");
        exit();
    }

    /* MÉTODO: registrar */
    private function registrar() {
        $datos = [
            'id'            => bin2hex(random_bytes(16)),
            'id_especifico' => bin2hex(random_bytes(16)),
            'nombre'        => $_POST['nombre'] ?? '',
            'apellido'      => $_POST['apellido'] ?? '',
            'correo'        => $_POST['correo'] ?? '',
            'contrasena'    => $_POST['contrasena'] ?? '',
            'rol'           => $_POST['rol'] ?? 'Usuario',
            'estado'        => 'Activo',
            'nivel_acceso'  => 1,
            'puesto'        => $_POST['puesto'] ?? 'N/A',
            'departamento'  => $_POST['departamento'] ?? 'N/A',
            'fecha_ingreso' => date('Y-m-d')
        ];

        if ($this->model->registrarNuevoUsuario($datos)) {
            echo "<script>alert('Registro exitoso'); window.location.href='../views/login.php';</script>";
        } else {
            die("Error: No se pudo completar el registro en la base de datos.");
        }
    }

    /* MÉTODO: logout */
    private function logout() {
        session_unset();
        session_destroy();
        header("Location: ../views/login.php");
        exit();
    }
}

$controller = new UsuarioController();
$controller->manejarPeticion();
?>