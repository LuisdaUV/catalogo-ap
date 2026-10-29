<?php
require_once 'models/UsuarioModel.php';

class LoginController {
    public function index() {
        session_start();
        
        if(isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=catalogo");
            exit();
        }

        $mensaje = '';
        $tipo_mensaje = '';

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo']);
            $password = $_POST['password'];
            
            if(empty($correo) || empty($password)) {
                $mensaje = 'Todos los campos son obligatorios';
                $tipo_mensaje = 'danger';
            } else {
                try {
                    $usuarioModel = new UsuarioModel();
                    $user = $usuarioModel->obtenerUsuarioPorCorreo($correo);
                    
                    if($user) {
                        if(password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellidos'];
                            $_SESSION['user_email'] = $user['correo'];
                            
                            header("Location: index.php?controller=catalogo");
                            exit();
                        } else {
                            $mensaje = 'Contraseña incorrecta';
                            $tipo_mensaje = 'danger';
                        }
                    } else {
                        $mensaje = 'Correo no registrado';
                        $tipo_mensaje = 'danger';
                    }
                } catch(Exception $e) {
                    $mensaje = 'Error: ' . $e->getMessage();
                    $tipo_mensaje = 'danger';
                }
            }
        }
        
        // Carga la vista e inyecta las variables $mensaje y $tipo_mensaje
        require_once 'views/login.php';
    }
}
?>