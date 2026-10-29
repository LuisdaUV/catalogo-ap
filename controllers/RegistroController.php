<?php
require_once 'models/UsuarioModel.php';

class RegistroController {
    public function index() {
        session_start();
        
        // Redirigir si ya está logueado
        if(isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=catalogo");
            exit();
        }

        $mensaje = '';
        $tipo_mensaje = '';
        $errores = [];

        // Inicializar variables para la vista
        $numero_documento = $nombre = $apellidos = $correo = $ciudad = $pais = '';

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $numero_documento = trim($_POST['numero_documento']);
            $nombre = trim($_POST['nombre']);
            $apellidos = trim($_POST['apellidos']);
            $correo = trim($_POST['correo']);
            $ciudad = trim($_POST['ciudad']);
            $pais = trim($_POST['pais']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Validaciones
            if(empty($numero_documento)) $errores['numero_documento'] = 'El documento es obligatorio.';
            if(empty($nombre)) $errores['nombre'] = 'El nombre es obligatorio.';
            if(empty($apellidos)) $errores['apellidos'] = 'Los apellidos son obligatorios.';
            
            if(empty($correo)) {
                $errores['correo'] = 'El correo es obligatorio.';
            } elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'Correo electrónico no válido.';
            }

            if(empty($ciudad)) $errores['ciudad'] = 'La ciudad es obligatoria.';
            if(empty($pais)) $errores['pais'] = 'El país es obligatorio.';
            
            if(empty($password)) {
                $errores['password'] = 'La contraseña es obligatoria.';
            } elseif(strlen($password) < 6) {
                $errores['password'] = 'Debe tener al menos 6 caracteres.';
            }

            if($password !== $confirm_password) {
                $errores['confirm_password'] = 'Las contraseñas no coinciden.';
            }

            if(empty($errores)) {
                try {
                    $usuarioModel = new UsuarioModel();
                    
                    if($usuarioModel->verificarExistencia($numero_documento, $correo)) {
                        $mensaje = 'El número de documento o correo ya está registrado.';
                        $tipo_mensaje = 'danger';
                    } else {
                        // Preparar datos para inserción
                        $datos = [
                            'numero_documento' => $numero_documento,
                            'nombre' => $nombre,
                            'apellidos' => $apellidos,
                            'correo' => $correo,
                            'ciudad' => $ciudad,
                            'pais' => $pais,
                            'password' => password_hash($password, PASSWORD_DEFAULT)
                        ];
                        
                        if($usuarioModel->registrarUsuario($datos)) {
                            $mensaje = 'Registro exitoso. Ahora puedes iniciar sesión.';
                            $tipo_mensaje = 'success';
                            // Limpiar variables
                            $numero_documento = $nombre = $apellidos = $correo = $ciudad = $pais = '';
                        } else {
                            $mensaje = 'Error al registrar el usuario.';
                            $tipo_mensaje = 'danger';
                        }
                    }
                } catch(Exception $e) {
                    $mensaje = 'Error: ' . $e->getMessage();
                    $tipo_mensaje = 'danger';
                }
            } else {
                $mensaje = 'Por favor, corrige los errores en el formulario.';
                $tipo_mensaje = 'danger';
            }
        }

        // Carga la vista de registro
        require_once 'views/registro.php';
    }
}
?>