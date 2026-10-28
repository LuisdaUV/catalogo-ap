<?php
session_start();
require_once 'config/database.php';
$catalogo_ciudades = require 'config/ciudades.php';

// Devuelve el país de una ciudad del catálogo (ignora mayúsculas y tildes) o null.
function buscarPaisPorCiudad($ciudad, $catalogo) {
    $normalizar = function($t) {
        $t = mb_strtolower(trim($t), 'UTF-8');
        return strtr($t, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u']);
    };
    foreach($catalogo as $pais => $ciudades) {
        foreach($ciudades as $c) {
            if($normalizar($c) === $normalizar($ciudad)) {
                return ['ciudad' => $c, 'pais' => $pais];
            }
        }
    }
    return null;
}

$mensaje = '';
$tipo_mensaje = '';
$errores = [];

// Inicializar variables para mantener los datos en el formulario
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
    if(empty($numero_documento) || empty($nombre) || empty($apellidos) || 
       empty($correo) || empty($ciudad) || empty($pais) || empty($password)) {
        $mensaje = 'Todos los campos son obligatorios';
        $tipo_mensaje = 'danger';
    } elseif(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores['correo'] = 'Correo electrónico no válido.';
    }

    if(empty($ciudad)) $errores['ciudad'] = 'La ciudad es obligatoria.';
    if(empty($pais)) $errores['pais'] = 'El país es obligatorio.';
    
    if(empty($password)) {
        $errores['password'] = 'La contraseña es obligatoria.';
    } elseif(strlen($password) < 6) {
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
        $tipo_mensaje = 'danger';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Verificar si el documento o correo ya existen
            $query = "SELECT id FROM usuarios WHERE numero_documento = :documento OR correo = :correo";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':documento', $numero_documento);
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $mensaje = 'El número de documento o correo ya está registrado.';
                $tipo_mensaje = 'danger';
            } else {
                // Insertar nuevo usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO usuarios (numero_documento, nombre, apellidos, correo, ciudad, pais, password) 
                          VALUES (:documento, :nombre, :apellidos, :correo, :ciudad, :pais, :password)";
                $stmt = $db->prepare($query);
                
                $stmt->bindParam(':documento', $numero_documento);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellidos', $apellidos);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':ciudad', $ciudad);
                $stmt->bindParam(':pais', $pais);
                $stmt->bindParam(':password', $password_hash);
                
                if($stmt->execute()) {
                    $mensaje = 'Registro exitoso. Ahora puedes iniciar sesión.';
                    $tipo_mensaje = 'success';
                    // Limpiar el formulario tras el éxito
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="text-center mb-4">Registro de Usuario</h2>
                        
                        <?php if($mensaje): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje; ?>" role="alert">
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Se añade novalidate para que el navegador no bloquee los mensajes personalizados de Bootstrap -->
                        <form method="POST" action="registro.php" novalidate>
                            <div class="mb-3">
                                <label for="numero_documento" class="form-label">Número de Documento</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errores['numero_documento']) ? 'is-invalid' : ''; ?>" 
                                       id="numero_documento" 
                                       name="numero_documento" 
                                       value="<?php echo htmlspecialchars($numero_documento); ?>" required>
                                <div class="invalid-feedback">
                                    <?php echo $errores['numero_documento'] ?? ''; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($errores['nombre']) ? 'is-invalid' : ''; ?>" 
                                           id="nombre" 
                                           name="nombre" 
                                           value="<?php echo htmlspecialchars($nombre); ?>" required>
                                    <div class="invalid-feedback">
                                        <?php echo $errores['nombre'] ?? ''; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="apellidos" class="form-label">Apellidos</label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($errores['apellidos']) ? 'is-invalid' : ''; ?>" 
                                           id="apellidos" 
                                           name="apellidos" 
                                           value="<?php echo htmlspecialchars($apellidos); ?>" required>
                                    <div class="invalid-feedback">
                                        <?php echo $errores['apellidos'] ?? ''; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" 
                                       class="form-control <?php echo isset($errores['correo']) ? 'is-invalid' : ''; ?>" 
                                       id="correo" 
                                       name="correo" 
                                       value="<?php echo htmlspecialchars($correo); ?>" required>
                                <div class="invalid-feedback">
                                    <?php echo $errores['correo'] ?? ''; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control" id="ciudad" name="ciudad" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pais" class="form-label">País</label>
                                    <input type="text" class="form-control" id="pais" name="pais" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errores['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" 
                                       name="password" required>
                                <div class="invalid-feedback">
                                    <?php echo $errores['password'] ?? ''; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errores['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                       id="confirm_password" 
                                       name="confirm_password" required>
                                <div class="invalid-feedback">
                                    <?php echo $errores['confirm_password'] ?? ''; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Autocompleta el país al elegir o escribir una ciudad del catálogo
        (function() {
            const normalizar = t => t.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const inputCiudad = document.getElementById('ciudad');
            const inputPais = document.getElementById('pais');
            const mapa = {};
            document.querySelectorAll('#lista-ciudades option').forEach(o => {
                mapa[normalizar(o.value)] = o.dataset.pais;
            });
            function actualizarPais() {
                inputPais.value = mapa[normalizar(inputCiudad.value)] || '';
            }
            inputCiudad.addEventListener('input', actualizarPais);
            inputCiudad.addEventListener('change', actualizarPais);
            actualizarPais();
        })();
    </script>
</body>
</html>