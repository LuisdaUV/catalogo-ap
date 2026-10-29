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
                        
                        <?php if(!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo htmlspecialchars($tipo_mensaje ?? 'info'); ?>" role="alert">
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Se actualiza el action al enrutador central -->
                        <form method="POST" action="index.php?controller=registro" novalidate>
                            <div class="mb-3">
                                <label for="numero_documento" class="form-label">Número de Documento</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errores['numero_documento']) ? 'is-invalid' : ''; ?>" 
                                       id="numero_documento" 
                                       name="numero_documento" 
                                       value="<?php echo htmlspecialchars($numero_documento ?? ''); ?>" required>
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
                                           value="<?php echo htmlspecialchars($nombre ?? ''); ?>" required>
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
                                           value="<?php echo htmlspecialchars($apellidos ?? ''); ?>" required>
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
                                       value="<?php echo htmlspecialchars($correo ?? ''); ?>" required>
                                <div class="invalid-feedback">
                                    <?php echo $errores['correo'] ?? ''; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ciudad" class="form-label">Ciudad</label>
                                    <input type="text" 
                                           class="form-control <?php echo isset($errores['ciudad']) ? 'is-invalid' : ''; ?>" 
                                           id="ciudad" 
                                           name="ciudad" 
                                           value="<?php echo htmlspecialchars($ciudad ?? ''); ?>" required>
                                    <div class="invalid-feedback">
                                        <?php echo $errores['ciudad'] ?? ''; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="pais" class="form-label">País</label>
                                    <input type="text" 
                                           class="form-control be-light <?php echo isset($errores['pais']) ? 'is-invalid' : ''; ?>" 
                                           id="pais" 
                                           name="pais" 
                                           value="<?php echo htmlspecialchars($pais ?? ''); ?>" required readonly>
                                    <div class="invalid-feedback">
                                        <?php echo $errores['pais'] ?? ''; ?>
                                    </div>
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
                            <!-- Enlace actualizado -->
                            <p class="mb-0">¿Ya tienes cuenta? <a href="index.php?controller=login">Inicia sesión aquí</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ciudadInput = document.getElementById("ciudad");
            const paisInput = document.getElementById("pais");

            // Diccionario básico de mapeo Ciudad -> País
            // Asegúrate de poner las ciudades en minúsculas para facilitar la validación
            const mapaCiudades = {
                "bogota": "Colombia",
                "bogotá": "Colombia",
                "medellin": "Colombia",
                "medellín": "Colombia",
                "cali": "Colombia",
                "barranquilla": "Colombia",
                "jamundi": "Colombia",
                "jamundí": "Colombia",
                "norte de santander": "Colombia",
                "cartagena": "Colombia",
                "barranquilla": "Colombia",
                "lima": "Perú",
                "quito": "Ecuador",
                "guayaquil": "Ecuador",
                "santiago": "Chile",
                "buenos aires": "Argentina",
                "ciudad de mexico": "México",
                "cdmx": "México"
            };

            // Escucha cada vez que el usuario escribe o sale del campo
            ciudadInput.addEventListener("input", function() {
                // Convertimos el texto a minúsculas y quitamos espacios en blanco extra
                let ciudadIngresada = this.value.trim().toLowerCase();
                
                // Si la ciudad existe en nuestro diccionario, asignamos el país
                if (mapaCiudades[ciudadIngresada]) {
                    paisInput.value = mapaCiudades[ciudadIngresada];
                } else {
                    // Si no la reconoce, limpia el campo hasta que haya una ciudad válida
                    paisInput.value = "";
                }
            });
        });
    </script>
</body>
</html>