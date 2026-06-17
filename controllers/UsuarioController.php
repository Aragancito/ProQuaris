<?php
require_once '../models/UsuarioModel.php';
session_start();

class UsuarioController {
    private $model;

    public function __construct() {
        $this->model = new UsuarioModel();
    }

    public function manejarPeticion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
                $this->registrar();
            } else {
                $this->login();
            }
        } elseif (isset($_GET['logout'])) {
            $this->logout();
        }
    }

    private function login() {
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        
        $usuario = $this->model->buscarPorCorreo($correo);

        if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . " " . $usuario['apellido'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            if ($usuario['rol'] === 'Administrador') {
                header("Location: ../views/dashboard.php");
            } else {
                header("Location: ../views/dashboard_empleado.php");
            }
            exit();
        } else {
            header("Location: ../views/login.php?error=1");
            exit();
        }
    }

    private function registrar() {
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'correo' => $_POST['correo'],
            'contrasena' => password_hash($_POST['contrasena'], PASSWORD_DEFAULT),
            'rol' => $_POST['rol']
        ];
        
        $resultado = $this->model->registrarUsuario($datos);
        
        if ($resultado) {
            header("Location: ../views/login.php?registro=exitoso");
        } else {
            header("Location: ../views/registro.php?error=1");
        }
        exit();
    }

    private function logout() {
        session_destroy();
        header("Location: ../views/index.php");
        exit();
    }
}

$controller = new UsuarioController();
$controller->manejarPeticion();
?>